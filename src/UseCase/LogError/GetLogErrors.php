<?php

declare(strict_types=1);

namespace App\UseCase\LogError;

use App\Dto\DtoTransformer\LogErrorDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Repository\LogErrorRepository;
use App\Service\PaginatorService;
use Symfony\Component\HttpFoundation\Request;

class GetLogErrors
{
    public function __construct(
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
        private LogErrorDtoTransformer $logErrorDtoTransformer,
        private LogErrorRepository $logErrorRepository
    ) {
    }

    public function execute(int $statusCode, Request $request): array
    {
        $query = $this->logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        
        return [
            'errors' => $this->logErrorDtoTransformer->fromEntities($errors),
            'tabs' => $this->logErrorDtoTransformer->tabs,
            'status_code' => $statusCode,
            'paginator' => $this->paginatorDtoTransformer->fromEntities($errors, ['statusCode' => $statusCode], 'admin_log_errors'),
        ];
    }
}
