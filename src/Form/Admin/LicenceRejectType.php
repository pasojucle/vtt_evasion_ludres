<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Form\LicenceReject;
use App\Form\Type\TiptapType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

        public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceReject::class,
        ]);
    }
}
