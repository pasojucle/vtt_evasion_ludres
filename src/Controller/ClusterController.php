<?php

namespace App\Controller;

use App\Entity\Cluster;
use App\Service\PdfService;
use App\Service\EventService;
use App\Service\FilenameService;
use App\ViewModel\ClusterPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClusterController extends AbstractController
{
    /**
     * @Route("/admin/groupe/complete/{cluster}", name="admin_cluster_complete", options={"expose"=true})
     */
    public function adminClusterComplete(
        EntityManagerInterface $entityManager,
        EventService $eventService,
        Cluster $cluster
    ): Response
    {
        $cluster->setIsComplete(!$cluster->isComplete());
        $entityManager->flush();

        $event = $cluster->getEvent();

        return $this->render('event/cluster_show.html.twig', [
            'event' => $eventService->getEventWithPresentsByCluster($event),
            'events_filters' => [],
        ]);
    }

    /**
     * @Route("/admin/groupe/export/{cluster}", name="admin_cluster_export")
     */
    public function adminClusterExport(
        PdfService $pdfService,
        ClusterPresenter $presenter,
        FilenameService $filenameService,
        Cluster $cluster
    ): Response
    {
        $presenter->present($cluster);
        $files = [];
        $dirName = '../data/'.$filenameService->clean($presenter->viewModel()->title);
        if (!is_dir($dirName)) {
            mkdir($dirName);
        }
        if (!empty($presenter->viewModel()->sessions)) {
            foreach($presenter->viewModel()->sessions as $session) {
                $render = $this->renderView('cluster/export.html.twig', [
                    'user' => $session['user'],
                ]);
                $tmp = $session['user']->id.'_tmp';
                $pdfFilepath = $pdfService->makePdf($render, $tmp, $dirName, 'B6');
                $files[] = ['filename' => $pdfFilepath];
            }
        }

        $fileName = $cluster->getTitle().'_'.$cluster->getEvent()->getStartAt()->format('Ymd');
        $fileName = $filenameService->clean($fileName).'.pdf';
        $pathName = $pdfService->joinPdf($files, null, '../data/'.$filenameService->clean($cluster->getTitle()).'.pdf');
        $fileContent = file_get_contents($pathName);
        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );

        (new Filesystem)->remove($dirName);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
