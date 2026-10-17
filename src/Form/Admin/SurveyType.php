<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\SurveyRestriction;
use App\Entity\Survey;
use App\Form\Admin\EventListener\Survey\AddRestrictionSubscriber;
use App\Form\Type\TiptapType;
use App\Repository\BikeRideRepository;
use App\Repository\MemberRepository;
use App\Service\LevelService;
use App\Validator\CKEditorBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SurveyType extends AbstractType
{
    public const DISPLAY_ALL_MEMBERS = null;
    public const DISPLAY_BIKE_RIDE = 1;
    public const DISPLAY_USER_LIST = 2;

    public function __construct(
        private readonly LevelService $levelService,
        private readonly MemberRepository $memberRepository,
        private readonly BikeRideRepository $bikeRideRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content', TiptapType::class, [
                'label' => 'Contenu',
                'config_name' => 'base',
                'constraints' => [
                    new CKEditorBlank(),
                ],
            ])
            ->add('surveyIssues', CollectionType::class, [
                'label' => false,
                'entry_type' => SurveyIssueType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'flex flex-col gap-2 p-2 border border-border rounded-sm'
                    ],
                    'issues_disabled' => $options['issues_disabled'],
                    // 'attr' => [
                    //     'class' => ($options['display_disabled']) ? 'row form-group-collection not-deleted' : 'row form-group-collection',
                    // ],
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
                'disabled' => $options['issues_disabled'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var Survey $survey */
            $survey = $event->getData();
            $restriction = match (true) {
                null !== $survey->getBikeRide() => SurveyRestriction::ACTIVITY,
                !$survey->getMembers()->isEmpty() => SurveyRestriction::MEMBERS,
                default => SurveyRestriction::ALL_MEMBERS
            };
            $survey->setRestriction($restriction);
            $form
                ->add('restriction', EnumType::class, [
                    'expanded' => true,
                    'multiple' => false,
                    'class' => SurveyRestriction::class,
                    'choice_attr' => function () {
                        return [
                            'data-action' => 'change->form-modifier#change',
                            'data-container-id' => 'survey-participants',
                        ];
                    },
                ]);
        });

        $builder->addEventSubscriber(new AddRestrictionSubscriber($this->levelService, $this->memberRepository, $this->bikeRideRepository, $this->urlGenerator));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
            'filters' => [],
            'issues_disabled' => false,
            'attr' => [
                'data-controller' => 'form-modifier'
            ]
        ]);
    }
}
