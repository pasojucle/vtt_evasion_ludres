<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Transformer\HiddenEntityTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Entity hidden custom type class definition.
 */
class HiddenEntityType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new HiddenEntityTransformer($this->entityManager, $options['class']));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('class');
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
    
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
