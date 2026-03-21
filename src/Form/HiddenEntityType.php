<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Cluster;
use App\Form\Transformer\HiddenEntityTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Entity hidden custom type class definition.
 */
class HiddenEntityType extends HiddenType
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats

        $builder->addModelTransformer(new HiddenEntityTransformer($this->entityManager, Cluster::class));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('class');
    }
}
