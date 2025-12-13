<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRide;
use App\Entity\BikeRideType as EntityBikeRideType;
use App\Form\Admin\EventListener\BikeRide\BikeRideSubscriber;
use App\Repository\BikeRideTypeRepository;
use App\Repository\UserRepository;
use App\Service\LevelService;
use App\Service\MessageService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\File;

class BikeRideType extends AbstractType
{
    public const NO_RESTRICTION = null;
    public const RESTRICTION_TO_MEMBER_LIST = 1;
    public const RESTRICTION_TO_RANGE_AGE = 2;

    public function __construct(
        private readonly LevelService $levelService,
        private readonly BikeRideTypeRepository $bikeRideTypeRepository,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MessageService $messageService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bikeRideType', EntityType::class, [
                'label' => 'Type de randonnée',
                'class' => EntityBikeRideType::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('brt')
                        ->orderBy('brt.name', 'ASC')
                    ;
                },
                'choice_label' => 'name',
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'bike_ride_container',
                ],
            ])
            ->add('bikeRideTypeChanged', HiddenType::class, [
                'mapped' => false,
                'data' => 0,
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier (optionnel)',
                'mapped' => false,
                'required' => false,
                'block_prefix' => 'custom_file',
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png, .pdf',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/bmp',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Format image bmp, jpeg, png ou pdf autorisé',
                    ]),
                ],
            ])
            ->add('startAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Date de départ',
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
            ->add('displayDuration', IntegerType::class, [
                'label' => 'Durée d\'affichage (nbr de jours avant)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 90,
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('private', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-off' => 'Publication publique',
                    'data-switch-on' => 'Publication privée',
                ],
            ])
            
            ->add('notify', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-off' => 'Pas de notification',
                    'data-switch-on' => 'Afficher une une pop up pour notifier l\'activité',
                ],
            ])
            
        ;

        $builder->addEventSubscriber(new BikeRideSubscriber($this->bikeRideTypeRepository, $this->messageService, $this->levelService, $this->userRepository, $this->urlGenerator));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BikeRide::class,
        ]);
    }
}
