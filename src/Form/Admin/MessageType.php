<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Message;
use App\Entity\ParameterGroup;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'minimum_config',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
        ;

        if ($options['referer']) {
            $builder->add('referer', HiddenType::class, [
                'data' => $options['referer'],
                'mapped' => false,
            ]);
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $message = $event->getData();
            if (!$options['modal']) {
                $form
                    ->add('name', TextType::class, [
                        'label' => 'Nom (Tout en capital, sans espace)',
                        'disabled' => $message->isProtected(),
                        'row_attr' => [
                            'class' => 'form-group',
                        ],
                    ])
                    ->add('label', TextType::class, [
                        'label' => 'Désignation',
                        'row_attr' => [
                            'class' => 'form-group',
                        ],
                    ])
                    ->add('levelType', ChoiceType::class, [
                        'label' => 'Type',
                        'choices' => array_flip(Level::TYPES),
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('section', EntityType::class, [
                        'label' => 'Section',
                        'class' => ParameterGroup::class,
                        'choice_label' => 'label',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('save', SubmitType::class, [
                        'label' => 'Enregistrer',
                        'attr' => [
                            'class' => 'btn btn-primary float-right',
                        ],
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'referer' => null,
            'modal' => false,
        ]);
    }
}
