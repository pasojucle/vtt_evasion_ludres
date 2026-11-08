<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\LevelType;
use App\Entity\Level;
use App\Entity\Message;
use App\Entity\Section;
use App\Form\Type\TiptapType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $message = $event->getData();
            dump($message);
            $form
                ->add('id', TextType::class, [
                    'label' => 'Nom (Tout en capital, sans espace)',
                    'disabled' => $message->isProtected(),
                ])
                ->add('label', TextType::class, [
                    'label' => 'Désignation',
                ])
                ->add('content', TiptapType::class, [
                    'label' => 'Contenu',
                    'config_name' => 'base',
                ])
                ->add('levelType', EnumType::class, [
                    'label' => 'Type de niveau',
                    'class' => LevelType::class,
                    'required' => false,
                ])
                ->add('section', EntityType::class, [
                    'label' => 'Section',
                    'class' => Section::class,
                    'choice_label' => 'label',
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
