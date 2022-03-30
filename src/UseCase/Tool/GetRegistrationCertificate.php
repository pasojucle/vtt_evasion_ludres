<?php

declare(strict_types=1);

namespace App\UseCase\Tool;

use App\Entity\Licence;
use App\Entity\User;
use App\Service\FilenameService;
use App\Service\ParameterService;
use App\Service\PdfService;
use App\ViewModel\LicenceViewModel;
use App\ViewModel\UserPresenter;
use App\ViewModel\UserViewModel;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class GetRegistrationCertificate
{
    public function __construct(
        private UserPresenter $presenter,
        private PdfService $pdfService,
        private FilenameService $filenameService,
        private Environment $twig,
        private ParameterService $parameterService
    ) {
    }

    public function execute(Request $request, ?User $user, ?string $content = null): array
    {
        $filename = null;
        if ($user) {
            $this->presenter->present($user);
            $user = $this->presenter->viewModel();
            $licence = $user->seasonLicence;
        }
        if (!$content) {
            $content = $this->getContent($user, $licence);
        }
        if (!$request->isXmlHttpRequest() && $content) {
            $filename = $this->makePdf($content);
        }

        return [$filename, $content];
    }

    private function getContent(UserViewModel $user, LicenceViewModel $licence)
    {
        $today = new DateTime();
        $todayStr = $today->format('d/m/Y');

        if (Licence::CATEGORY_ADULT === $licence->category) {
            $content = $this->parameterService->getParameterByName('REGISTRATION_CERTIFICATE_ADULT');
            list($search, $replace) = $this->getMemberData($user, $licence, $todayStr);
        } else {
            $content = $this->parameterService->getParameterByName('REGISTRATION_CERTIFICATE_SCHOOL');
            list($search, $replace) = $this->getKinShipData($user, $licence, $todayStr);
        }

        return str_replace($search, $replace, $content);
    }

    private function getMemberData(UserViewModel $user, LicenceViewModel $licence, string $today): array
    {
        $address = $user->member->address->toString();

        $search = [
            '{{ nom_prenom }}',
            '{{ adresse }}',
            '{{ saison }}',
            '{{ numero_licence }}',
            '{{ montant }}',
            '{{ date }}',
        ];
        $replace = [
            $user->member->fullName,
            $address,
            $licence->season,
            $user->getLicenceNumber(),
            $licence->amount,
            $today,
        ];

        return [$search, $replace];
    }

    private function getKinShipData(UserViewModel $user, LicenceViewModel $licence, string $today): array
    {
        $address = $user->kinship['address']->toString();

        $search = [
            '{{ nom_prenom_parent }}',
            '{{ nom_prenom_enfant }}',
            '{{ adresse_parent }}',
            '{{ saison }}',
            '{{ numero_licence }}',
            '{{ montant }}',
            '{{ date }}',
        ];
        $replace = [
            $user->kinship->fullName,
            $user->member->fullName,
            $address,
            $licence->season,
            $user->getLicenceNumber(),
            $licence->amount,
            $today,
        ];

        return [$search, $replace];
    }

    private function makePdf(string $content): string
    {
        $renderPdf = $this->twig->render('tool/registration_certificate_pdf.html.twig', [
            'content' => $content,
        ]);
        $filename = 'Attestation d\'inscription';
        $dirname = 'registration_certificate';
        $this->pdfService->makePdf($renderPdf, $filename, '../public/'.$dirname);

        return DIRECTORY_SEPARATOR.$dirname.DIRECTORY_SEPARATOR.$this->filenameService->clean($filename).'.pdf';
    }
}
