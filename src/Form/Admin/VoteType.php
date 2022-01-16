<?php

namespace App\Form\Admin;

use App\Entity\Vote;
use App\Form\Admin\VoteIssueType;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('title', TextType::class, [
            'label' => 'Titre',
            'row_attr' => [
                'class' => 'form-group-inline',
            ],
        ])
        ->add('content', CKEditorType::class, [
            'label' => 'Contenu',
            'config_name' => 'minimum_config',
            'required' => false,
        ])
        ->add('startAt', DateTimeType::class, [
            'label' => 'Date de début',
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'js-datepicker',
                'autocomplete' => "off",
            ],
            'row_attr' => [
                'class' => 'form-group-inline',
            ],
        ])
        ->add('endAt', DateTimeType::class, [
            'label' => 'Date de fin',
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => [
                'class' => 'js-datepicker',
                'autocomplete' => "off",
            ],
            'row_attr' => [
                'class' => 'form-group-inline',
            ],
        ])
        ->add('voteIssues', CollectionType::class, [
            'label' => false,
            'entry_type' => VoteIssueType::class,
            'entry_options' => [
                'label' => false,
                'attr' => [
                    'class' => 'row',
                ],
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Enregistere',
            'attr' => ['class' => 'btn btn-primary float-right'],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vote::class,
        ]);
    }
}
