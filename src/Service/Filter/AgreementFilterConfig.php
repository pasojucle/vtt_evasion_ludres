<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\AgreementFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AgreementFilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return 'admin_agreement_list';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        return null;
    }
    
    public function isPaginated(): bool
    {
        return true;
    }

    public function getFields(): array
    {
        return [
            new FilterFieldConfig(
                name: 'name',
                type: TextType::class,
                options: [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'Nom',
                        'data-action' => 'change->filter#submit'
                    ],
                ],
            )
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            AdvancedFilterFlields::createItemsPerPageField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return AgreementFilter::class;
    }
}
