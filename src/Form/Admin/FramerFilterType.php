<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'remote_route' => $this->urlGenerator->generate('admin_framer_autocomplete', $options['filters']),
            ])
            ->add('availability', ChoiceType::class, [
                'label' => false,
                'multiple' => false,
                'autocomplete' => true,
                'choices' => $this->getAvailabilityChoices(),
                'attr' => [
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une disponibilité',
                ],
                'required' => false,
            ])
            ;
    }

    private function getAvailabilityChoices()
    {
        $choices = Session::AVAILABILITIES;
        $choices[Session::AVAILABILITY_UNDEFINED] = 'session.availability.undefined';
        return array_flip($choices);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => [],
        ]);
    }
}
