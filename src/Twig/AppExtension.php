<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\RegistrationStep;
use IntlDateFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('imgPath', [$this, 'imgPath']),
            new TwigFilter('formatDateLong', [$this, 'formatDateLong']),
        ];
    }

    public function imgPath($content, $media)
    {
        if (RegistrationStep::RENDER_FILE === $media) {
            $pattern = '#src="\/images#';
            $replace = 'src="./images';
            $content = preg_replace($pattern, $replace, $content);
        }

        return $content;
    }

    public function formatDateLong($date): string
    {
        $formatter = new IntlDateFormatter('fr_fr', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE');

        return ucfirst($formatter->format($date)).' '.$date->format('d/m/Y');
    }
}
