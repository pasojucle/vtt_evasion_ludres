<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LogErrorDto;
use App\Entity\LogError;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorDtoTransformer
{
    public array $tabs = [
        500 => 'Erreur d\'application',
        404 => 'Page inexistantes',
        403 => 'Problème d\'authorisation',
    ];

    public function __construct(
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function fromEntity(LogError $logError): LogErrorDto
    {
        $logErrorDto = new LogErrorDto();
        $logErrorDto->id = $logError->getId();
        $createdAt = $logError->getCreatedAt();
        $logErrorDto->createdAt = $createdAt->format('d/m/Y H:i:s');
        $logErrorDto->statusCode = $logError->getStatusCode();
        $logErrorDto->errorMessage = $logError->getErrorMessage();
        $logErrorDto->message = $logError->getMessage();
        $logErrorDto->userAgent = $logError->getUserAgent();
        $logErrorDto->route = $logError->getRoute();
        $logErrorDto->url = $logError->getUrl();
        $logErrorDto->fileName = $logError->getFileName();
        $logErrorDto->line = $logError->getLine();
        $logErrorDto->user = ($logError->getUserId())
            ? $this->userDtoTransformer->getHeaderFromEntity($this->userRepository->find($logError->getUserId()))
            : null;

        return $logErrorDto;
    }

    public function fromEntities(Paginator $logErrorEntities): array
    {
        $logErrors = [];
        foreach ($logErrorEntities as $logError) {
            $logErrors[] = $this->fromEntity($logError);
        }
   
        return $logErrors;
    }
}
