<?php

namespace App\Service;

use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Licence;
use setasign\Fpdi\Fpdi;
use App\DataTransferObject\User as UserDto;
use Symfony\Component\HttpKernel\KernelInterface;

class PdfService
{
    private FilenameService $filenameService;
    private LicenceService $licenceService;
    private KernelInterface $kernel;

    public function __construct(
        FilenameService $filenameService,
        LicenceService $licenceService,
        KernelInterface $kernel
    )
    {
        $this->filenameService = $filenameService;
        $this->licenceService = $licenceService;
        $this->kernel = $kernel;
    }

    public function makePdf(string $html, string $filename)
    {
        $options = new Options();
        $options->setIsHtml5ParserEnabled(true);
        $dompdf = new Dompdf($options);
        $dompdf->getOptions()->setChroot($this->kernel->getProjectDir().'/public');
        dump($dompdf->getOptions());
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory = '../data/licences';
        $pdfFilepath =  $publicDirectory .$this->filenameService->clean($filename).'.pdf';
        file_put_contents($pdfFilepath, $output);

        return $pdfFilepath;
    }

    public function addData(Fpdi &$pdf, User $user )
    {
        /**@var UserDto $userDto */
        $userDto = new UserDto($user);
        $today = new DateTime();

        $coverage = [
            Licence::COVERAGE_MINI_GEAR => 50.5,
            Licence::COVERAGE_SMALL_GEAR => 60,
            Licence::COVERAGE_HIGH_GEAR => 73,
        ];

        $fields = [
            ['value' => $userDto->getFullName(), 'x' => 35, 'y' => 208],
            ['value' => $userDto->getBirthDate(), 'x' => 165, 'y' => 208],
            ['value' => $userDto->getFullNameChildren(), 'x' => 60, 'y' => 213],
            ['value' => $userDto->getBirthDateChildren(), 'x' => 165, 'y' => 213],
            ['value' => 'VTT EVASION LUDRES', 'x' => 65, 'y' => 219],
            ['value' => 'X', 'x' => $coverage[$userDto->getCoverage($this->licenceService->getCurrentSeason())], 'y' => 247.5],
            ['value' => 'X', 'x' => 81, 'y' => 257.5],
            ['value' => 'Ludres', 'x' => 20, 'y' => 262],
            ['value' => $today->format('d/m/Y'), 'x' => 75, 'y' => 262]
        ];

        $pdf->SetFont('Helvetica');
        foreach ($fields as $field) {
            $pdf->SetXY($field['x'], $field['y']);
            $pdf->Write(8, iconv('UTF-8', 'cp1250', $field['value']));
        }

        return $pdf;
    }

    public function joinPdf(array $files, User $user): string
    {
        $filename = '../data/pdf_temp.pdf';
        // initiate FPDI
        $pdf = new Fpdi();
        // iterate through the files
        foreach ($files AS $file) {
            $pageCount = $pdf->setSourceFile($file['filename']);
            // iterate through all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                // get the size of the imported page
                $size = $pdf->getTemplateSize($templateId);

                // add a page with the same orientation and size
                $pdf->AddPage($size['orientation'], $size);

                // use the imported page
                $pdf->useTemplate($templateId);
                if (3 == $pageNo && UserType::FORM_LICENCE === $file['form']) {
                    $this->addData($pdf, $user);
                }
            }
        }

        $pdf->Output('F', $filename);

        return $filename;
    }
}