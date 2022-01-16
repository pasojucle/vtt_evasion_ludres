<?php

namespace App\Form;

use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use App\Form\HiddenVoteIssueType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class VoteResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $voteResponse = $event->getData();
            $form = $event->getForm();

            if ($voteResponse->getVoteIssue()->getResponseType() === VoteIssue::RESPONSE_TYPE_CHOICE) {
                $form
                    ->add('value', ChoiceType::class, [
                        'choices' => array_flip(VoteResponse::VALUES),
                        'expanded' => true,
                        'label' => false,
                        'row_attr' => [
                            'class' => 'form-group radio-group',
                        ],
                    ]);
            } else {
                $form
                    ->add('value', TextareaType::class, [
                        'label' => false,
                        'row_attr' => [
                            'class' => 'form-group',
                        ],
                        'required' => false,
                    ]);
            }
        });
        $builder
            ->add('voteIssue', HiddenVoteIssueType::class)
            ->add('uuid', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoteResponse::class,
        ]);
    }
}
