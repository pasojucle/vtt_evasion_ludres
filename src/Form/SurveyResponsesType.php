<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class SurveyResponsesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surveyResponses', CollectionType::class, [
                'label' => false,
                'entry_type' => SurveyResponseType::class,
                'entry_options' => [
                    'label' => false,
                ],
            ]);
    }
}
