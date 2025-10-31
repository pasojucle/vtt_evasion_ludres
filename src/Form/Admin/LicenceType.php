<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            if ($licence === $options['season_licence']) {
                $notAllowedState = LicenceStateEnum::DRAFT;
                $form
                    ->add('state', EnumType::class, [
                        'label' => 'État',
                        'class' => LicenceStateEnum::class,
                        'choice_filter' => ChoiceList::filter(
                            $this,
                            function (LicenceStateEnum $licenceState) use ($notAllowedState): bool {
                                return  $notAllowedState !== $licenceState;
                            },
                            $notAllowedState
                        ),
                        'autocomplete' => true,
                        'attr' => [
                            'data-width' => '100%',
                            'data-placeholder' => 'Sélectionnez un état',
                        ],
                        'required' => false,
                    ])
                    ->add('isVae', CheckboxType::class, [
                        'label' => 'VTT à assistance électrique',
                        'required' => false,
                    ])
                    ->add('coverage', ChoiceType::class, [
                        'label' => 'Selectionnez une formule d\'assurance',
                        'choices' => array_flip(Licence::COVERAGES),
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'season_licence' => null,
        ]);
    }
}
