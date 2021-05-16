<?php

namespace App\Form;

use App\Entity\RegistrationStep;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('filename', TextType::class, [
                'label' => 'Fichier pdf',
                'required' => false,
            ])
            ->add('form', TextType::class, [
                'label' => 'Nom du formulaire',
                'required' => false,
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Eregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegistrationStep::class,
        ]);
    }
}
