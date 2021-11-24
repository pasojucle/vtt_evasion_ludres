<?php

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $parameter = $event->getData();
            $type = $parameter->getType();
            $value = $parameter->getValue();
            $label = $parameter->getLabel();
            $form = $event->getForm();

            $fieldOptions = [];
            if ($type === Parameter::TYPE_BOOL) {
                $classType = CheckboxType::class;
                $fieldOptions = [
                    'data' => (bool)$value,
                    'block_prefix' => 'switch',
                    'required' => false,
                ];
            } elseif($type === Parameter::TYPE_TEXT) {
                $classType = CKEditorType::class;
                $fieldOptions = [
                    'config_name' => 'minimum_config',
                    
                ];
            } elseif($type === Parameter::TYPE_ARRAY) {
                    $classType = CollectionType::class;
                    $fieldOptions = [
                        'entry_type' => ParameterType::class,
                        'entry_options' => [
                            'label' => false,
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                    ];
            } else {
                $classType = TextType::class;
            }

            $fieldOptions['label'] = $label;
            $fieldOptions['row_attr'] = [
                'class' => 'form-group',
            ];
            $fieldOptions['required'] = false;

            $form
                ->add('value', $classType, $fieldOptions)
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
        ]);
    }
}
