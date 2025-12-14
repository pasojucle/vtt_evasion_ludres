<?php

namespace App\Form\Admin;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\UserSkill;
use App\Form\HiddenSkillType;
use App\Form\HiddenUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSkillType extends AbstractType
{
    public const BY_USERS = 1;
    public const BY_SKILLS = 2;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var UserSkill $userSkill */
            $userSkill = $event->getData();
            $form = $event->getForm();
  
            list($name, $content) = $this->getText($options['text_type'], $userSkill);

            $form
                ->add($name, TextType::class, [
                    'mapped' => false,
                    'disabled' => true,
                    'block_prefix' => 'vueText',
                    'data' => $content,
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

    private function getText(int $type, UserSkill $userSkill): array
    {
        if (self::BY_USERS === $type) {
            $member = $userSkill->getUser()->getIdentity();
            return ['member', sprintf('%s %s', $member->getName(), $member->getFirstName())];
        }

        return ['content', $userSkill->getSkill()->getContent()];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSkill::class,
            'text_type' => self::BY_USERS,
        ]);
    }
}
