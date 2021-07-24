<?php

namespace App\Form;

use App\Entity\Cluster;
use App\Entity\Session;
use App\Repository\ClusterRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SessionSwitchType extends AbstractType
{
    private ClusterRepository $clusterRepository;
    public function __construct(ClusterRepository $clusterRepository)
    {
        $this->clusterRepository = $clusterRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Enregister',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
            ;
            
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $session = $event->getData();
            $form = $event->getForm();
            $form->add('cluster', EntityType::class, [
                'label' => false,
                'class' => Cluster::class, 
                'choices' => $this->getChoices($session),
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'customcheck',
            ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'session' => null
        ]);
    }

    private function getChoices(Session $session): array
    {
        $event = $session->getCluster()->getEvent();

        return $this->clusterRepository->findByEvent($event);
    }
}
