<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Dto\ChoiceDto;
use App\Entity\Enum\EnumTrait;
use App\State\PermissionStateProvider;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ApiResource(shortName: 'Permission')]
#[GetCollection(
    name: 'permission_choices',
    output: ChoiceDto::class,
    provider: PermissionStateProvider::class,
)]
enum PermissionEnum: string implements TranslatableInterface
{
    case BIKE_RIDE_CLUSTER = 'bike_ride_cluster';
    case BIKE_RIDE = 'bike_ride';
    case USER = 'user';
    case PRODUCT = 'product';
    case SURVEY = 'survey';
    case MODAL_WINDOW = 'notification';
    case SECOND_HAND = 'second_hand';
    case PERMISSION = 'permission';
    case DOCUMENTATION = 'documentation';
    case SLIDESHOW = 'slideshow';
    case PARTICIPATION = 'participation';
    case SUMMARY = 'summary';

    use EnumTrait;

    public function isAdmin(): bool
    {
        return self::BIKE_RIDE_CLUSTER !== $this;
    }

    public static function getCase(Operation $operation, array $uriVariables)
    {
        $name = $uriVariables['id'] ?? null;

        return constant(self::class . "::$name");
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('permission.' . $this->value, locale: $locale);
    }
}
