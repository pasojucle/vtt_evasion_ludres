<?php

namespace App\Form;

use App\Entity\RegistrationStep;
use Symfony\Component\Form\AbstractType;
use App\Form\RegistrationStepContentType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class RegistrationStepType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
            ->add('form', ChoiceType::class, [
                'label' => 'Nom du formulaire',
                'placeholder' => 'Aucun',
                'choices' => array_flip(UserType::FORMS),
                'choice_label' => function ($choice, $key, $value) {
                    return $this->translator->trans($key);
                },
                'required' => false,
            ])
            // ->add('contents', CollectionType::class, [
            //     'label' => false,
            //     'entry_type' => RegistrationStepContentType::class,
            //     'entry_options' => [
            //         'label' => false,
            //     ],
            // ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
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
