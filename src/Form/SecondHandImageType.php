<?php

namespace App\Form;

use App\Entity\SecondHandImage;
use App\Service\ProjectDirService;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class SecondHandImageType extends AbstractType
{
    public function __construct(
        private readonly ProjectDirService $projectDir, 
    )
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $image = $event->getData();
            $path = $filename = '';
            if ($image && $image->getFilename()) {
                $path = $this->projectDir->dir('', 'second_hands', $image->getFilename());
                $filename = $image->getFilename();
            }
            $event->getForm()
                ->add('uploadFile', FileType::class, [
                    'label' => false,
                    'mapped' => false,
                    'required' => false,
                    'block_prefix' => 'collection_file',
                    'attr' => [
                        'accept' => '.bmp,.jpeg,.jpg,.png',
                        'data-path' => $path,
                        'data-filename' => $filename,
                    ],
                    'row_attr' => [
                        'class' => 'second-hand form-group-collection',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '2048k',
                            'mimeTypes' => [
                                'image/bmp',
                                'image/jpeg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => 'Format image bmp, jpeg, png ou pdf autorisé',
                        ]),
                    ],
                ])
            ;
        }); 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecondHandImage::class,
        ]);
    }
}
