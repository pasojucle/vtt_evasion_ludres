<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Service\SeasonService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class OverviewSaisonMemberType extends AbstractType
{
    public function __construct(
        private SeasonService $seasonService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('season', ChoiceType::class, [
                'label' => false,
                'choices' => $this->seasonService->getSeasons(),
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ;
    }
}
