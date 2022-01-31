<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Cluster;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClusterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('event')
            ->add('usersPerCluster', IntegerType::class, [
                'label' => 'Nombre de personne par groupe hors encardrement (optionnel)',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cluster::class,
        ]);
    }
}
