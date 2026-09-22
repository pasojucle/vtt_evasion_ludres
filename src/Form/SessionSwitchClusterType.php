<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Cluster;
use App\Entity\Session;
use App\Repository\ClusterRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionSwitchClusterType extends AbstractType
{
    public function __construct(
        private ClusterRepository $clusterRepository
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $session = $event->getData();
            $form = $event->getForm();
            $form->add('cluster', EntityType::class, [
                'label' => false,
                'class' => Cluster::class,
                'choices' => $this->getChoices($session),
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'ballot_radio',
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'session' => null,
        ]);
    }

    private function getChoices(Session $session): array
    {
        $bikeRide = $session->getCluster()->getBikeRide();

        return $this->clusterRepository->findByBikeRide($bikeRide);
    }
}
