<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Disease;
use App\Entity\DiseaseKind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiseaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /**@var Disease $disease */
            $disease = $event->getData();
            $form = $event->getForm();
            $isActive = !empty($disease->getCurentTreatment()) || !empty($disease->getEmergencyTreatment());

            $curentTreatmentClass = 'widget-inline widget-curent-treatment';
            $currentTraitementLabel = (DiseaseKind::CATEGORY_DISEASE === $disease->getDiseaseKind()->getCategory()) ? 'Traitement actuel' : 'Lesquelles';
            if ($disease->getDiseaseKind()->hasEmergencyTreatment()) {
                $curentTreatmentClass = 'widget-inline';
                $form
                    ->add('emergencyTreatment', TextType::class, [
                        'attr' => [
                            'class' => ($isActive) ? 'widget-inline' : 'widget-inline disabled',
                            'placeholder' => "Traitement d'urgence",
                        ],
                        'required' => $isActive,
                    ])
                ;
            }
            $form
                ->add('curentTreatment', TextType::class, [
                    'attr' => [
                        'class' => ($isActive) ? $curentTreatmentClass : $curentTreatmentClass . ' disabled',
                        'placeholder' => $currentTraitementLabel,
                    ],
                    'required' => $isActive,
                ])
                ->add('active', CheckboxType::class, [
                    'mapped' => false,
                    'data' => $isActive,
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'disease-active',
                    ],
                ])
            ;
            if ($disease->getDiseaseKind()->hasCustomLabel()) {
                $form
                    ->add('title', TextType::class, [
                        'attr' => [
                            'class' => ($isActive) ? 'widget-inline' : 'widget-inline disabled',
                        ],
                        'required' => $isActive,
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Disease::class,
            'current' => null,
        ]);
    }
}
