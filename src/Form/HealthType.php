<?php

namespace App\Form;

use App\Entity\Health;
use App\Form\HealthQuestionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class HealthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('Identity' === $options['current']->getForm()) {
        $builder
            ->add('socialSecurityNumber')
            ->add('mutualCompany')
            ->add('mutualNumber')
            ->add('bloodGroup')
            ->add('tetanusBooster')
            ->add('doctorName')
            ->add('doctorAddress')
            ->add('doctorTown')
            ->add('doctorPhone')
            ;
        }

        $builder
            ->add('healthQuestions', CollectionType::class, [
                'label' => false,
                'entry_type' => HealthQuestionType::class,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Health::class,
            'type' => 'adulte',
            'current' => null,
        ]);
    }
}
