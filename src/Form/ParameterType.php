<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Parameter;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $parameter = $event->getData();
            if (null !== $parameter) {
                $type = $parameter?->getType();
                $value = $parameter?->getValue();
                $label = $parameter?->getLabel();
                $form = $event->getForm();
                $fieldOptions = [];
                if (Parameter::TYPE_BOOL === $type) {
                    $classType = CheckboxType::class;
                    $fieldOptions = [
                        'data' => (bool) $value,
                        'block_prefix' => 'switch',
                        'required' => false,
                    ];
                } elseif (Parameter::TYPE_TEXT === $type) {
                    $classType = CKEditorType::class;
                    $fieldOptions = [
                        'config_name' => 'minimum_config',
                    ];
                } elseif (Parameter::TYPE_ARRAY === $type) {
                    $classType = CollectionType::class;
                    $fieldOptions = [
                        'entry_options' => [
                            'label' => false,
                            'row_attr' => [
                                'class' => 'row',
                            ],
                            'attr' => [
                                'class' => 'col-md-11 form-group',
                            ],
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
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
        ]);
    }
}
