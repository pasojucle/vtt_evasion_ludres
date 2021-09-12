<?php

namespace App\Form;


use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\HiddenUserType;
use App\Form\HiddenClusterType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;

class SessionEditType extends AbstractType
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
            $submitLabel = null;

            if (!$options['is_already_registered'] && !$options['is_end_testing']) {
                if (null !== $options['event'] && $options['event']->getAccessAvailabity($this->security->getUser())) {
                    $submitLabel = 'Enregister';
                    $form
                        ->add('availability', ChoiceType::class, [
                        'label' => false,
                        'choices' => array_flip(Session::AVAILABILITIES),
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'customcheck',
                    ])
                    ;
                } else {
                    if (null === $session->getCluster()) {
                        $submitLabel = 'S\'inscrire';
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
                    } else {
                        $submitLabel .= 'S\'inscrire';
                        $form
                            ->add('cluster', HiddenClusterType::class);
                    }
                }
                if (null !== $submitLabel) {
                    $form
                        ->add('submit', SubmitType::class, [
                            'label' => '<i class="fas fa-chevron-circle-right"></i> '. $submitLabel,
                            'label_html' => true,
                            'attr' => ['class' => 'btn btn-primary float-right'],
                        ])
                    ;
                }
            } else {

                if ($options['is_already_registered']) {
                    $form->addError(new FormError('Votre inscription a déjà été prise en compte !'));
                }elseif ($options['is_end_testing']) {
                    $form->addError(new FormError('Votre période d\'essai est terminée ! Pour continuer à participer aux sorties, inscrivez-vous.'));
                }
                
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
            'event' => null,
            'is_already_registered' => false,
            'is_end_testing' => false,
        ]);
    }
}
