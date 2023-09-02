<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Cluster;
use App\Entity\Session;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                $form
                    ->add('availability', ChoiceType::class, [
                        'label' => false,
                        'choices' => array_flip(Session::AVAILABILITIES),
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
                        ->add('cluster', HiddenClusterType::class);
                }
            }
            
            if (true === $options['display_bike_kind']) {
                $form
                    ->add('bikeKind', ChoiceType::class, [
                        'label' => 'Type de vélo',
                        'choices' => array_flip(Session::BIKEKINDS),
                        'expanded' => true,
                        'multiple' => false,
                        'block_prefix' => 'customcheck',
                    ]);
            }
        });

        $builder
            ->add('user', HiddenUserType::class);
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
