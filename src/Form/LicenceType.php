<?php

namespace App\Form;

use App\Entity\Licence;
use App\Repository\LicenceRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            if ($licence === $options['season_licence']) {
                $choices = array_flip(Licence::COVERAGES);
                if ($options['category'] === Licence::CATEGORY_MINOR) {
                    array_shift($choices);
                }
                $form
                    ->add('coverage', ChoiceType::class, [
                        'label' => false,
                        'choices' => $choices,
                        'expanded' => true,
                        'multiple' => false,
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
            'category' => Licence::CATEGORY_ADULT,
        ]);
    }
}
