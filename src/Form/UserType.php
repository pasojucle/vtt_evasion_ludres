<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'placeholder' => 'Saisir son prénom',
                ],
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'placeholder' => 'Saisir son nom',
                ],
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Saisir son adresse mail',
                ],
                'label' => 'Adresse mail',
            ])
        ;

        $formModifier = function (FormInterface $form, bool $isActive = false, ?int $id = null) {
            if (!$isActive && null!= $id) {
                $form->add('sendActiveLink', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'label' => 'Envoyer un lien d\'activation',
                    'required' => false,
                ])
                ;
            }

        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $isActive = (null !== $data) ? $data->isActive() : false;
                $id = (null !== $data) ? $data->getId() : null;
                $formModifier($event->getForm(), $isActive, $id);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
