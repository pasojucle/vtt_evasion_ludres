<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Type\UserEntityType;
use App\Service\SeasonService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSearchType extends AbstractType
{
    public function __construct(private SeasonService $seasonService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', UserEntityType::class, [
                'remote_route' => 'admin_all_user_choices',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ;
    }
}
