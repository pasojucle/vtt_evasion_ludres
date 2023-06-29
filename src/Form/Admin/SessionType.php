<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\HiddenClusterType;
use App\Service\SeasonService;
use App\Service\SessionService;
use App\Validator\SessionUniqueMember;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SessionType extends AbstractType
{
    public function __construct(private SeasonService $seasonService, private SessionService $sessionService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('season', ChoiceType::class, [
                'label' => false,
                'multiple' => false,
                'choices' => $this->getSeasonChoices(),
                'attr' => [
                    'class' => 'customSelect2 form-modifier',
                    'data-modifier' => 'admin_session_add',
                    'data-width' => '100%',
                    'data-placeholder' => 'Sélectionnez une saison',
                    'data-language' => 'fr',
                    'data-allow-clear' => true,
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ;

        $formModifier = function (FormInterface $form, null|int|string $season) use ($options) {
            $filters = $options['filters'];
            $filters['season'] = $season;
            $form
                ->add('user', Select2EntityType::class, [
                    'multiple' => false,
                    'remote_route' => 'admin_member_choices',
                    'class' => User::class,
                    'primary_key' => 'id',
                    'text_property' => 'fullName',
                    'minimum_input_length' => 0,
                    'page_limit' => 10,
                    'allow_clear' => true,
                    'delay' => 250,
                    'cache' => true,
                    'cache_timeout' => 60000,
                    // if 'cache' is true
                    'language' => 'fr',
                    'placeholder' => 'Saisissez un nom et prénom',
                    'width' => '100%',
                    'label' => 'Participant',
                    'required' => true,
                    'remote_params' => [
                        'filters' => json_encode($filters),
                    ],
                    'constraints' => [
                        new NotBlank(),
                        new SessionUniqueMember(),
                    ],
                ]);
        };
    
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier, $options) {
            $form = $event->getForm();
            $data = $event->getData();

            $bikeRide = $options['bikeRide'];
            if (BikeRideType::REGISTRATION_CLUSTERS === $bikeRide->getBikeRideType()->getRegistration() && 1 < $this->sessionService->selectableClusterCount($bikeRide, $bikeRide->getClusters())) {
                $form
                        ->add('cluster', EntityType::class, [
                            'label' => false,
                            'class' => Cluster::class,
                            'choices' => $bikeRide->getClusters(),
                            'expanded' => true,
                            'multiple' => false,
                            'block_prefix' => 'customcheck',
                        ])
                    ;
            } else {
                $form
                        ->add('cluster', HiddenClusterType::class)
                    ;
            }

            $formModifier($form, $data['season']);
        });
    
        $builder->get('season')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $season = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $season);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => null,
            'bikeRide' => null,
        ]);
    }

    private function getSeasonChoices(): array
    {
        $seasonChoices = $this->seasonService->getSeasons();

        $seasonChoices['licence.status.testing_in_processing'] = Licence::STATUS_TESTING_IN_PROGRESS;

        return $seasonChoices;
    }
}
