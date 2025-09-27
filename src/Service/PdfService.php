<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\RegistrationStepDto;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Form\UserType;
use DateTime;
use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;

class PdfService
{
    public function __construct(
        private StringService $ftringService,
        private ProjectDirService $projectDir,
        private UserDtoTransformer $userDtoTransformer,
    ) {
    }

    public function makePdf(string $html, string $filename, ?string $directory = null, string $paper = 'A4')
    {
        if (null === $directory) {
            $directory = $this->projectDir->path('tmp', 'licences');
        }

        $dompdf = new Dompdf();
        $dompdf->getOptions()->setChroot($this->projectDir->path('public'));
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        if (!is_dir($this->projectDir->path('data'))) {
            mkdir($this->projectDir->path('data'));
        }
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        $pdfFilepath = $directory . DIRECTORY_SEPARATOR . $this->ftringService->clean($filename) . '.pdf';

        file_put_contents($pdfFilepath, $output);

        return $pdfFilepath;
    }

    private function writeCoverage(Fpdi &$pdf, User $user): void
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);

        $coverage = [
            Licence::COVERAGE_MINI_GEAR => 50.5,
            Licence::COVERAGE_SMALL_GEAR => 60,
            Licence::COVERAGE_HIGH_GEAR => 73,
        ];

        
        $createdAt = ($userDto->lastLicence->isFinal) ? $userDto->lastLicence->createdAt : $userDto->lastLicence->testingAt;

        $fields = [
            [
                'value' => $userDto->ffctLicence->fullName,
                'x' => 35,
                'y' => 208,
            ],
            [
                'value' => $userDto->ffctLicence->birthDate,
                'x' => 165,
                'y' => 208,
            ],
            [
                'value' => $userDto->ffctLicence->fullNameChildren,
                'x' => 60,
                'y' => 213,
            ],
            [
                'value' => $userDto->ffctLicence->birthDateChildren,
                'x' => 165,
                'y' => 213,
            ],
            [
                'value' => 'VTT EVASION LUDRES',
                'x' => 65,
                'y' => 219,
            ],
            [
                'value' => 'X',
                'x' => $coverage[$userDto->lastLicence->coverage],
                'y' => 247.5,
            ],
            [
                'value' => 'X',
                'x' => 81,
                'y' => 257.5,
            ],
            [
                'value' => 'Ludres',
                'x' => 20,
                'y' => 262,
            ],
            [
                'value' => $createdAt ?? (new DateTime())->format('d/m/Y'),
                'x' => 75,
                'y' => 262,
            ],
        ];
        $this->writeFields($pdf, $fields);
    }


    private function writeHealthQuestion(Fpdi &$pdf): void
    {
        $fields = [
            [
                'value' => 'X',
                'x' => 52.3,
                'y' => 113.9,
            ],
            [
                'value' => 'X',
                'x' => 52.3,
                'y' => 197.7,
            ],
            [
                'value' => 'X',
                'x' => 52.3,
                'y' => 271.3,
            ],
        ];
        $this->writeFields($pdf, $fields);
    }

    private function writeFields(Fpdi &$pdf, array $fields): void
    {
        $pdf->SetFont('Helvetica');
        foreach ($fields as $field) {
            $pdf->SetAutoPageBreak(10);
            $pdf->SetXY($field['x'], $field['y']);
            $pdf->Write(8, iconv('UTF-8', 'ISO-8859-1', $field['value'] ?? ''));
        }
    }


    public function joinPdf(array $files, ?User $user = null, ?int $key = null, ?string $filename = null): string
    {
        if (null === $filename) {
            $filename = $this->projectDir->path('tmp', 'pdf_temp.pdf');
        }
        // initiate FPDI
        $pdf = new Fpdi();
        // iterate through the files
        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file['filename']);
            // iterate through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; ++$pageNo) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                // get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);

                // add a page with the same orientation and size
                $pdf->AddPage($size['orientation'], $size);

                // use the imported page
                $pdf->useTemplate($templateId);
                if (null !== $user && RegistrationStepDto::OUTPUT_FILENAME_CLUB === $key && UserType::FORM_LICENCE_COVERAGE === $file['form']) {
                    $this->writeCoverage($pdf, $user);
                }
                if (RegistrationStepDto::OUTPUT_FILENAME_PERSONAL === $key && UserType::FORM_HEALTH_QUESTION === $file['form'] && RegistrationStep::RENDER_FILE === $file['final_render']) {
                    $this->writeHealthQuestion($pdf);
                }
            }
        }

        $pdf->Output('F', $filename);

        return $filename;
    }
}
