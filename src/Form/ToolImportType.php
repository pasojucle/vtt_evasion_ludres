<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ToolImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userList', FileType::class, [
                'label' => false,
                'attr' => [
                    'accept' => '.csv'
                ],
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
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
            ;
        
    }
}