<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\LevelType as LevelTypeEnum;
use App\Entity\Level;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LevelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
            ])
            ->add('color', ColorType::class, [
                'label' => 'Couleur',
            ])
            ->add('type', EnumType::class, [
                'label' => 'Type',
                'class' => LevelTypeEnum::class,
            ])
            ->add('accompanyingCertificat', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'attr' => [
                    'data-switch-on' => 'Réaliser une attestation adulte accompagnateur avec ce niveau',
                    'data-switch-off' => 'Pas d\'attestation adulte accompagnateur',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
