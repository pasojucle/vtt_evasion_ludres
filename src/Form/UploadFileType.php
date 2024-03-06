<?php

declare(strict_types=1);

namespace App\Form;

use App\Service\UploadService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class UploadFileType extends AbstractType
{
    public function __construct(
        private UploadService $uploadService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxSize = $this->uploadService->getMaxAllowedUploadSize();
        $builder
            ->add('uploadFile', FileType::class, [
                'label' => false,
                'attr' => [
                    'accept' => '.bmp,.jpeg,.jpg,.png',
                    'data-max-size' => $maxSize['toBytes'],
                    'data-max-size-value' => $maxSize['value'],
                    'class' => 'drop-zone',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => $maxSize['toBytes'],
                        'mimeTypes' => [
                            'image/bmp',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Format image bmp, jpeg ou png autorisé',
                    ]),
                ],
            ])
            ->add('directory', HiddenSlideshowDirectoryType::class)
            ;
    }
}
