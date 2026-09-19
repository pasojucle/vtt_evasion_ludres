<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\ListActionViewInterface;
use App\Dto\Enum\ButtonType;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;

readonly class ButtonView implements ListActionViewInterface
{
    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';
    public const string SHEET_CONTENT = 'sheet_content';

    /**
     * @param ButtonType $type
     * @param ColorVariant $variant
     * @param RoundedVariant $rounded
     * @param ?string $label
     * @param string|null $icon
     * @param string|null $className
     * @param HtmlAttributView[] $htmlAttributes
     */
    public function __construct(
        public ButtonType $type = ButtonType::SUBMIT,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public RoundedVariant $rounded = RoundedVariant::ROUNDED,
        public Size $size = Size::SM,
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $className = null,
        public array $htmlAttributes = [
            new HtmlAttributView('data-turbo-frame', self::TOP)
        ],
        public string $title = '',
    ) {
    }

    public function getName(): string
    {
        return 'button';
    }

    public function getTemplate(): string
    {
        return 'components/_button.html.twig';
    }
}
