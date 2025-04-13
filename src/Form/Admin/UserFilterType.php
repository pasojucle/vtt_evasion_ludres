<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\UserAutocompleteField;
use App\Service\LevelService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserFilterType extends AbstractType
{
    public function __construct(
        private LevelService $levelService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserAutocompleteField::class, [
                'autocomplete_url' => $this->urlGenerator->generate($options['remote_route'], $options['filters'])
            ])
            ->add('levels', ChoiceType::class, [
                'label' => false,
                'multiple' => true,
                'choices' => $this->levelService->getLevelChoices(),
                'required' => false,
                'autocomplete' => true,
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
                        'autocomplete' => true,
                        'required' => $options['status_is_require'],
                    ])
                ;
            }

            if ($options['permission_choices']) {
                $form
                    ->add('permission', ChoiceType::class, [
                        'label' => false,
                        'multiple' => true,
                        'choices' => $options['permission_choices'],
                        'autocomplete' => true,
                        'required' => false,
                        'attr' => [
                           'placeholder' => 'Selectionnez une permission',
                        ],
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'status_choices' => [],
            'permission_choices' => [],
            'status_is_require' => false,
            'status_placeholder' => '',
            'filters' => [],
            'remote_route' => '',
        ]);
    }
}
