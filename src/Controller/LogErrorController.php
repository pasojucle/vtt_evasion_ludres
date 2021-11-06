<?php

namespace App\Controller;

use App\Entity\LogError;
use App\Service\PaginatorService;
use App\ViewModel\LogErrorPresenter;
use App\ViewModel\LogErrorsPresenter;
use App\Repository\LogErrorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogErrorController extends AbstractController
{
    /**
     * @Route("/admin/log/errors/{statusCode}", name="admin_log_errors", defaults = {"statusCode"=500})
     */
    public function list(
        PaginatorService $paginator,
        Request $request,
        LogErrorsPresenter $presenter,
        LogErrorRepository $logErrorRepository,
        int $statusCode
    ): Response
    {
        $query = $logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($errors);

        return $this->render('log_error/admin/list.html.twig', [
            'errors' => $presenter->viewModel()->logErrors,
            'tabs' => $presenter->viewModel()->tabs,
            'status_code' => $statusCode,
            'lastPage' => $paginator->lastPage($errors),
            'count' => $paginator->total($errors),
        ]);
    }

    /**
     * @Route("/admin/log/error/{error}", name="admin_log_error")
     */
    public function show(
        LogErrorPresenter $presenter,
        LogError $error
    ): Response
    {
        $presenter->present($error);

        return $this->render('log_error/admin/show.html.twig', [
            'error' => $presenter->viewModel(),
        ]);
    }

    /**
     * @Route("/admin/log/error/delete/{error}", name="admin_log_error_delete")
     */
    public function delete(
        EntityManagerInterface $entityManager,
        PaginatorService $paginator,
        LogErrorsPresenter $presenter,
        LogErrorRepository $logErrorRepository,
        Request $request,
        LogError $error
    ): Response
    {
        $statusCode = $error->getStatusCode();

        $entityManager->remove($error);
        $entityManager->flush();

        $query = $logErrorRepository->findLogErrorQuery($statusCode);
        $errors = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($errors);

        return $this->render('log_error/admin/list.html.twig', [
            'errors' => $presenter->viewModel()->logErrors,
            'tabs' => $presenter->viewModel()->tabs,
            'status_code' => $statusCode,
            'lastPage' => $paginator->lastPage($errors),
            'count' => $paginator->total($errors),
        ]);
    }
}
