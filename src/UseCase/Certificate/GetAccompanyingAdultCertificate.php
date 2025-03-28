<?php

declare(strict_types=1);

namespace App\UseCase\Certificate;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Service\MessageService;
use App\Service\PdfService;
use App\Service\ReplaceKeywordsService;
use App\Service\StringService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class GetAccompanyingAdultCertificate
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private PdfService $pdfService,
        private StringService $stringService,
        private ReplaceKeywordsService $replaceKeywordsService,
        private Environment $twig,
        private MessageService $messageService,
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function execute(Request $request, User $user, ?string $content = null): array
    {
        $filename = null;

        $user = $this->userDtoTransformer->fromEntity($user);
        if (null === $content) {
            $content = $this->getContent($user);
        }
        
        if (!$request->isXmlHttpRequest() && $content) {
            $filename = $this->makePdf($content);
        }

        return [$content, $filename];
    }

    private function getContent(UserDto $user)
    {
        $content = $this->messageService->getMessageByName('ACCOMPANYING_ADULT_CERTIFICATE');

        return $this->replaceKeywordsService->replace($content, $user, RegistrationStep::RENDER_FILE);
    }

    private function makePdf(string $content): string
    {
        $renderPdf = $this->twig->render('certificate/certificate_pdf.html.twig', [
            'title' => 'Attestation adulte accompagnateur',
            'content_class' => 'accompanying-adult-cerficate',
            'content' => $content,
        ]);
        $filename = 'Attestation d\'adulte accompaganteur';

        $this->pdfService->makePdf($renderPdf, $filename, $this->parameterBag->get('data_directory_path'));

        return $this->stringService->clean($filename) . '.pdf';
    }
}
