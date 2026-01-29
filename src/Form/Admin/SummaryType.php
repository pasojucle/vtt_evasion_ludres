<?php

namespace App\Form\Admin;

use App\Entity\Summary;
use App\Form\Type\TiptapType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SummaryType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('bikeRide', BikeRideAutocompleteField::class, [
                'autocomplete_url' => $this->urlGenerator->generate('admin_bike_ride_autocomplete'),
                'required' => true,
            ])
            ->add('content', TiptapType::class, [
                'label' => 'Détail',
                'config_name' => 'full',
                'required' => true,
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Summary::class,
        ]);
    }
}
