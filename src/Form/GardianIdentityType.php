<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\GardianKindEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\EventListener\GardianIdentitySubscriber;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GardianIdentityType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {        
        $builder->addEventSubscriber(new GardianIdentitySubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
            'category' => Licence::CATEGORY_ADULT,
            'is_yearly' => null,
            'gardian' => GardianKindEnum::LEGAL_GARDIAN,
        ]);
    }
}
