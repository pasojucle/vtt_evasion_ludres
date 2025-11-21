<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\RegistrationStep;
use DateTime;
use DateTimeImmutable;
use IntlDateFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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

    public function imgPath($content, $media = RegistrationStep::RENDER_VIEW)
    {
        if (RegistrationStep::RENDER_FILE === $media) {
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
