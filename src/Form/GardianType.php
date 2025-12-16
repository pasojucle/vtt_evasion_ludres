<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\GardianKindEnum;
use App\Entity\Enum\KinshipEnum;
use App\Entity\Licence;
use App\Entity\UserGardian;
use App\Form\GardianIdentityType;
use App\Form\IdentityType;
use App\Service\LicenceService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GardianType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $gardian = $event->getData();
            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();

            $notAllowedKinship = (GardianKindEnum::SECOND_CONTACT !== $gardian->getKind()) ? KinshipEnum::KINSHIP_OTHER : null;

            $form
                ->add('kinship', EnumType::class, [
                    'label' => 'Parenté',
                    'class' => KinshipEnum::class,
                    'placeholder' => 'Choisir le lien de parenté',
                    'choice_filter' => ChoiceList::filter(
                        $this,
                        function (KinshipEnum $kinship) use ($notAllowedKinship): bool {
                                return  $notAllowedKinship !== $kinship;
                            },
                        $notAllowedKinship
                    ),
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('identity', GardianIdentityType::class, [
                    'label' => false,
                    'category' => $options['category'],
                    'is_yearly' => $options['is_yearly'],
                    'gardian' => $gardian->getKind()
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserGardian::class,
            'category' => Licence::CATEGORY_ADULT,
            'is_yearly' => null,
        ]);
    }
}
