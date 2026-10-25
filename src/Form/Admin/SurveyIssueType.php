<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\SurveyResponseType;
use App\Entity\SurveyIssue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyIssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $builder->getFormConfig()->getDisabled();

        $builder
            ->add('content', TextType::class, [
                'label' => false,
                'disabled' => $disabled,
                'attr' => [
                    'placeholder' => 'Saisir une question',
                ],
            ])
            ->add('responseType', EnumType::class, [
                'label' => false,
                'class' => SurveyResponseType::class,
                'disabled' => $disabled,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurveyIssue::class,
            'issues_disabled' => false,
        ]);
    }
}
