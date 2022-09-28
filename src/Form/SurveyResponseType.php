<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $surveyResponse = $event->getData();
            $form = $event->getForm();

            if (SurveyIssue::RESPONSE_TYPE_STRING !== $surveyResponse->getSurveyIssue()->getResponseType()) {
                $choices = SurveyResponse::VALUES;
                if (SurveyIssue::RESPONSE_TYPE_CHECK) {
                    unset($choices[SurveyResponse::VALUE_NO_OPINION]);
                }
                $form
                    ->add('value', ChoiceType::class, [
                        'choices' => array_flip($choices),
                        'expanded' => true,
                        'label' => false,
                        'row_attr' => [
                            'class' => 'form-group radio-group',
                        ],
                    ])
                ;
            } else {
                $form
                    ->add('value', TextareaType::class, [
                        'label' => false,
                        'row_attr' => [
                            'class' => 'form-group',
                        ],
                        'required' => false,
                    ])
                ;
            }
        });
        $builder
            ->add('surveyIssue', HiddenSurveyIssueType::class)
            ->add('uuid', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SurveyResponse::class,
        ]);
    }
}
