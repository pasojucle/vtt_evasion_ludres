<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ToolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'admin_all_user_choices',
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
                'placeholder' => 'Saisissez un nom et prénom',
                'width' => '100%',
                'label' => 'Adhérent',
                'remote_params' => [
                    'filters' => json_encode(['status' => null]),
                ],
                'required' => true,
                'attr' => [
                    'class' => 'submit-asynchronous',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Message',
                'config_name' => 'full_config',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'has_current_season' => false,
        ]);
    }
}
