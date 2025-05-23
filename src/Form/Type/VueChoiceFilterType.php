<?php

declare(strict_types=1);

namespace App\Form\Type;

use ReflectionClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VueChoiceFilterType extends AbstractType
{
    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'field',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['className'] = (new ReflectionClass($options['class']))->getShortName();
        $view->vars['field'] = $options['field'];
    }

    public function getBlockPrefix(): string
    {
        return 'vueChoiceFilter';
    }
}
