<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Dto\HandlerContext;
use App\Core\Handler\ActionExportHandler;
use App\Core\Handler\JsonHandler;
use App\Core\Handler\ListDrawerHandler;
use App\Entity\Survey;
use App\State\SurveyResponse\Provider\SurveyResponcesReadProvider;
use App\State\SurveyResponse\Provider\SurveyResponseAdminProvider;
use App\State\SurveyResponse\Provider\SurveyResponseEmailProvider;
use App\State\SurveyResponse\Provider\SurveyResponseExportProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reponse/sondage', name: 'admin_survey_response_')]
class SurveyResponseController extends AbstractController
{
    #[Route('/{survey}', name: 'list', methods: ['GET'], requirements:['survey' => '\d+'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function list(
        Request $request,
        SurveyResponseAdminProvider $provider,
        Survey $survey
    ): Response {
        $context = HandlerContext::fromRequest($request);
        return $this->render('survey_response/admin/list.html.twig', [
            'list' => $provider->getCollection($survey, $context)
        ]);
    }

    #[Route('/show/{survey}', name: 'show', methods: ['GET', 'POST'], requirements:['survey' => '\d+'])]
    #[IsGranted('SURVEY_EDIT', subject: 'survey')]
    public function show(
        Request $request,
        SurveyResponcesReadProvider $provider,
        Survey $survey,
        ListDrawerHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
            $survey,
        );
    }

    #[Route('/export/{survey}', name: 'export', methods: ['GET'])]
    #[IsGranted('SURVEY_VIEW', 'survey')]
    public function export(
        SurveyResponseExportProvider $provider,
        Survey $survey,
        ActionExportHandler $handler,
    ): Response {
        return $handler->handle(
            $provider,
            $survey,
        );
    }

    #[Route('/emails/{survey}', name: 'email_to_clipboard', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function adminEmailSurvey(
        Request $request,
        Survey $survey,
        SurveyResponseEmailProvider $provider,
        JsonHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            null,
            $survey,
        );
    }
}
