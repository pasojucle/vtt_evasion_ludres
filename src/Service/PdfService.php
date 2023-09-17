<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\UserType;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfService
{
    public function __construct(
        private StringService $ftringService,
        private ProjectDirService $projectDir,
        private UserDtoTransformer $userDtoTransformer,
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function makePdf(string $html, string $filename, ?string $directory = null, string $paper = 'A4')
    {
        if (null === $directory) {
            $directory = $this->parameterBag->get('tmp_directory_path') . 'licences';
        }

        $dompdf = new Dompdf();
        $dompdf->getOptions()->setChroot($this->projectDir->path('public'));
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        if (!is_dir($this->parameterBag->get('data_directory_path'))) {
            mkdir($this->parameterBag->get('data_directory_path'));
        }
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        $pdfFilepath = $directory . DIRECTORY_SEPARATOR . $this->ftringService->clean($filename) . '.pdf';

        file_put_contents($pdfFilepath, $output);

        return $pdfFilepath;
    }

    public function addData(Fpdi &$pdf, User $user)
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);

        $coverage = [
            Licence::COVERAGE_MINI_GEAR => 50.5,
            Licence::COVERAGE_SMALL_GEAR => 60,
            Licence::COVERAGE_HIGH_GEAR => 73,
        ];

        $today = new DateTime();

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
                'value' => $userDto->lastLicence->createdAt ?? $today->format('d/m/Y'),
                'x' => 75,
                'y' => 262,
            ],
        ];

        $pdf->SetFont('Helvetica');
        foreach ($fields as $field) {
            $pdf->SetXY($field['x'], $field['y']);
            $pdf->Write(8, iconv('UTF-8', 'ISO-8859-1', $field['value'] ?? ''));
        }

        return $pdf;
    }

    public function joinPdf(array $files, ?User $user = null, ?string $filename = null): string
    {
        if (null === $filename) {
            $filename = $this->parameterBag->get('tmp_directory_path') . 'pdf_temp.pdf';
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
                if (null !== $user && 3 === $pageNo && UserType::FORM_LICENCE_COVERAGE === $file['form']) {
                    $this->addData($pdf, $user);
                }
            }
        }

        $pdf->Output('F', $filename);

        return $filename;
    }
}
