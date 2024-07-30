<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\RegistrationStep;
use App\Service\EnumService;
use DateTime;
use DateTimeImmutable;
use IntlDateFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly EnumService $enumService
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('imgPath', [$this, 'imgPath']),
            new TwigFilter('formatDateLong', [$this, 'formatDateLong']),
            new TwigFilter('parseInt', [$this, 'parseInt']),
            new TwigFilter('base64Encode', [$this, 'base64Encode']),
            new TwigFilter('enumTrans', [$this, 'enumTrans']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('enum', [$this, 'enum']),
        ];
    }

    public function imgPath($content, $media = RegistrationStep::RENDER_VIEW)
    {
        if (RegistrationStep::RENDER_FILE === $media) {
            $pattern = ['#\/images\/#', '#\/uploads\/#'];
            $replace = ['./images/', './uploads/'];
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

    public function enum(string $fullClassName): object
    {
        $parts = explode('::', $fullClassName);
        $className = $parts[0];
        $constant = $parts[1] ?? null;

        if (!enum_exists($className)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not an enum.', $className));
        }

        if ($constant) {
            return constant($fullClassName);
        }

        return new class($fullClassName) {
            public function __construct(private string $fullClassName)
            {
            }

            public function __call(string $caseName, array $arguments): mixed
            {
                return call_user_func_array([$this->fullClassName, $caseName], $arguments);
            }
        };
    }

    public function enumTrans(object $enum): string
    {
        return $this->enumService->translate($enum);
    }
}
