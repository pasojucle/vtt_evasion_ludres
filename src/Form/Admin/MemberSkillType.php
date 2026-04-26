<?php

namespace App\Form\Admin;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Form\HiddenEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberSkillType extends AbstractType
{
    public const BY_USERS = 1;
    public const BY_SKILLS = 2;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var MemberSkill $memberSkill */
            $memberSkill = $event->getData();
            $form = $event->getForm();
  
            list($name, $content) = $this->getText($options['text_type'], $memberSkill);
            // $attrClass = [
            //     'data-action' => 'change->form-modifier#change',
            //     'data-container-id' => 'user_skill_container'
            // ];
            $attrClass = [
                'data-action' => 'change->form-filter#submit',
            ];
            // $form->getConfig()->getOptions()->set

            $form
                ->add($name, TextType::class, [
                    'label' => $content,
                    'label_html' => true,
                    'mapped' => false,
                    'required' => false,
                    'block_prefix' => 'label_only',
                    'row_attr' => [
                        'class' => 'w-1/2 text-sm',
                    ],
                ])
                ->add('evaluation', EnumType::class, [
                    'label' => false,
                    'class' => EvaluationEnum::class,
                    'expanded' => true,
                    'multiple' => false,
                    'row_attr' => [
                        'class' => 'w-1/2',
                    ],
                    'block_prefix' => 'btn_radio',
                    'choice_attr' => function ($choice, string $key, mixed $value) use ($attrClass) {
                        return array_merge($attrClass, ['data-color' => $choice->variant()]);
                    }
                ])
                ->add('skill', HiddenEntityType::class, [
                    'class' => Skill::class,
                ])
                ->add('member', HiddenEntityType::class, [
                    'class' => Member::class,
                ])
            ;
        });
    }

    private function getText(int $type, MemberSkill $memberSkill): array
    {
        if (self::BY_USERS === $type) {
            $member = $memberSkill->getMember()->getIdentity();
            return ['user', sprintf('%s %s', $member->getName(), $member->getFirstName())];
        }

        return ['content', $memberSkill->getSkill()->getContent()];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberSkill::class,
            'text_type' => self::BY_USERS,
        ]);
    }
}
