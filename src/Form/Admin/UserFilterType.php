<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\User;
use App\Service\LevelService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class UserFilterType extends AbstractType
{
    public function __construct(
        private LevelService $levelService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => $options['remote_route'],
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
                'placeholder' => 'Saisisez un nom et prénom',
                'width' => '100%',
                'label' => 'Participant',
                'remote_params' => [
                    'filters' => json_encode($options['filters']),
                ],
            ])
            ->add('levels', ChoiceType::class, [
                'label' => false,
                'multiple' => true,
                'choices' => $this->levelService->getLevelChoices(),
                'attr' => [
                    'class' => 'customSelect2',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez un ou plusieurs niveaux',
                    'data-maximum-selection-length' => 4,
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            if ($options['status_choices']) {
                $form
                    ->add('status', ChoiceType::class, [
                        'label' => false,
                        'multiple' => false,
                        'choices' => $options['status_choices'],
                        'attr' => [
                            'class' => 'customSelect2',
                            'data-width' => '100%',
                            'data-placeholder' => $options['status_placeholder'],
                            'data-language' => 'fr',
                            'data-allow-clear' => true,
                        ],
                        'required' => false,
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'status_choices' => [],
            'status_placeholder' => '',
            'filters' => [],
            'remote_route' => '',
        ]);
    }
}
