<?php

namespace App\Form\Admin;


use App\Entity\User;
use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;


class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('user', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'user_list_select2',
                'class' => User::class,
                'primary_key' => 'id',
                'text_property' => 'fullName',
                'minimum_input_length' => 2,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000, // if 'cache' is true
                'language' => 'fr',
                'placeholder' => 'Saisisez un nom et prénom',
                'width' => '100%',
                'label' => 'Participant'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-primary float-right'],
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
