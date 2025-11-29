<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use App\Form\EventListener\AdditionalFamilyMemberSubscriber;
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
        $builder->addEventSubscriber(new AdditionalFamilyMemberSubscriber($this->urlGenerator));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'is_kinship' => false,
            'season_licence' => null,
        ]);
    }
}
