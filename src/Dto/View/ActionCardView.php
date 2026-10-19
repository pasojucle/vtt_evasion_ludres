<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class ActionCardView
{
    public const string TOP = '_top';
    public const string MODAL_CONTENT = 'modal_content';
    public const string SHEET_CONTENT = 'sheet_content';

    /**
     * @param string $url
     * @param string $label
     * @param string $icon
     * @param HtmlAttributeView[] $htmlAttributes
     */
    public function __construct(
        public string $url,
        public string $label,
        public string $icon,
        public array $htmlAttributes = [
            new HtmlAttributeView('data-turbo-frame', self::TOP)
        ],
        public string $title = '',
    ) {
    }

    public function getName(): string
    {
        return 'link';
    }

    public function getTemplate(): string
    {
        return 'components/_action_card.html.twig';
    }
}
