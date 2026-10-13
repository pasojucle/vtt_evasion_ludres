<?php

declare(strict_types=1);

namespace App\Form\Filter\EventListener;

use App\Dto\Enum\SurveyResponseValueType;
use App\Entity\Enum\SurveyResponseType;
use App\Form\HiddenEnumType;
use App\Repository\SurveyIssueRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SurveyIssueResponseFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SurveyIssueRepository $surveyIssueRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $filter = $event->getData();

        $issue = $filter->issue;

        $this->modifier($event->getForm(), $issue->getResponseType());
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        $issueId = $data['issue'] ?? null;
        $issue = ($issueId) ? $this->surveyIssueRepository->find($issueId) : null;
        $responseType = $issue?->getResponseType() ?? SurveyResponseType::TEXT;
        if (SurveyResponseType::TEXT === $responseType) {
            $data['value'] = null;
            $event->setData($data);
        }

        $this->modifier($event->getForm(), $responseType);
    }

    private function modifier(FormInterface $form, SurveyResponseType $responseType): void
    {
        if (SurveyResponseType::TEXT !== $responseType) {
            $form
                ->add('value', EnumType::class, [
                    'label' => 'Réponse',
                    'class' => SurveyResponseValueType::class,
                    'choices' => [SurveyResponseValueType::YES, SurveyResponseValueType::NO, SurveyResponseValueType::NO_OPINION],
                    'attr' => [
                        'data-action' => 'change->filter#updateContent',
                        'data-container-id' => 'survey-response-drawer'
                    ],
                    'row_attr' => ['class' => 'not-last:border-border not-last:border-b not-last:pb-4'],
                    'autocomplete' => true,
                    'required' => false,
                ])
            ;
        } else {
            $form->add('value', HiddenEnumType::class, [
                'class' => SurveyResponseValueType::class,
            ]);
        }
    }
}
