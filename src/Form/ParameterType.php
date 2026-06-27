<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Parameter;
use App\Form\Type\TiptapType;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterType extends AbstractType
{
    public function __construct(
        private ReplaceKeywordsService $replaceKeywords,
        private SeasonService $seasonService,
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $parameter = $event->getData();
            if (null !== $parameter) {
                $type = $parameter->getType();
                $value = $parameter->getValue();
                $label = $this->replaceKeywords->replaceCurrentSaison($parameter->getLabel(), $this->seasonService->getCurrentSeason());
                $form = $event->getForm();

                [$classType, $fieldOptions] = match($type) {
                    Parameter::TYPE_BOOL => [
                        CheckboxType::class, 
                        [
                            'label' => $label,
                            'data' => (bool) $value,
                            'block_prefix' => 'switch',
                            'required' => false,
                        ]
                    ],
                    Parameter::TYPE_HTML => [
                        TiptapType::class,
                        ['config_name' => 'base',]
                    ],
                    Parameter::TYPE_ARRAY => [
                        CollectionType::class,
                        [
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
                        ]
                    ],
                    Parameter::TYPE_MONTH_AND_DAY => [
                        CollectionType::class,
                        [
                            'label' => false,
                            'block_prefix' => 'custom_month_and_hour',
                            'entry_options' => [
                                'label' => false,
                            ],
                        ]
                    ],
                    Parameter::TYPE_INTEGER => [
                        IntegerType::class,
                        [
                            'label' => false,
                            'attr' => [
                                'class' => 'border border-border',
                            ],
                        ]
                    ],
                    Parameter::TYPE_TEXT => [TextareaType::class, []],
                    
                    default => [TextType::class, []]
                };
                
                $fieldOptions['required'] = false;

                $form
                    ->add('value', $classType, $fieldOptions)
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
            'referer' => null,
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit'],
        ]);
    }
}
