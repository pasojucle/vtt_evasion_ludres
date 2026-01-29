<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Type\TiptapType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LicenceRejectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TiptapType::class, [
                'config_name' => 'base',
            ])
        ;
    }
}
