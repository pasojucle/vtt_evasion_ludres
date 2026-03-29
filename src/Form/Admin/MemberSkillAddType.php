<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\EventListener\Skill\AddSkillSubscriber;
use App\Form\Admin\SkillAddFilterType;
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
            ->add('filter', SkillAddFilterType::class)
            ->addEventSubscriber(new AddSkillSubscriber($this->urlGenerator))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'memberId' => null,
            'attr' => [
                'data-controller' => 'form-modifier',
                'data-turbo-action' => 'replace',
            ]
        ]);
    }
}
