<?php

declare(strict_types=1);

namespace App\Form\Type;

use ReflectionClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ReactChoiceType extends AbstractType
{
    public function getParent(): string
    {
        return EntityType::class;
    }


    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entityName'] = (new ReflectionClass($options['class']))->getShortName();
    }

    public function getBlockPrefix(): string
    {
        return 'reactChoice';
    }
}
