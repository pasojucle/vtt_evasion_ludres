<?php

namespace App\Form\Admin\EventListener\Document;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\File;

class AddMediaSubscriber implements EventSubscriberInterface
{
    private const MEDIA_FILE = 1;
    private const MEDIA_LINK = 2;

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
        $documentation = $event->getData();
        $media = ($documentation?->getLink()) ? self::MEDIA_LINK : self::MEDIA_FILE;
        $form
                ->add('media', ChoiceType::class, [
                    'label' => 'Type de média',
                    'choices' => [
                        'Fichier' => self::MEDIA_FILE,
                        'Lien Youtube' => self::MEDIA_LINK,
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'attr' => [
                        'class' => 'form-modifier',
                        'data-modifier' => 'mediaContainer',
                    ],
                    'data' => $media,
                    'mapped' => false,
                ]);


        $this->modifier($form, $media);
    }

    public function preSubmit(FormEvent $event): void
    {
        $documentation = $event->getData();

        $this->modifier($event->getForm(), $documentation['media']);
    }

    private function modifier(FormInterface $form, ?int $media): void
    {
        if (self::MEDIA_FILE === $media) {
            $form
                ->add('file', FileType::class, [
                    'label' => 'Télécharger un fichier',
                    'mapped' => false,
                    'required' => false,
                    'block_prefix' => 'custom_file',
                    'attr' => [
                        'accept' => '.bmp,.jpeg,.jpg,.png, .pdf, .mp4',
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '8196k',
                            'mimeTypes' => [
                                'image/bmp',
                                'image/jpeg',
                                'image/png',
                                'application/pdf',
                                'video/mp4',
                            ],
                            'mimeTypesMessage' => 'Format image bmp, jpeg, png, video mp4 ou pdf autorisé',
                        ]),
                    ],
                ])
                ->remove('link');
        } else {
            $form
                ->add('link', UrlType::class, [
                    'label' => 'Lien Youtube',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ])
                ->remove('file');
        }
    }
}
