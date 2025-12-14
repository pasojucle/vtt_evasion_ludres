<?php

declare(strict_types=1);

namespace App\Twig;

use DateTime;
use Twig\TwigFilter;
use DateTimeImmutable;
use IntlDateFormatter;
use App\Entity\RegistrationStep;
use App\Entity\Enum\DisplayModeEnum;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('imgPath', [$this, 'imgPath']),
            new TwigFilter('formatDateLong', [$this, 'formatDateLong']),
            new TwigFilter('parseInt', [$this, 'parseInt']),
            new TwigFilter('base64Encode', [$this, 'base64Encode']),
        ];
    }

    public function imgPath($content, $media = DisplayModeEnum::SCREEN)
    {
        if (DisplayModeEnum::FILE === $media) {
            $pattern = ['#\/images\/#', '#\/uploads\/#', '#\/logos\/#'];
            $replace = ['./images/', './uploads/', './logos/'];
            $content = preg_replace($pattern, $replace, $content);
        }
        
        return $content;
    }

    public function formatDateLong(DateTime|DateTimeImmutable $date): string
    {
        $formatter = new IntlDateFormatter('fr_fr', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE d/M/yy');

        return ucfirst($formatter->format($date));
    }

    public function parseInt(string|bool $value): int
    {
        return (int) $value;
    }

    public function base64Encode(string $value): string
    {
        return base64_encode($value);
    }
}
