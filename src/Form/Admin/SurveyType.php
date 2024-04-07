<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Survey;
use App\Form\Admin\EventListener\Survey\AddRestrictionSubscriber;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Validator\CKEditorBlank;
use Doctrine\Common\Collections\Collection;
use App\Form\Type\CkeditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyType extends AbstractType
{
    public const DISPLAY_ALL_MEMBERS = null;
    public const DISPLAY_BIKE_RIDE = 1;
    public const DISPLAY_MEMBER_LIST = 2;

    public function __construct(
        private readonly LevelService $levelService,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'base',
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'constraints' => [
                    new CKEditorBlank(),
                ],
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('restriction', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Afficher le sondage à tous les adhérents' => self::DISPLAY_ALL_MEMBERS,
                    'Afficher le sondage à l\'inscription à une sortie' => self::DISPLAY_BIKE_RIDE,
                    'Afficher le sondage à une liste d\'adhérents' => self::DISPLAY_MEMBER_LIST,
                ],
                'choice_attr' => function () {
                    return [
                        'data-modifier' => 'surveyRestriction',
                        'class' => 'form-modifier',
                     ];
                },
                'disabled' => $options['display_disabled'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, array $options) {
            $form
                ->add('surveyIssues', CollectionType::class, [
                    'label' => false,
                    'entry_type' => SurveyIssueType::class,
                    'entry_options' => [
                        'label' => false,
                        'attr' => [
                            'class' => ($options['display_disabled']) ? 'row form-group-collection not-deleted' : 'row form-group-collection',
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('isAnonymous', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'attr' => [
                        'data-switch-on' => 'Mode anonyme activé',
                        'data-switch-off' => 'Activer le mode anonyme',
                    ],
                    'required' => false,
                    'disabled' => $options['display_disabled'],
                ]);
        };

        $builder->addEventSubscriber(new AddRestrictionSubscriber($this->levelService, $this->userRepository, $this->urlGenerator));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $formModifier) {
            $form = $event->getForm();

            $formModifier($form, $options);
        });

        $builder->get('restriction')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($options, $formModifier) {
                $formModifier($event->getForm()->getParent(), $options);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
            'filters' => [],
            'display_disabled' => false,
        ]);
    }
}
