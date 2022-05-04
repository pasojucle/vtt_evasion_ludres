<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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

                switch ($type) {
                    case Parameter::TYPE_BOOL:
                        $classType = CheckboxType::class;
                        $fieldOptions = [
                            'data' => (bool) $value,
                            'block_prefix' => 'switch',
                            'required' => false,
                        ];
                        break;
                    case Parameter::TYPE_TEXT:
                        $classType = CKEditorType::class;
                        $fieldOptions = [
                            'config_name' => 'minimum_config',
                        ];
                        break;
                    case Parameter::TYPE_ARRAY:
                        $classType = CollectionType::class;
                        $fieldOptions = [
                            'entry_options' => [
                                'label' => false,
                                'row_attr' => [
                                    'class' => 'row form-group-collection',
                                ],
                                'attr' => [
                                    'class' => 'col-md-11',
                                ],
                            ],
                            'allow_add' => true,
                            'allow_delete' => true,
                        ];
                        break;
                    case Parameter::TYPE_MONTH_AND_DAY:
                        $classType = CollectionType::class;
                        $fieldOptions = [
                            'block_prefix' => 'custom_month_and_hour',
                            'entry_options' => [
                                'label' => false,
                            ],
                        ];
                        break;
                    default:
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
