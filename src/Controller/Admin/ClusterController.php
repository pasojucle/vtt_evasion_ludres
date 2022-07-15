<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Cluster;
use App\Service\PdfService;
use App\Service\FilenameService;
use App\ViewModel\ClusterPresenter;
use App\ViewModel\BikeRidePresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ClusterController extends AbstractController
{
    public function __construct(private ParameterBagInterface $parameterBag)
    {
        
    }
    #[Route('/admin/groupe/complete/{cluster}', name: 'admin_cluster_complete', options:['expose' => true], methods: ['GET'])]
    public function adminClusterComplete(
        EntityManagerInterface $entityManager,
        BikeRidePresenter $presenter,
        Cluster $cluster
    ): Response {
        $cluster->setIsComplete(!$cluster->isComplete());
        $entityManager->flush();

        $bikeRide = $cluster->getBikeRide();
        $presenter->present($bikeRide);

        return $this->render('cluster/show.html.twig', [
            'bikeRide' => $presenter->viewModel(),
            'bike_rides_filters' => [],
        ]);
    }

    #[Route('/admin/groupe/export/{cluster}', name: 'admin_cluster_export', methods: ['GET'])]
    public function adminClusterExport(
        PdfService $pdfService,
        ClusterPresenter $presenter,
        FilenameService $filenameService,
        Cluster $cluster
    ): Response {
        $presenter->present($cluster);
        $files = [];
        $dirName = $this->parameterBag->get('tmp_directory_path').$filenameService->clean($presenter->viewModel()->title);
        if (!is_dir($dirName)) {
            mkdir($dirName);
        }
        if (!empty($presenter->viewModel()->sessions)) {
            foreach ($presenter->viewModel()->sessions as $session) {
                if ($session['isPresent']) {
                    $render = $this->renderView('cluster/export.html.twig', [
                        'user' => $session['user'],
                    ]);
                    $tmp = $session['user']->entity->getId().'_tmp';
                    $pdfFilepath = $pdfService->makePdf($render, $tmp, $dirName, 'B6');
                    $files[] = [
                        'filename' => $pdfFilepath,
                    ];
                }
            }
        }

        $fileName = $cluster->getTitle().'_'.$cluster->getBikeRide()->getStartAt()->format('Ymd');
        $fileName = $filenameService->clean($fileName).'.pdf';
        $pathName = $pdfService->joinPdf($files, null, $this->parameterBag->get('tmp_directory_path').$filenameService->clean($cluster->getTitle()).'.pdf');
        $fileContent = file_get_contents($pathName);
        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );

        (new Filesystem())->remove($dirName);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
