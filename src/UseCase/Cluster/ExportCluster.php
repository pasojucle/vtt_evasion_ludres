<?php

declare(strict_types=1);

namespace App\UseCase\Cluster;

use App\Entity\Cluster;
use App\Entity\RegistrationStep;
use App\Service\PdfService;
use App\Service\StringService;
use App\ViewModel\ClusterPresenter;
use App\ViewModel\ClusterViewModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ExportCluster
{
    private array $files = [];
    private ClusterViewModel $cluster;
    private string $dirName;

    public function __construct(
        private StringService $stringService,
        private ClusterPresenter $presenter,
        private PdfService $pdfService,
        private Environment $twig,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function execute(Cluster $cluster): Response
    {
        $this->presenter->present($cluster);
        $this->cluster = $this->presenter->viewModel();

        $this->dirName = $this->parameterBag->get('tmp_directory_path') . $this->stringService->clean($this->cluster->title);
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
                        'media' => RegistrationStep::RENDER_FILE,
                    ]);
                    $tmp = $session['user']->entity->getId() . '_tmp';
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
        $fileName = $this->stringService->clean($fileName) . '.pdf';
        $pathName = $this->pdfService->joinPdf($this->files, null, $this->parameterBag->get('tmp_directory_path') . $this->stringService->clean($this->cluster->title) . '.pdf');
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
