<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Survey;
use App\Entity\SurveyResponse;
use App\State\SurveyResponse\Provider\SurveyResponseAdminProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reponse/sondage', name: 'admin_survey_response_')]
class SurveyResponseController extends AbstractController
{
    #[Route('s/{survey}', name: 'list', methods: ['GET'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function list(
        SurveyResponseAdminProvider $provider,
        Survey $survey
    ): Response {
        return $this->render('survey_response/admin/list.html.twig', [
            'list' => $provider->getCollection($survey)
        ]);
    }

    #[Route('/show/{surveyResponse}', name: 'show', methods: ['GET'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function show(
        SurveyResponse $surveyResponse
    ): Response {
        return $this->render('survey_response/admin/list.html.twig', [
            'list' => []
        ]);
    }
}
