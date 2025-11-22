<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceOptionEnum;
use App\Entity\Licence;
use App\Form\LicenceConsentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

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
                if (LicenceCategoryEnum::ADULT === $options['category'] && $licence->getState()->isYearly()) {
                    if (UserType::FORM_LICENCE_TYPE === $options['current']->getForm()) {
                        $form
                            ->add('isVae', CheckboxType::class, [
                                'label' => 'VTT à assistance électrique',
                                'required' => false,
                            ])
                        ;
                    }
                }

                if (UserType::FORM_OVERVIEW === $options['current']->getForm()) {
                    $form
                        ->add('licenceOverviewConsents', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceConsentType::class,
                        ])
                    ;
                }
                if (UserType::FORM_LICENCE_AUTHORIZATIONS === $options['current']->getForm()) {
                    $form
                        ->add('licenceAuthorizations', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceAuthorizationType::class,
                        ])
                        ->add('licenceAuthorizationConsents', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceConsentType::class,
                        ])
                    ;
                }

                if (UserType::FORM_HEALTH_QUESTION === $options['current']->getForm()) {
                    $form
                        ->add('licenceHealthConsents', CollectionType::class, [
                            'label' => false,
                            'entry_type' => LicenceConsentType::class,
                        ])
                    ;
                }

                if (UserType::FORM_LICENCE_COVERAGE === $options['current']->getForm()) {
                    $form
                        ->add('coverage', ChoiceType::class, [
                            'label' => 'Selectionnez une formule d\'assurance',
                            'choices' => $choicesCoverage,
                            'expanded' => true,
                            'multiple' => false,
                        ])
                        ->add('options', ChoiceType::class, [
                            'label' => 'Selectionnez une ou plusieurs options d\'assurance',
                            'choices' => $this->getOptionChoices(),
                            'expanded' => true,
                            'multiple' => true,
                        ])
                    ;
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
            'category' => Licence::CATEGORY_ADULT,
            'current' => null,
        ]);
    }
}
