<?php

declare(strict_types=1);

namespace App\UseCase\Cluster;

use App\Entity\Cluster;
use App\Repository\SessionRepository;
use App\Service\FilenameService;
use App\Service\PdfService;
use App\ViewModel\ClusterPresenter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ExportCluster
{
    private array $files = [];
    private Cluster $cluster;
    private string $dirName;

    public function __construct(
        private SessionRepository $sessionRepository,
        private FilenameService $filenameService,
        private ClusterPresenter $presenter,
        private PdfService $pdfService,
        private Environment $twig,
        private ParameterBagInterface $parameterBag
    ) {
    }

    private const SEPARATOR = ',';

    public function execute(Cluster $cluster): Response
    {
        $this->presenter->present($cluster);
        $this->cluster = $this->presenter->viewModel();
        $this->dirName = $this->parameterBag->get('tmp_directory_path') . $this->filenameService->clean($this->presenter->viewModel()->title);
        if (!is_dir($this->dirName)) {
            mkdir($this->dirName);
        }
        $this->addSessions();

        return $this->getResponse();
    }

    private function addSessions(): void
    {
        if (!empty($this->cluster->sessions)) {
            foreach ($this->cluster->sessions as $session) {
                if ($session['isPresent']) {
                    $render = $this->twig->render('cluster/export.html.twig', [
                        'user' => $session['user'],
                    ]);
                    $tmp = $session['user']->id . '_tmp';
                    $pdfFilepath = $this->pdfService->makePdf($render, $tmp, $this->dirName, 'B6');
                    $this->files[] = [
                        'filename' => $pdfFilepath,
                    ];
                }
            }
        }
    }

    private function getResponse(): Response
    {
        $fileName = $this->cluster->title . '_' . $this->cluster->entity->getBikeRide()->getStartAt()->format('Ymd');
        $fileName = $this->filenameService->clean($fileName) . '.pdf';
        $pathName = $this->pdfService->joinPdf($this->files, null, $this->parameterBag->get('tmp_directory_path') . $this->filenameService->clean($this->cluster->title) . '.pdf');
        $fileContent = file_get_contents($pathName);
        $response = new Response($fileContent);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );

        (new Filesystem())->remove($this->dirName);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
