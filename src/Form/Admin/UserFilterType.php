<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\User;
use App\Repository\LevelRepository;
use App\Service\LicenceService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class UserFilterType extends AbstractType
{
    public const STATUS_TYPE_MEMBER = 1;
    public const STATUS_TYPE_REGISTRATION = 2;
    public const STATUS_TYPE_COVERAGE = 3;
    public const LEVEL_GROUP_SCHOOL = 'École VTT';
    public const LEVEL_GROUP_FRAME = 'Encadrement';

    public function __construct(
        private LevelRepository $levelRepository,
        private Security $security,
        private LicenceService $licenceService,
        private RouterInterface $router
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
                'choices' => $this->getLevelChoices(),
                'attr' => [
                    'class' => 'select2',
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
                            'class' => 'select2',
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

    private function getLevelChoices(): array
    {
        $levelChoices = [
            self::LEVEL_GROUP_SCHOOL => ['Toute l\'école VTT' => Level::TYPE_ALL_MEMBER],
            self::LEVEL_GROUP_FRAME => ['Tout l\'encadrement' => Level::TYPE_ALL_FRAME],
        ];

        $levels = $this->levelRepository->findAll();
        if (!empty($levels)) {
            foreach ($levels as $level) {
                match ($level->getType()) {
                    Level::TYPE_SCHOOL_MEMBER => $levelChoices[self::LEVEL_GROUP_SCHOOL][$level->getTitle()] = $level->getId(),
                    Level::TYPE_FRAME => $levelChoices[self::LEVEL_GROUP_FRAME][$level->getTitle()] = $level->getId(),
                    default => $levelChoices[$level->getTitle()] = $level->getId()
                };
            }
        }

        return $levelChoices;
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
