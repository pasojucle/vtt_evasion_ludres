<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class ToolImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userList', FileType::class, [
                'label' => 'Fichier',
                'attr' => [
                    'accept' => '.csv',
                ],
                'block_prefix' => 'custom_file',
                // 'constraints' => [
                //     new File([
                //         'maxSize' => '1024k',
                //         'mimeTypes' => [
                //             'text/csv',
                //         ],
                //         'mimeTypesMessage' => 'Format csv autorisé',
                //     ])
                // ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Importer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ;
    }
}
