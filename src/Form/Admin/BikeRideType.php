<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRide;
use App\Entity\BikeRideType as EntityBikeRideType;
use App\Form\Admin\EventListener\BikeRide\AddContentSubscriber;
use App\Form\Admin\EventListener\BikeRide\AddRestriptionSubscriber;
use App\Repository\BikeRideTypeRepository;
use App\Repository\UserRepository;
use App\Service\LevelService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BikeRideType extends AbstractType
{
    public const NO_RESTRICTION = null;
    public const RESTRICTION_TO_MEMBER_LIST = 1;
    public const RESTRICTION_TO_LEVELS = 2;
    public const RESTRICTION_TO_MIN_AGE = 3;

    public function __construct(
        private LevelService $levelService,
        private BikeRideTypeRepository $bikeRideTypeRepository,
        private UserRepository $userRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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
        ;

        $builder->addEventSubscriber(new AddContentSubscriber($this->bikeRideTypeRepository));

        $builder->addEventSubscriber(new AddRestriptionSubscriber($this->levelService, $this->userRepository));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BikeRide::class,
        ]);
    }
}
