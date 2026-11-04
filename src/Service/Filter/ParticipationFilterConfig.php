<?php

declare(strict_types=1);

namespace App\Service\Filter;

use App\Core\Contract\Filter\AdvancedFilterFlields;
use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\ParticipationFilter;
use App\Entity\BikeRideType;
use App\Entity\Enum\PracticeEnum;
use App\Form\ChoiceProvider\LevelChoiceProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ParticipationFilterConfig implements FilterConfigInterface
{
    public function __construct(
        private LevelChoiceProvider $levelChoiceProvider,
    ) {
    }

    public function getRouteName(): string
    {
        return 'admin_participation_list';
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
                name: 'type',
                type: EntityType::class,
                options: [
                    'label' => false,
                    'class' => BikeRideType::class,
                    'autocomplete' => true,
                    'placeholder' => 'Tous les types d\'activité',
                    'required' => false,
                ],
            ),
        ];
    }

    public function getAdvancedFields(): array
    {
        $period = AdvancedFilterFlields::createPeriodRangeChip();
        return [
            AdvancedFilterFlields::createDateField('startAt', 'Date de début', $period),
            AdvancedFilterFlields::createDateField('endAt', 'Date de fin', $period),
            AdvancedFilterFlields::createLevelsField($this->levelChoiceProvider),
            new FilterFieldConfig(
                name: 'practice',
                type: EnumType::class,
                options: [
                    'label' => 'Partique',
                    'class' => PracticeEnum::class,
                    'required' => false,
                ],
            ),
            AdvancedFilterFlields::createItemsPerPageField(),
        ];
    }

    public function getDataClass(): ?string
    {
        return ParticipationFilter::class;
    }
}
