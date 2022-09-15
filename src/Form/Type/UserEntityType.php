<?php

declare(strict_types=1);

namespace App\Form\Type;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class UserEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
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
            'language' => 'fr',
            'placeholder' => 'Saisisez un nom et prénom',
            'width' => '100%',
            'label' => 'Adhérent',
            'remote_params' => [
                'filters' => json_encode([]),
            ],
            'required' => true,
        ]);
    }

    public function getParent(): string
    {
        return Select2EntityType::class;
    }
}
