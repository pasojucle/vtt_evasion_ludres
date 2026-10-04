<?php

declare(strict_types=1);

namespace App\Dto\View\User;

use App\Dto\View\Gardian\GardianExportView;


readonly class ParticipantExportView
{

    public function __construct(
        public string $picture,
        public string $fullName,
        public string $birthDate,
        public string $birthPlace,
        public ?string $birthDepartment,
        public string $birthCountry,
        public string $address,
        public string $email,
        public string $phones,
        public ?string $emergencyPhone,
        public ?string $emergencyContact,
        public ?GardianExportView $legalGardian,
        public ?GardianExportView $secondContact,
        public ?string $healthContent,
        public string $coverage,
    ) {
    }
}
