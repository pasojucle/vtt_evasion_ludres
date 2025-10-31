<?php

declare(strict_types=1);

namespace App\Workflow\MarkingStore;

use App\Entity\Enum\LicenceStateEnum;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

class EnumMarkingStore implements MarkingStoreInterface
{
    public function __construct(
        private string $property = 'state'
    ) {
    }

    public function getMarking(object $subject): Marking
    {
        $getter = 'get' . ucfirst($this->property);
        $enumValue = $subject->$getter();
        
        $value = $enumValue instanceof \BackedEnum ? $enumValue->value : (string) $enumValue;
        
        return new Marking([$value => 1]);
    }

    public function setMarking(object $subject, Marking $marking, array $context = []): void
    {
        $value = key($marking->getPlaces());
        $setter = 'set' . ucfirst($this->property);
        
        $subject->$setter(LicenceStateEnum::tryFrom($value));
    }
}
