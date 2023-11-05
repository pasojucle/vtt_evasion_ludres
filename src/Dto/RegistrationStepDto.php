<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\RegistrationStep;
use Symfony\Component\Form\FormInterface;

class RegistrationStepDto
{
    public const OUTPUT_FILENAME_CLUB = 0;
    public const OUTPUT_FILENAME_PERSONAL = 1;
    public const OUTPUT_FILENAMES = [
        self::OUTPUT_FILENAME_CLUB => 'A_remettre_au_club.pdf',
        self::OUTPUT_FILENAME_PERSONAL => 'A_conserver.pdf',
    ];

    public ?FormInterface $formObject;

    public ?RegistrationStep $entity;

    public ?string $template;

    public ?string $class;

    public null|string|array $content;

    public ?string $overviewTemplate;

    public ?string $pdfFilename = null;

    public ?string $pdfRelativePath = null;

    public ?string $pdfPath = null;

    public ?string $title = '';

    public ?int $form;

    public ?array $registrationDocumentForms;

    public int $outputFilename = self::OUTPUT_FILENAME_CLUB;
}
