<?php

declare(strict_types=1);

namespace App\UseCase\Certificate;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Service\MessageService;
use App\Service\PdfService;
use App\Service\ReplaceKeywordsService;
use App\Service\StringService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class GetRegistrationCertificate
{
    public function __construct(
        private PdfService $pdfService,
        private StringService $stringService,
        private Environment $twig,
        private MessageService $messageService,
        private ParameterBagInterface $parameterBag,
        private ReplaceKeywordsService $replaceKeywordsService,
        private UserDtoTransformer $userDtoTransformer,
    ) {
    }

    public function execute(Request $request, User $user, ?string $content = null): array
    {
        $filename = null;
        $userDto = $this->userDtoTransformer->fromEntity($user);

        if (null === $content) {
            $content = $this->getContent($userDto);
        }

        if (!$request->isXmlHttpRequest() && $content) {
            $filename = $this->makePdf($content);
        }

        return [$content, $filename];
    }

    private function getContent(UserDto $user)
    {
        if (Licence::CATEGORY_ADULT === $user->lastLicence->category) {
            $content = $this->messageService->getMessageByName('REGISTRATION_CERTIFICATE_ADULT');
        } else {
            $content = $this->messageService->getMessageByName('REGISTRATION_CERTIFICATE_SCHOOL');
        }

        return $this->replaceKeywordsService->replace($content, $user, RegistrationStep::RENDER_FILE);
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
