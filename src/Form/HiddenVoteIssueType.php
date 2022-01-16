<?php

namespace App\Form;

use App\Entity\VoteIssue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Transformer\HiddenEntityTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;



/**
 * Entity hidden custom type class definition.
 */
class HiddenVoteIssueType extends HiddenType
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats

        $builder->addModelTransformer(new HiddenEntityTransformer($this->manager, VoteIssue::class));
    }
}