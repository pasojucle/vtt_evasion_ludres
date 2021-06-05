<?php

namespace App\Service;

use DateTime;
use Dompdf\Dompdf;
use App\Entity\User;
use mikehaertl\pdftk\Pdf;
use App\DataTransferObject\User as UserDto;


class PdfService
{
    private FilenameService $filenameService;
    public function __construct(
        FilenameService $filenameService
    )
    {
        $this->filenameService = $filenameService;
    }

    public function makePdf(string $html, string $filename)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        $publicDirectory = '../data/licences';
        $pdfFilepath =  $publicDirectory .$this->filenameService->clean($filename).'.pdf';
        file_put_contents($pdfFilepath, $output);

        return $pdfFilepath;
    }

    public function joinPdf(array $filesToAdd)
    {
        $pdf = new Pdf();
        if (!empty($filesToAdd)) {
            foreach ($filesToAdd as $file) {
                $pdf->addFile($file);
            }
        }

        $result = $pdf
            ->execute();

        if ($result === false) {
            $error = $pdf->getError();
            dump($error);
        }
        return $pdf;
    }

    public function setFillFormData(Pdf $pdf, User $user) 
    {
        /**@var UserDto $userDto */
        $userDto = new UserDTO($user);
        $fullName = $userDto->getFullName();
        $bithDate = $userDto->getBithDate();
        $fullNameKinShip = $userDto->getFullNameKinShip();
        $bithDateKinShip= $userDto->getBithDateKinship();
        //$coverage = $translator->trans(Licence::COVERAGES[$user->getCoverage($this->currentseason)]);
        $today = new DateTime();
        // Pour éditer les champs
        $pdfTemp = new Pdf($pdf);
        $data = $pdfTemp->getDataFields();
        $error = null;
        if ($data === false) {
            $error = $pdfTemp->getError();
        }
        if (null === $error && !empty($data->__toArray())) {
            dump($data->__toArray());
        }

        $data = [
            'Nom et prénom'=> $fullName,
            'née le' => $bithDate,
            'Je soussigné Père mère ou représentant légal 1' => $fullNameKinShip,
            'Date de naissance'=> $bithDateKinShip,
            'Fait à'=> 'Ludres',
            'le' => $today->format('d/m/Y'),
            'Bouton radio' => 'Grand',
        ];

        $fillFormPdf = new Pdf($pdf);
        $result = $fillFormPdf 
            ->fillForm($data)
            ->needAppearances()
            ->flatten()
            ->execute();
        // ->saveAs($pdfFilename);
        if ($result === false) {
            $error = $pdf->getError();
            dump($error);
        }
        return $fillFormPdf;
    }
}