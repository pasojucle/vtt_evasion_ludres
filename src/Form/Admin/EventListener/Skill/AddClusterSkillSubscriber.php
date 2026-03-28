<?php

declare(strict_types=1);

namespace App\Form\Admin\EventListener\Skill;

use App\Form\Admin\SkillAutocompleteField;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddClusterSkillSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
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
        $this->modify($event);
    }

    public function preSubmit(FormEvent $event): void
    {
        $this->modify($event);
    }

    private function modifier(FormInterface $form, ?string $categoryId, ?string $levelId): void
    {
        $params = ['cluster' => $form->getConfig()->getOption('clusterId')];
        if ($categoryId) {
            $params['category'] = $categoryId;
        }
        if ($categoryId) {
            $params['level'] = $levelId;
        }

        $form
            ->add('skill', SkillAutocompleteField::class, [
                'autocomplete_url' => $this->urlGenerator->generate('admin_skill_autocomplete', $params),
            ]);
    }

    private function modify(FormEvent $event): void
    {
        $data = $event->getData();
        [$categoryId, $levelId] = ($data)
            ? [
                array_key_exists('skillCategory', $data) && "" !== $data['skillCategory'] ? $data['skillCategory'] : null,
                array_key_exists('level', $data) && "" !== $data['level'] ? $data['level'] : null
            ] : [null, null];
    
        $this->modifier($event->getForm(), $categoryId, $levelId);
    }
}
