<?php

namespace App\UseCase\Tool;


use DateTime;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Licence;
use App\Service\PdfService;
use App\Service\FilenameService;
use App\ViewModel\UserPresenter;
use App\ViewModel\UserViewModel;
use App\Service\ParameterService;
use Symfony\Component\HttpFoundation\Request;

class GetRegistrationCertificate
{
    private UserPresenter $presenter;
    private PdfService $pdfService;
    private FilenameService $filenameService;
    private Environment $twig;
    private ParameterService $parameterService;

    public function __construct(
        UserPresenter $presenter,
        PdfService $pdfService,
        FilenameService $filenameService,
        Environment $twig,
        ParameterService $parameterService
    )
    {
        $this->presenter = $presenter;
        $this->pdfService = $pdfService;
        $this->filenameService = $filenameService;
        $this->twig = $twig;
        $this->parameterService = $parameterService;
    }

    public function execute(Request $request, ?User $user, ?string $content = null): array
    {
        $filename = null;
        if ($user) {
            $this->presenter->present($user);
            $user = $this->presenter->viewModel();
            $licence = $user->getSeasonLicence();
        }
        if (!$content) {
            $content = $this->getContent($user, $licence);
        }
        if (!$request->isXmlHttpRequest() && $content) {
            $filename = $this->makePdf($content);
        }

        return [$filename, $content];
    }

    private function getContent(UserViewModel $user, array $licence)
    {
        $today = new DateTime();
        $todayStr = $today->format('d/m/Y');

        if ($licence['category'] === Licence::CATEGORY_ADULT) {
            $content = $this->parameterService->getParameterByName('REGISTRATION_CERTIFICATE_ADULT');
            list($search, $replace) = $this->getMemberData($user, $licence, $todayStr);
        } else {
            $content = $this->parameterService->getParameterByName('REGISTRATION_CERTIFICATE_SCHOOL');
            list($search, $replace) = $this->getKinShipData($user, $licence, $todayStr);
        }

        return str_replace($search, $replace, $content);
    }

    private function getMemberData(UserViewModel $user, array $licence, string $today): array
    {
        
        $member = $user->getMember();
        $address = $member['address']->toString();

        $search  = [
            '{{ nom_prenom }}',
            '{{ adresse }}',
            '{{ saison }}',
            '{{ numero_licence }}',
            '{{ montant }}',
            '{{ date }}'
        ];
        $replace = [
            $member['fullName'], 
            $address,
            $licence['season'], 
            $user->getLicenceNumber(), 
            $licence['amount'], 
            $today,
        ];
        
        return [$search, $replace];
    }

    private function getKinShipData(UserViewModel $user, array $licence, string $today): array
    {
        $kinShip = $user->getKinShip();
        $member = $user->getMember();
        $address = $kinShip['address']->toString();

        $search  = [
            '{{ nom_prenom_parent }}',
            '{{ nom_prenom_enfant }}',
            '{{ adresse_parent }}',
            '{{ saison }}',
            '{{ numero_licence }}',
            '{{ montant }}',
            '{{ date }}'
        ];
        $replace = [
            $kinShip['fullName'], 
            $member['fullName'], 
            $address,
            $licence['season'], 
            $user->getLicenceNumber(), 
            $licence['amount'], 
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