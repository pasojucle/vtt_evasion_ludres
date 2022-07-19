<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Background;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackgroundsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Background::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('b')
                    ->orderBy('b.filename', 'ASC');
            },
            'choice_label' => 'filename',
            'multiple' => true,
            'expanded' => true,
            'block_prefix' => 'thumbnail',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
