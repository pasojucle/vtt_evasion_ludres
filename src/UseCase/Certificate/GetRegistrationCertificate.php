<?php

declare(strict_types=1);

namespace App\UseCase\Certificate;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Service\ParameterService;
use App\Service\PdfService;
use App\Service\ReplaceKeywordsService;
use App\Service\StringService;
use App\ViewModel\LicenceViewModel;
use App\ViewModel\UserPresenter;
use App\ViewModel\UserViewModel;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class GetRegistrationCertificate
{
    public function __construct(
        private UserPresenter $presenter,
        private PdfService $pdfService,
        private StringService $stringService,
        private Environment $twig,
        private ParameterService $parameterService,
        private ParameterBagInterface $parameterBag,
        private ReplaceKeywordsService $replaceKeywordsService
    ) {
    }

    public function execute(Request $request, User $user, ?string $content = null): array
    {
        $filename = null;

        $this->presenter->present($user);
        $user = $this->presenter->viewModel();
        $licence = $user->seasonLicence;
        $content = $this->getContent($user, $licence);


        if (!$request->isXmlHttpRequest() && $content) {
            $filename = $this->makePdf($content);
        }

        return [$content, $filename];
    }

    private function getContent(UserViewModel $user, LicenceViewModel $licence)
    {
        $today = new DateTime();
        $todayStr = $today->format('d/m/Y');

        if (Licence::CATEGORY_ADULT === $user->lastLicence->category) {
            $content = $this->parameterService->getParameterByName('REGISTRATION_CERTIFICATE_ADULT');
        // list($search, $replace) = $this->getMemberData($user, $licence, $todayStr);
        } else {
            $content = $this->parameterService->getParameterByName('REGISTRATION_CERTIFICATE_SCHOOL');
            // list($search, $replace) = $this->getKinShipData($user, $licence, $todayStr);
        }

        return $this->replaceKeywordsService->replace($user, $content, RegistrationStep::RENDER_FILE);
    }
    
    private function makePdf(string $content): string
    {
        $renderPdf = $this->twig->render('certificate/certificate_pdf.html.twig', [
            'title' => 'Attestation licence sportive annuelle',
            'content_class' => 'registration-cerficate',
            'content' => $content,
        ]);
        $filename = 'Attestation d\'inscription';
        $this->pdfService->makePdf($renderPdf, $filename, $this->parameterBag->get('data_directory_path'));

        return $this->stringService->clean($filename) . '.pdf';
    }
}
