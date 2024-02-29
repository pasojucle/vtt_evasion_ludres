<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UploadFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uploadFile', FileType::class, [
                'label' => false,
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/bmp',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Format image bmp, jpeg ou png autorisé',
                    ]),
                ],
            ]);
    }
}
