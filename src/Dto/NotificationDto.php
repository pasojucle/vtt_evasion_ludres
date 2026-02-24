<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Form\FormView;

class NotificationDto
{
    public ?string $index = null;

    public ?string $title = null;

    public ?string $content = null;

    public ?string $url = null;

    public ?string $target = null;

    public ?string $modalLink = null;

    public ?string $btnLabel = null;

    public array $action = ['name' => 'click->modal#close'];

    public ?FormView $form = null;
}
