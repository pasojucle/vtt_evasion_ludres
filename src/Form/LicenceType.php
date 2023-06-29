<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                $choicesCoverage = array_flip(Licence::COVERAGES);
                if (Licence::CATEGORY_MINOR === $options['category']) {
                    array_shift($choicesCoverage);
                }
                if (Licence::CATEGORY_ADULT === $options['category'] && $licence->isFinal() && UserType::FORM_LICENCE_TYPE === $options['current']->getForm()) {
                    $form
                        ->add('type', ChoiceType::class, [
                            'label' => 'Sélectionnez un type de licence avec les 3 boutons Balade, Rando et Sportive',
                            'choices' => array_flip(Licence::TYPES),
                            'expanded' => true,
                            'multiple' => false,
                            'block_prefix' => 'customcheck',
                        ])
                        ->add('isVae', CheckboxType::class, [
                            'label' => 'VTT à assistance électrique',
                            'required' => false,
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
                            'block_prefix' => 'customcheck',
                        ])
                    ;
                }
            }
        });
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
