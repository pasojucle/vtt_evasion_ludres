<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalFamilyMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var Licence $licence */
            $licence = $event->getData();
            $form = $event->getForm();
            if ($licence->getSeason() === $options['season_licence']->season && !$options['is_kinship']) {
                $form
                    ->add('additionalFamilyMember', CheckboxType::class, [
                        'label' => 'Un membre de ma famille est déjà inscrit au club',
                        'required' => false,
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'is_kinship' => false,
            'season_licence' => null,
        ]);
    }
}
