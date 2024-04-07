<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Form\Type\CkeditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ToolType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserAutocompleteField::class, [
                'label' => 'Adhérent',
                'autocomplete_url' => $this->urlGenerator->generate('admin_all_user_autocomplete'),
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Message',
                'config_name' => 'full',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'has_current_season' => false,
        ]);
    }
}
