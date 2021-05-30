<?php

namespace App\Form;

use App\Entity\RegistrationStepContent;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationStepContentType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'required' => false,
            ])
            ->add('formChildren', ChoiceType::class, [
                'label' => 'Nom du formulaire',
                'placeholder' => 'Aucun',
                'choices' => array_flip(UserType::FORMS),
                'choice_label' => function ($choice, $key, $value) {
                    return $this->translator->trans($key);
                },
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegistrationStepContent::class,
        ]);
    }
}
