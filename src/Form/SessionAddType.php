<?php

namespace App\Form;

use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\HiddenUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SessionAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cluster', EntityType::class, [
                'label' => false,
                'class' => Cluster::class, 
                'choices' => $options['clusters'],
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'customcheck',
            ])
            ->add('user', HiddenUserType::class)
            ->add('submit', SubmitType::class, [
                'label' => '<i class="fas fa-chevron-circle-right"></i> S\'inscrire',
                'label_html' => true,
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'clusters' => [],
        ]);
    }
}
