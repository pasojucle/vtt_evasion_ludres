<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\SkillCategory;
use App\Form\Admin\EventListener\Skill\AddClusterSkillSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClusterSkillAddType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skillCategory', EntityType::class, [
                'label' => 'Categorie',
                'class' => SkillCategory::class,
                'placeholder' => 'Séléctionner une catégorie',
                'required' => false,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'skills-container',
                ],
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'placeholder' => 'Séléctionner un niveau',
                'required' => false,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'skills-container',
                ],
            ])
            ->addEventSubscriber(new AddClusterSkillSubscriber($this->urlGenerator))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'clusterId' => null,
            'attr' => [
                'data-controller' => 'form-modifier',
                'data-turbo-action' => 'replace',
            ]
        ]);
    }
}
