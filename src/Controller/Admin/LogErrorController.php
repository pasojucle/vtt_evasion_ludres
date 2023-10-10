<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\LogError;
use App\Service\PaginatorService;
use App\Repository\LogErrorRepository;
use App\UseCase\LogError\GetLogErrors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Dto\DtoTransformer\LogErrorDtoTransformer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogErrorController extends AbstractController
{
    public function __construct(
        private GetLogErrors $getLogErrors,
        private LogErrorRepository $logErrorRepository
    ) {
    }

    #[Route('/admin/log/errors/{statusCode}', name: 'admin_log_errors', methods: ['GET'], defaults:['statusCode' => 500])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        Request $request,
        int $statusCode
    ): Response {
        $params = $this->getLogErrors->execute($statusCode, $request);

        return $this->render('log_error/admin/list.html.twig', $params);
    }

    #[Route('/admin/log/error/show/{error}', name: 'admin_log_error', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(
        LogErrorDtoTransformer $logErrorDtoTransformer,
        LogError $error
    ): Response {
        return $this->render('log_error/admin/show.html.twig', [
            'error' => $logErrorDtoTransformer->fromEntity($error),
        ]);
    }

    #[Route('/admin/log/error/delete/{error}/{total}', name: 'admin_log_error_delete', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request,
        LogError $error,
        int $total
    ): Response {
        $statusCode = $error->getStatusCode();

        $entityManager->remove($error);
        $entityManager->flush();

        --$total;
        $currentPage = (int) $request->query->get('p');
        if (0 === $total % PaginatorService::PAGINATOR_PER_PAGE && $total / PaginatorService::PAGINATOR_PER_PAGE < $currentPage) {
            $request->query->set('p', --$currentPage);
        }

        $params = $this->getLogErrors->execute($statusCode, $request);

        return $this->render('log_error/admin/list.html.twig', $params);
    }

    #[Route('/admin/log/errors/delete/{statusCode}', name: 'admin_log_errors_delete', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAll(
        Request $request,
        int $statusCode
    ): Response {
        $this->logErrorRepository->deletAllBySatusCode($statusCode);

        $params = $this->getLogErrors->execute($statusCode, $request);

        return $this->render('log_error/admin/list.html.twig', $params);
    }
}
