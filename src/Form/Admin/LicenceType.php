<?php

namespace App\Form\Admin;

use App\Entity\Licence;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            
            if ($licence === $options['season_licence']) {
                if (!$licence->isFinal()) {
                    $form
                        ->add('final', CheckboxType::class, [
                            'label' => 'Période de test terminée',
                            'required' => false,
                        ]);
                }

                if (!$licence->isValid()) {
                    $form
                        ->add('valid', CheckboxType::class, [
                            'label' => 'Dossier d\'inscription valide',
                            'required' => false,
                        ]);
                }
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
