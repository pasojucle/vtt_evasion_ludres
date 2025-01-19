<?php

declare(strict_types=1);

namespace App\Form\Type;

use ReflectionClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReactChoiceFilteredType extends AbstractType
{
    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'selected_values',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entityName'] = (new ReflectionClass($options['class']))->getShortName();
        $view->vars['selectedValues'] = json_encode($options['selected_values']);
    }


    public function getBlockPrefix(): string
    {
        return 'reactChoiceFiltered';
    }
}
