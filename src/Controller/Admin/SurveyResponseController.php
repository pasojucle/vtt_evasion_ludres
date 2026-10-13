<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ListDrawerHandler;
use App\Dto\Filter\SurveyResponseFilter;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\State\SurveyResponse\Provider\SurveyResponcesReadProvider;
use App\State\SurveyResponse\Provider\SurveyResponseAdminProvider;
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
        SurveyResponseAdminProvider $provider,
        Survey $survey
    ): Response {
        return $this->render('survey_response/admin/list.html.twig', [
            'list' => $provider->getCollection($survey)
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
}
