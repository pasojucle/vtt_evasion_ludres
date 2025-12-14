<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum DisplayModeEnum: string implements TranslatableInterface
{
    case NONE = 'none';

    case SCREEN = 'screen';

    case FILE = 'file';

    case SCREN_AND_FILE = 'screen_and_file';

    case FILE_AND_LINK = 'file_and_link';


    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('display_mode.' . $this->value, locale: $locale);
    }
}
