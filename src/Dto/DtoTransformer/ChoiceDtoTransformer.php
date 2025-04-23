<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ChoiceDto;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Level;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoiceDtoTransformer
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function fromEnum(PermissionEnum $enum): ChoiceDto
    {
        $choiceDto = new ChoiceDto();
        $choiceDto->id = $enum->value;
        $choiceDto->name = $enum->trans($this->translator);

        return $choiceDto;
    }

    public function fromLevel(?Level $level, array $options = []): ChoiceDto
    {
        $choiceDto = new ChoiceDto();
        $choiceDto->id = (array_key_exists('id', $options)) ? $options['id'] : $level?->getId();
        $choiceDto->name = (array_key_exists('title', $options)) ? $options['title'] : $level?->getTitle();
        $choiceDto->label = (array_key_exists('label', $options)) ? $this->translator->trans($options['label']) : null;
        $choiceDto->group = (array_key_exists('group', $options)) ? $options['group'] : null;
        $choiceDto->target = (array_key_exists('target', $options)) ? $options['target'] : null;
        $choiceDto->value = (array_key_exists('value', $options)) ? $options['value'] : null;

        return $choiceDto;
    }

    public function fromValue(int|string $value, string $prefix): ChoiceDto
    {
        $choiceDto = new ChoiceDto();
        $choiceDto->id = $value;
        $choiceDto->name = sprintf('%s %s', $prefix, $value);

        return $choiceDto;
    }
}
