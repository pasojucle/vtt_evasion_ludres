<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\AvailabilityEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FramerFilterType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserAutocompleteField::class, [
                'multiple' => false,
                'autocomplete_url' => $this->urlGenerator->generate('admin_framer_autocomplete', $options['filters']),
                'attr' => [
                    'data-action' => 'change->filter#change'
                ],
            ])
            ->add('availability', EnumType::class, [
                'label' => false,
                'class' => AvailabilityEnum::class,
                'autocomplete' => true,
                'attr' => [
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une disponibilité',
                    'data-action' => 'change->filter#change'
                ],
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => [],
            'attr' => [
                'data-controller' => "filter",
            ],
        ]);
    }
}
