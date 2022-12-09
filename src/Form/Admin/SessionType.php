<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Session;
use App\Entity\User;
use App\Validator\SessionUniqueMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        dump($options['filters']);
        $builder
            ->add('user', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'admin_member_choices',
                'class' => User::class,
                'primary_key' => 'id',
                'text_property' => 'fullName',
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000,
                // if 'cache' is true
                'language' => 'fr',
                'placeholder' => 'Saisisez un nom et prénom',
                'width' => '100%',
                'label' => 'Participant',
                'remote_params' => [
                    'filters' => json_encode($options['filters']),
                ],
                'constraints' => [
                    new NotBlank(),
                    new SessionUniqueMember(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
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
            'filters' => null,
        ]);
    }
}
