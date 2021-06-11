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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AdditionalFamilyMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            if ($licence === $options['season_licence'] && !$options['is_kinship']) {
                $form
                ->add('additionalFamilyMember', CheckboxType::class, [
                    'label' => 'Un membre de ma famille est déja inscri au club',
                    'required' => false,
                ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'is_kinship' => false,
            'season_licence' => null,
        ]);
    }
}
