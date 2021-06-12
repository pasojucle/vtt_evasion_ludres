<?php

namespace App\Form;

use App\Entity\Disease;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiseaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('curentTreatment', TextType::class, [
                'attr' => [
                    'class' => 'widget-inline'
                ]
            ])
            ->add('emergencyTreatment', TextType::class, [
                'attr' => [
                    'class' => 'widget-inline'
                ]
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $disease = $event->getData();
            $form = $event->getForm();
            if (Disease::LABEL_OTHER === $disease->getLabel()) {
                $form
                    ->add('title', TextType::class, [
                        'attr' => [
                            'class' => 'widget-inline'
                        ]
                    ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Disease::class,
        ]);
    }
}
