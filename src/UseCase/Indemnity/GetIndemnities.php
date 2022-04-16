<?php

declare(strict_types=1);

namespace App\UseCase\Indemnity;


class GetIndemnities
{
    public function __construct(
        private UserPresenter $presenter,
        private EntityManagerInterface $entityManager,
        private IdentityService $identityService,
        private UploadService $uploadService
    ) {
    }

    public function execute(array $indemnities): array
    {

    }
}