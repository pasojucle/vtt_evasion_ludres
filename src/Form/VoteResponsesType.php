<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class VoteResponsesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('voteResponses', CollectionType::class, [
                'label' => false,
                'entry_type' => VoteResponseType::class,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Voter',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ;
    }
}