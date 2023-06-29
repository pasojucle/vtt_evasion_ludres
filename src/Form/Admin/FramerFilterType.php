<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class FramerFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'admin_framer_choices',
                'class' => User::class,
                'primary_key' => 'id',
                'text_property' => 'fullName',
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000,
                'language' => 'fr',
                'placeholder' => 'Saisissez un nom et prénom',
                'width' => '100%',
                'label' => 'Participant',
                'remote_params' => [
                    'filters' => json_encode($options['filters']),
                ],
            ])
            ->add('availability', ChoiceType::class, [
                'label' => false,
                'multiple' => false,
                'choices' => $this->getAvailabilityChoices(),
                'attr' => [
                    'class' => 'customSelect2',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une disponibilité',
                    'data-maximum-selection-length' => 4,
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ;
    }

    private function getAvailabilityChoices()
    {
        $choices = Session::AVAILABILITIES;
        $choices[Session::AVAILABILITY_UNDEFINED] = 'session.availability.undefined';
        return array_flip($choices);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => [],
        ]);
    }
}
