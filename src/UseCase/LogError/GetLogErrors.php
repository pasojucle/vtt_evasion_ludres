<?php

declare(strict_types=1);

namespace App\UseCase\LogError;

use App\Repository\LogErrorRepository;
use App\Service\PaginatorService;
use App\ViewModel\LogErrorsPresenter;
use App\ViewModel\Paginator\PaginatorPresenter;
use Symfony\Component\HttpFoundation\Request;

class GetLogErrors
{
    public function __construct(
        private PaginatorService $paginator,
        private PaginatorPresenter $paginatorPresenter,
        private LogErrorsPresenter $presenter,
        private LogErrorRepository $logErrorRepository
    ) {
    }

    public function execute(int $statusCode, Request $request): array
    {
        $query = $this->logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $this->presenter->present($errors);
        $this->paginatorPresenter->present($errors, ['statusCode' => $statusCode], 'admin_log_errors');

        return [
            'errors' => $this->presenter->viewModel()->logErrors,
            'tabs' => $this->presenter->viewModel()->tabs,
            'status_code' => $statusCode,
            'paginator' => $this->paginatorPresenter->viewModel(),
        ];
    }
}
