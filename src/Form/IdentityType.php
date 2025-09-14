<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Form\EventListener\IdentityAdultSubscriber;
use App\Service\LicenceService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdentityType extends AbstractType
{
    public function __construct(
        private LicenceService $licenceService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new IdentityAdultSubscriber($this->licenceService));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
            'category' => Licence::CATEGORY_ADULT,
            'is_final' => false,
            'is_kinship' => false,
        ]);
    }
}
