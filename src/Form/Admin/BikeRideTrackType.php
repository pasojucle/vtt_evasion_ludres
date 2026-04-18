<?php

namespace App\Form\Admin;

use App\Entity\BikeRideTrack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\File;

class BikeRideTrackType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $track = $event->getData();
            $filename = $track?->getFileName();
            $thumbnail = $track?->getThumbnail();
            $form
                ->add('label', TextType::class, [
                    'label' => 'Libellé',
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ])
                ->add('file', FileType::class, [
                    'label' => 'Fichier GPX',
                    'mapped' => false,
                    'required' => null === $filename,
                    'block_prefix' => 'custom_file',
                    'attr' => [
                        'accept' => '.gpx',
                        'filename' => $filename,
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline form-group-file',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '2048k',
                            'mimeTypes' => [
                                'application/gpx+xml',
                                'application/xml',
                                'text/xml',
                                'application/octet-stream',
                            ],
                            'mimeTypesMessage' => 'Format gpx autorisé',
                        ]),
                    ],
                ])
                ->add('thumbnailFile', FileType::class, [
                    'label' => 'Mignature',
                    'mapped' => false,
                    'required' => null === $thumbnail,
                    'block_prefix' => 'custom_file',
                    'attr' => [
                        'accept' => '.bmp,.jpeg,.jpg,.png',
                        'filename' => $thumbnail,
                        'preview' => $thumbnail
                            ? $this->urlGenerator->generate('admin_bike_ride_show_file', ['filename' => base64_encode($thumbnail), 'mimeType' => 'image'])
                            : null,
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline form-group-file',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '2048k',
                            'mimeTypes' => [
                                'image/bmp',
                                'image/jpeg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => 'Formats image bmp, jpeg, png autorisés',
                        ]),
                    ],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BikeRideTrack::class,
        ]);
    }
}
