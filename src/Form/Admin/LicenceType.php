<?php

namespace App\Form\Admin;

use App\Entity\Licence;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class LicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            
            if ($licence === $options['season_licence']) {
                $form
                    ->add('final', ChoiceType::class, [
                        'label' => 'Période de 3 séances de test',
                        'choices' => [
                            'En cours' => false,
                            'Terminée'=> true,
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('valid', ChoiceType::class, [
                        'label' => 'Dossier d\'inscription',
                        'choices' => [
                            'En cours' => false,
                            'Validé'=> true,
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'season_licence' => null,
        ]);
    }
}