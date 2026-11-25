<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Service\PdfService;
use App\Service\ProjectDirService;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class MemberRegistrationCertificateMapper
{
    public function __construct(
        private Environment $twig,
        private PdfService $pdfService,
        private ProjectDirService $projectDir,
        private Filesystem $filesystem,
    ) {
    }

    public function streamToPdf(string $content): void
    {
        $dirName = $this->projectDir->path('tmp', uniqid());

        if (!$this->filesystem->exists($dirName)) {
            $this->filesystem->mkdir($dirName);
        }

        $renderPdf = $this->twig->render('certificate/certificate_pdf.html.twig', [
            'title' => 'Attestation licence sportive annuelle',
            'content_class' => 'registration-cerficate',
            'content' => $content,
        ]);
        $filename = 'Attestation d\'inscription';
        $pathName = $this->pdfService->makePdf($renderPdf, $filename, $dirName);
        try {
            if ($this->filesystem->exists($pathName)) {
                dump($pathName);
                readfile($pathName);
                flush();
            }
        } finally {
            $this->filesystem->remove($dirName);
        }
    }
}
