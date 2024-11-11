<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Content;
use App\Form\Type\BackgroundsType;
use App\Form\Type\CkeditorType;
use App\Repository\ContentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ContentType extends AbstractType
{
    public function __construct(
        private ContentRepository $contentRepository,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ->add('route', HiddenType::class, [
                'empty_data' => 'home',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $content = $event->getData();
            $form = $event->getForm();
            if (null === $content) {
                $parent = $this->contentRepository->findOneByRoute('home');
                $content = new Content();
                $content->setRoute('home')->setParent($parent);
                $event->setData($content);
            }

            if (!$content->isBackgroundOnly()) {
                $form
                    ->add('content', CKEditorType::class, [
                        'label' => 'Contenu',
                        'config_name' => 'full',
                        'required' => false,
                    ]);
            }
            if (null === $content || 'home' === $content->getRoute()) {
                $form
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('startAt', DateTimeType::class, [
                        'label' => 'Date de départ',
                        'widget' => 'single_text',
                        'html5' => false,
                        'format' => 'dd/MM/yyyy',
                        'attr' => [
                            'class' => 'js-datepicker',
                            'autocomplete' => 'off',
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'required' => false,
                    ])
                    ->add('endAt', DateTimeType::class, [
                        'label' => 'Date de fin (optionnel)',
                        'widget' => 'single_text',
                        'html5' => false,
                        'format' => 'dd/MM/yyyy',
                        'attr' => [
                            'class' => 'js-datepicker',
                            'autocomplete' => 'off',
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'required' => false,
                    ])
                    ->add('isFlash', CheckboxType::class, [
                        'label' => 'Message flash',
                        'required' => false,
                        'block_prefix' => 'customsimplecheck',
                    ])
                    ->add('file', FileType::class, [
                        'label' => 'Fichier (optionnel)',
                        'mapped' => false,
                        'required' => false,
                        'block_prefix' => 'custom_file',
                        'attr' => [
                            'accept' => '.bmp,.jpeg,.jpg,.png, .pdf',
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'constraints' => [
                            new File([
                                'maxSize' => '2048k',
                                'mimeTypes' => [
                                    'image/bmp',
                                    'image/jpeg',
                                    'image/png',
                                    'application/pdf',
                                ],
                                'mimeTypesMessage' => 'Format image bmp, jpeg, png ou pdf autorisé',
                            ]),
                        ],
                    ])
                    ->add('buttonLabel', TextType::class, [
                        'label' => 'Libellé du bouton (optionnel)',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('url', TextType::class, [
                        'label' => 'Url du bouton (optionnel)',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                ;
            }
            if (null === $content?->getParent()) {
                $form
                    ->add('backgrounds', BackgroundsType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
            'route' => null,
        ]);
    }
}
