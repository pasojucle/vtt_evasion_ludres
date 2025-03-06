<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ContentKindEnum: string implements TranslatableInterface
{
    case HOME_FLASH = 'home-flash';

    case HOME_CONTENT = 'home-content';

    case BACKGROUND_ONLY = 'background-only';

    case BACKROUND_AND_TEXT = 'background-and-text';

    case CARROUSEL_AND_TEXT = 'carrousel-and-text';

    case VIDEO_AND_TEXT = 'video-and-text';

    use EnumTrait;

    public function homeKinds(): array
    {
        return [self::HOME_FLASH, self::HOME_CONTENT, self::BACKGROUND_ONLY];
    }

    public function requireBackgrounds(): bool
    {
        if (in_array($this, [self::BACKGROUND_ONLY, self::BACKROUND_AND_TEXT, self::CARROUSEL_AND_TEXT])) {
            return true;
        }

        return false;
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('content.kind.' . $this->value, locale: $locale);
    }
}
