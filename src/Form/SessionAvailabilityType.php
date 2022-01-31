<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SessionAvailabilityType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('availability', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(Session::AVAILABILITIES),
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'customcheck',
            ])
            ->add('user', HiddenUserType::class)
            ->add('submit', SubmitType::class, [
                'label' => '<i class="fas fa-chevron-circle-right"></i> Modifier',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
