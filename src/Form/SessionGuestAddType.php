<?php

namespace App\Form;

use App\Entity\Cluster;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Session;
use App\Repository\ClusterRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SessionGuestAddType extends AbstractType
{
    public function __construct(
        private ClusterRepository $clusterRepository,
    ) {
    }

    private const array ALLOWED_PRACTICES = [PracticeEnum::VTT, PracticeEnum::GRAVEL];


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cluster', EntityType::class, [
                'label' => 'Sélectionnez votre randonnée',
                'class' => Cluster::class,
                'choices' => $options['clusters'],
                'choice_label' => function (Cluster $cluster): string {
                    return $cluster->getTitle();
                },
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'checkgroup',
                'choice_attr' => function ($choice, string $key, mixed $value) {
                    return [
                        'data-action' => 'change->form-modifier#change',
                        'data-container-id' => 'bike-ride-session'
                    ];
                },
            ])
            ->add('user', GuestType::class)
            ->add('rulesApproval', CheckboxType::class, [
                'label' => 'J\'accepte le règlement de la randonnée inscrit ci-dessus.',
                'block_prefix' => 'customsimplecheck',
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez accepter les conditions.']),
                ],
                'mapped' => false,
            ])
            ->add('healthApproval', CheckboxType::class, [
                'label' => 'J\'atteste sur l\'honneur, avoir pris connaissance du questionnaire de santé et des règles d’or. Être en condition physique suffisante pour effectuer le parcours que j\'ai choisi. Avoir pris connaissance des difficultés du parcours et des consignes de sécurité..',
                'block_prefix' => 'customsimplecheck',
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez accepter les conditions.']),
                ],
                'mapped' => false,
            ])
        ;

        $formModifier = function (FormInterface $form, ?Cluster $cluster, bool $isRulesApproved, bool $isHealthApproved) {
            $attr = ['class' => 'btn btn-primary'];
            if ($isRulesApproved && $isHealthApproved) {
                $attr['onclick'] = "window.location.href='https://www.payasso.fr/evasion-de-ludres/paiements'";
            } else {
                $attr['class'] = 'btn btn-primary disabled';
            }
            [$choices, $hidden] = (in_array($cluster?->getPractice(), self::ALLOWED_PRACTICES))
                ? [[BikeTypeEnum::MUSCULAR, BikeTypeEnum::ELECTRIC, ], '']
                : [[BikeTypeEnum::NONE], 'hidden'];
            $form
                ->add('bikeType', EnumType::class, [
                    'label' => 'Type de vélo',
                    'class' => BikeTypeEnum::class,
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => false,
                    'block_prefix' => 'checkgroup',
                    'row_attr' => [
                        'class' => $hidden
                    ]
                ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var Session $session */
            $session = $event->getData();
            $formModifier($event->getForm(), $session->getCluster(), false, false);
        });

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $clusterId = array_key_exists('cluster', $data) ? $data['cluster'] : null;
                $cluster = $clusterId ? $this->clusterRepository->find($clusterId) : null;
                if (!in_array($cluster?->getPractice(), self::ALLOWED_PRACTICES)) {
                    $data['bikeType'] = BikeTypeEnum::NONE->value;
                    $event->setData($data);
                }
                $isRulesApproved = array_key_exists('rulesApproval', $data) ? (bool) $data['rulesApproval'] : false;
                $isHealthApproved = array_key_exists('healthApproval', $data) ? (bool) $data['healthApproval'] : false;

                $formModifier($event->getForm(), $cluster, $isRulesApproved, $isHealthApproved);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'clusters' => [],
            'attr' => [
                'data-controller' => 'form-modifier',
            ]
        ]);
    }
}
