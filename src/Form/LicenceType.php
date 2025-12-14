<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use App\Form\LicenceAgreementType;
use Symfony\Component\Form\FormEvent;
use App\Entity\Enum\LicenceOptionEnum;
use Symfony\Component\Form\FormEvents;
use App\Entity\Enum\LicenceCategoryEnum;
use Symfony\Component\Form\AbstractType;
use App\Entity\Enum\RegistrationFormEnum;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class LicenceType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            if ($licence->getId() === $options['season_licence']?->id) {
                $choicesCoverage = array_flip(Licence::COVERAGES);
                if (LicenceCategoryEnum::SCHOOL === $options['category']) {
                    array_shift($choicesCoverage);
                }

                if (RegistrationFormEnum::OVERVIEW === $options['current']->getForm()) {
                    $form
                        ->add('licenceOverviewAgreements', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceAgreementType::class,
                        ])
                    ;
                }
                if (RegistrationFormEnum::LICENCE_AGREEMENTS === $options['current']->getForm()) {
                    $form
                        ->add('licenceAuthorizationAgreements', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceAgreementType::class,
                        ])
                    ;
                }

                if (RegistrationFormEnum::HEALTH_QUESTION === $options['current']->getForm()) {
                    $form
                        ->add('licenceHealthAgreements', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceAgreementType::class,
                        ])
                    ;
                }

                if (RegistrationFormEnum::LICENCE_COVERAGE === $options['current']->getForm()) {
                    $form
                        ->add('coverage', ChoiceType::class, [
                            'label' => 'Selectionnez une formule d\'assurance oblogatoire',
                            'choices' => $choicesCoverage,
                            'expanded' => true,
                            'multiple' => false,
                        ])
                        ->add('options', ChoiceType::class, [
                            'label' => 'Sélectionnez en complément, si besoin, la ou les garanties optionnelles proposée',
                            'choices' => $this->getOptionChoices(),
                            'expanded' => true,
                            'multiple' => true,
                        ])
                    ;
                    if (LicenceCategoryEnum::ADULT === $options['category'] && $licence->getState()->isYearly()) {
                        $form
                            ->add('isVae', ChoiceType::class, [
                                'label' => 'Type de vélo',
                                'choices' => [
                                    'Vélo musculaire' => false,
                                    'VTT à assistance électrique' => true,
                                ],
                                'expanded' => true,
                                'multiple' => false,
                            ])
                        ;
                    }
                }
            }
        });
    }

    private function getOptionChoices(): array
    {
        $options = [];
        foreach (LicenceOptionEnum::cases() as $option) {
            $options[$option->trans($this->translator)] = $option->value;
        }

        return $options;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'season_licence' => null,
            'category' => LicenceCategoryEnum::ADULT,
            'current' => null,
        ]);
    }
}
