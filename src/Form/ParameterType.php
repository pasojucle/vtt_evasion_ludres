<?php

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formModifier = function (FormInterface $form, string $type = null, string $name, $value, string $label) {
            if ($type == "bool") {
                $classType = CheckboxType::class;
                $fieldOptions = [
                    'data' => (bool)$value,
                    'block_prefix' => 'switch',
                    'required' => false,
                ];
            } else {
                $classType = ChoiceType::class;
                $fieldOptions = [
                    'expanded' => false,
                    'multiple' => false,
                    'choices' => (array_key_exists($name, Parameter::CHOICES)) ? Parameter::CHOICES[$name] : [],
                    'choice_label' => function ($choice, $key, $value) {
                        return lcfirst($value);
                    },
                ];
            }

            $fieldOptions['label'] = $label;

            $form
                ->add('value', $classType, $fieldOptions)

            ;
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event)  use ($formModifier) {
                $data = $event->getData();
                $type = (null !== $data) ? $data->getType() : null;
                $name = (null !== $data) ? $data->getName() : null;
                $value = (null !== $data) ? $data->getValue() : null;
                $label = (null !== $data) ? $data->getLabel() : null;
                $formModifier($event->getForm(), $type, $name, $value, $label);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
        ]);
    }
}
