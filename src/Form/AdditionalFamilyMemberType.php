<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdditionalFamilyMemberType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('familyMember', LicenceAutocompleteField::class, [
                'label' => 'Un membre de ma famille est déjà inscrit au club (Père, Mère, frères, sœurs, enfants)',
                'autocomplete_url' => $this->urlGenerator->generate('licence_autocomplete'),
                'attr' => [
                    'data-constraint' => 'app-LicenceNumber',
                ],
                'row_attr' => [
                    'class' => 'form-group mt-10',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'is_gardian' => false,
            'season_licence' => null,
        ]);
    }
}
