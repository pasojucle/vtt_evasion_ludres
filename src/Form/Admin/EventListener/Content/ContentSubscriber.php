<?php

declare(strict_types=1);

namespace App\Form\Admin\EventListener\Content;

use App\Entity\Content;
use App\Entity\Enum\ContentKindEnum;
use App\Form\Admin\ContentType;
use App\Form\Type\BackgroundsType;
use App\Form\Type\TiptapType;
use App\Repository\ContentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\File;

class ContentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
        private readonly ContentType $contentType,
    ) {
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $content = $event->getData();
        $options = $form->getConfig()->getOptions();
        $allowedKins = $options['allowed_kinds'];
        $requiredUrl = ContentKindEnum::VIDEO_AND_TEXT === $content?->getKind();
        if (null === $content) {
            $parent = $this->contentRepository->findOneByRoute('home');
            $content = new Content();
            $content->setRoute('home')->setParent($parent);
            $event->setData($content);
        }

        if (ContentKindEnum::BACKGROUND_ONLY !== $content->getKind()) {
            $form
                ->add('content', TiptapType::class, [
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
                ->add('url', TextType::class, [
                    'label' => 'Url du bouton (optionnel)',
                    'required' => false,
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ])
                ->add('buttonLabel', TextType::class, [
                    'label' => 'Libellé du bouton (optionnel)',
                    'required' => false,
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ])
            ;
        }
   
        if ($allowedKins) {
            $form->add('kind', EnumType::class, [
                    'label' => 'Type',
                    'class' => ContentKindEnum::class,
                    'choice_filter' => ChoiceList::filter(
                        $this->contentType,
                        function (ContentKindEnum $kind) use ($allowedKins): bool {
                            return in_array($kind, $allowedKins, true);
                        },
                        $allowedKins
                    ),
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'attr' => [
                        'class' => 'form-modifier',
                        'data-modifier' => 'content_container',
                    ],
                    'auto_initialize' => false
                ]);
        }

        $requiredBackground = null === $content?->getParent() && $content->getKind()->requireBackgrounds();

        $this->modifier($form, $requiredUrl, $requiredBackground);
    }

    public function preSubmit(FormEvent $event): void
    {
        $content = $event->getData();
        $kind = ContentKindEnum::from($content['kind']);
        $requiredUrl = ContentKindEnum::VIDEO_AND_TEXT === $kind;
        $requiredBackground = $kind->requireBackgrounds();
        $this->modifier($event->getForm(), $requiredUrl, $requiredBackground);
    }

    private function modifier(FormInterface $form, bool $requiredUrl, bool $requiredBackground): void
    {
        if ($requiredUrl) {
            $form
                ->add('youtubeEmbed', TextareaType::class, [
                    'label' => 'Code d\'intégration',
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ]);
        } else {
            $form->remove('youtubeEmbed');
        }
        if ($requiredBackground) {
            $form
                ->add('backgrounds', BackgroundsType::class);
        } else {
            $form->remove('backgrounds');
        }
    }
}
