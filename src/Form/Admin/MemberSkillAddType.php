<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Payload\MemberSkillCreatePayload;
use App\Entity\Level;
use App\Entity\MemberSkill;
use App\Entity\SkillCategory;
use App\Form\Admin\EventListener\Skill\AddSkillSubscriber;
use App\Form\Admin\SkillAddFilterType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MemberSkillAddType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'label' => 'Categorie',
                'class' => SkillCategory::class,
                'placeholder' => 'Séléctionner une catégorie',
                'required' => false,
                'autocomplete' => true,
                'row_attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'sheet-skills-container',
                ],
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'placeholder' => 'Séléctionner un niveau',
                'required' => false,
                'autocomplete' => true,
                'row_attr' => [
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'sheet-skills-container',
                ],
            ])
            ->addEventSubscriber(new AddSkillSubscriber($this->urlGenerator))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberSkillCreatePayload::class,
            'memberId' => null,
            'clusterId' => null,
            'attr' => [
                'data-controller' => 'form-modifier',
            ]
        ]);
    }
}
