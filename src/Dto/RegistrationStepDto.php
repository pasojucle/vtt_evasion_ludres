<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\RegistrationStep;
use Symfony\Component\Form\FormInterface;


class RegistrationStepDto
{
    public ?FormInterface $formObject;

    public ?RegistrationStep $entity;

    public ?string $template;

    public ?string $class;

    public null|string|array $content;

    public ?string $overviewTemplate;

    public ?string $filename;

    public ?string $title;

    public ?int $form;

    public ?array $registrationDocumentForms;
}