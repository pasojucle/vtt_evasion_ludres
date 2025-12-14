<?php

declare(strict_types=1);

namespace App\UseCase\Cluster;

use App\Dto\ClusterDto;
use App\Dto\DtoTransformer\ClusterDtoTransformer;
use App\Entity\Cluster;
use App\Entity\Enum\DisplayModeEnum;
use App\Entity\RegistrationStep;
use App\Service\PdfService;
use App\Service\ProjectDirService;
use App\Service\StringService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ExportCluster
{
    private array $files = [];
    private ClusterDto $cluster;
    private string $dirName;

    public function __construct(
        private StringService $stringService,
        private ClusterDtoTransformer $clusterDtoTransformer,
        private PdfService $pdfService,
        private Environment $twig,
        private ProjectDirService $projectDir,
    ) {
    }

    public function execute(Cluster $cluster): Response
    {
        $this->cluster = $this->clusterDtoTransformer->fromEntity($cluster);

        $this->dirName = $this->projectDir->path('tmp', $this->stringService->clean($this->cluster->title));

        if (!is_dir($this->dirName)) {
            mkdir($this->dirName);
        }
        $this->addSessions();

        return $this->getResponse($cluster);
    }

    private function addSessions(): void
    {
        foreach ($this->cluster->sessions as $session) {
            if ($session['isPresent']) {
                $render = $this->twig->render('cluster/export.html.twig', [
                    'user' => $session['user'],
                    'media' => DisplayModeEnum::FILE,
                ]);
                $tmp = $session['user']->id . '_tmp';
                $pdfFilepath = $this->pdfService->makePdf($render, $tmp, $this->dirName, 'B6');
                $this->files[] = [
                    'filename' => $pdfFilepath,
                ];
            }
        }
    }

    private function getResponse(Cluster $cluster): Response
    {
        $fileName = $this->cluster->title . '_' . $cluster->getBikeRide()->getStartAt()->format('Ymd');
        $fileName = $this->stringService->clean($fileName) . '.pdf';
        $pathName = $this->pdfService->joinPdf($this->files, null, null, $this->projectDir->path('tmp', $fileName));
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
