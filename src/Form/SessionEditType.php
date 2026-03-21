<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Session;
use App\Form\HiddenEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $session = $event->getData();
            $form = $event->getForm();

            if (true === $options['is_writable_availability']) {
                $notAllowedAvailabilities = [AvailabilityEnum::NONE];
                if (!$session->getCluster()->getBikeRide()->getBikeRideType()->isNeedFramers()) {
                    $notAllowedAvailabilities[] = AvailabilityEnum::AVAILABLE;
                }

                $form
                    ->add('availability', EnumType::class, [
                        'label' => false,
                        'class' => AvailabilityEnum::class,
                        'choice_filter' => ChoiceList::filter(
                            $this,
                            function (AvailabilityEnum $availability) use ($notAllowedAvailabilities): bool {
                                return !in_array($availability, $notAllowedAvailabilities);
                            },
                            $notAllowedAvailabilities
                        ),
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'customcheck',
                    ]);
            } else {
                if (null === $session->getCluster()) {
                    $form
                        ->add('cluster', EntityType::class, [
                            'label' => false,
                            'class' => Cluster::class,
                            'choices' => $options['clusters'],
                            'expanded' => true,
                            'multiple' => false,
                            'block_prefix' => 'customcheck',
                        ]);
                } else {
                    $form
                        ->add('cluster', HiddenEntityType::class, [
                            'class' => Cluster::class,
                        ]);
                }
            }
            
            if (true === $options['display_bike_kind']) {
                $form
                    ->add('practice', EnumType::class, [
                        'label' => 'Type de vélo',
                        'class' => PracticeEnum::class,
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'customcheck',
                    ]);
            }
        });

        $builder
            ->add('user', HiddenMemberType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'clusters' => [],
            'is_writable_availability' => false,
            'display_bike_kind' => false,
        ]);
    }
}
