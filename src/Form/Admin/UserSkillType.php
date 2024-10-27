<?php

namespace App\Form\Admin;

use App\Entity\UserSkill;
use App\Form\HiddenUserType;
use App\Form\HiddenSkillType;
use App\Entity\Enum\EvaluationEnum;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData()->getUser();
            $member = $user->getMemberIdentity();
  
        $event->getForm()
            ->add('member', TextType::class, [
                'mapped' => false,
                'disabled' => true,
                'block_prefix' => 'vueText',
                'data' => sprintf('%s %s', $member->getName(), $member->getFirstName()),
                'row_attr' => [
                    'class' => 'col-md-6 text-label',
                ],
            ])
            ->add('evaluation', EnumType::class, [
                'class' => EvaluationEnum::class,
                'block_prefix' => 'vueRadio',
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
            ])
            ->add('skill', HiddenSkillType::class)
            ->add('user', HiddenUserType::class)
        ;
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSkill::class,
        ]);
    }
}
