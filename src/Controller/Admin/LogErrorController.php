<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\LogError;
use App\Repository\LogErrorRepository;
use App\Service\PaginatorService;
use App\ViewModel\LogErrorPresenter;
use App\ViewModel\LogErrorsPresenter;
use App\ViewModel\Paginator\PaginatorPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogErrorController extends AbstractController
{
    public function __construct(
        private PaginatorService $paginator,
        private PaginatorPresenter $paginatorPresenter,
        private LogErrorsPresenter $presenter,
        private LogErrorRepository $logErrorRepository
    ) {
    }

    #[Route('/admin/log/errors/{statusCode}', name: 'admin_log_errors', methods: ['GET'], defaults:['statusCode' => 500])]
    public function list(
        Request $request,
        int $statusCode
    ): Response {
        $query = $this->logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $this->presenter->present($errors);
        $this->paginatorPresenter->present($errors, ['statusCode' => $statusCode]);

        return $this->render('log_error/admin/list.html.twig', [
            'errors' => $this->presenter->viewModel()->logErrors,
            'tabs' => $this->presenter->viewModel()->tabs,
            'status_code' => $statusCode,
            'paginator' => $this->paginatorPresenter->viewModel(),
        ]);
    }

    #[Route('/admin/log/error/show/{error}', name: 'admin_log_error', methods: ['GET'])]
    public function show(
        LogErrorPresenter $presenter,
        LogError $error
    ): Response {
        $presenter->present($error);

        return $this->render('log_error/admin/show.html.twig', [
            'error' => $presenter->viewModel(),
        ]);
    }

    #[Route('/admin/log/error/delete/{error}', name: 'admin_log_error_delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request,
        LogError $error
    ): Response {
        $statusCode = $error->getStatusCode();

        $entityManager->remove($error);
        $entityManager->flush();

        $query = $this->logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $this->presenter->present($errors);
        $this->paginatorPresenter->present($errors, ['statusCode' => $statusCode], 'admin_log_errors');

        return $this->render('log_error/admin/list.html.twig', [
            'errors' => $this->presenter->viewModel()->logErrors,
            'tabs' => $this->presenter->viewModel()->tabs,
            'status_code' => $statusCode,
            'paginator' => $this->paginatorPresenter->viewModel(),
        ]);
    }

    #[Route('/admin/log/errors/delete/{statusCode}', name: 'admin_log_errors_delete', methods: ['GET'])]
    public function deleteAll(
        Request $request,
        int $statusCode
    ): Response {
        $this->logErrorRepository->deletAllBySatusCode($statusCode);

        $query = $this->logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $this->presenter->present($errors);
        $this->paginatorPresenter->present($errors, ['statusCode' => $statusCode], 'admin_log_errors');
        
        return $this->render('log_error/admin/list.html.twig', [
            'errors' => $this->presenter->viewModel()->logErrors,
            'tabs' => $this->presenter->viewModel()->tabs,
            'status_code' => $statusCode,
            'paginator' => $this->paginatorPresenter->viewModel(),
        ]);
    }
}
