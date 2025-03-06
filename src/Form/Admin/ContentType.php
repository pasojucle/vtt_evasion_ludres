<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Content;
use App\Form\Admin\EventListener\Content\ContentSubscriber;
use App\Repository\ContentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentType extends AbstractType
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ->add('route', HiddenType::class, [
                'empty_data' => 'home',
            ])
        ;
        $builder->addEventSubscriber(new ContentSubscriber($this->contentRepository, $this));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
            'route' => null,
            'allowed_kinds' => null,
        ]);
    }
}
