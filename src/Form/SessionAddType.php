<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\HiddenUserType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SessionAddType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
     $this->security = $security;   
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $session = $event->getData();
            $form = $event->getForm();
            $submitLabel = '<i class="fas fa-chevron-circle-right"></i> ';
            if (null === $session->getAvailability() && null === $session->getCluster()) {
                if ($session->getCluster()->getEvent()->getAccessAvailabity($this->security->isGranted('ROLE_ACCOMPANIST'))) {
                    $submitLabel .= 'Enregister';
                    $form
                        ->add('availability', ChoiceType::class, [
                        'label' => false,
                        'choices' => array_flip(Session::AVAILABILITIES),
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'customcheck',
                    ])
                    ;
                } elseif (null === $session->getCluster()) {
                    $submitLabel .= 'S\'inscrire';
                    $form
                    ->add('cluster', EntityType::class, [
                        'label' => false,
                        'class' => Cluster::class, 
                        'choices' => $options['clusters'],
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'customcheck',
                    ])
                    ;
                }

                $form
                    ->add('submit', SubmitType::class, [
                        'label' => '<i class="fas fa-chevron-circle-right"></i> S\'inscrire',
                        'label_html' => true,
                        'attr' => ['class' => 'btn btn-primary float-right'],
                    ])
                ;
            }
        });
        $builder
            ->add('user', HiddenUserType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'clusters' => [],
        ]);
    }
}
