<?php

namespace App\Form;

use App\Entity\Link;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, [
                'label' => 'Url du lien',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregister',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
            ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $link = $event->getData();
            $form = $event->getForm();
            if (null !== $link) {
                $form
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('description', TextareaType::class, [
                        'label' => 'Description',
                        'row_attr' => [
                            'class' => 'form-group',
                        ],
                        'required' => false,
                    ])
                    ->add('image', TextType::class, [
                        'label' => 'Url ou nom de l\'image',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('imageFile', FileType::class, [
                        'label' => false,
                        'mapped' => false,
                        'required' => false,
                        'attr' => [
                            'accept' => '.bmp,.jpeg,.jpg,.png,.gif,.svg'
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    'image/bmp',
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                    'image/svg+xml',
                                ],
                                'mimeTypesMessage' => 'Format image bmp, jpeg ou png autorisé',
                            ])
                        ],
                    ])
                    ->add('isDisplayHome', CheckboxType::class, [
                        // 'data' => (bool)$value,
                        'label' => 'Afficher en page d\'accueil',
                        'block_prefix' => 'switch',
                        'required' => false,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Link::class,
        ]);
    }
}
