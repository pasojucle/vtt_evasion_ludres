<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Health;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('password')
            //->add('health')
            //->add('licence')
        ;

        if ('HealthQuestion' === $options['current']->getForm()) {
            $builder
                ->add('health', HealthType::class, [
                    'label' => false,
                    'current' => $options['current']
                ]);
        }
        
        if ('Identity' === $options['current']->getForm()) {
            $builder
                ->add('identities', CollectionType::class, [
                    'label' => false,
                    'entry_type' => IdentityType::class,
                ]);
        }
        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'type' => 'adulte',
            'current' => null,
        ]);
    }
}
