<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\SurveyDtoTransformer;
use App\Entity\Member;
use App\Entity\Survey;
use App\Repository\RespondentRepository;
use App\Repository\SurveyRepository;
use App\Service\LogService;
use App\UseCase\Survey\GetResponsesByUser;
use App\UseCase\Survey\SetSurveyResponses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SurveyController extends AbstractController
{
    #[Route('/mon-compte/sondage/{survey}', name: 'survey', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_VIEW', 'survey')]
    public function show(
        Request $request,
        SetSurveyResponses $setSurveyResponses,
        SurveyDtoTransformer $surveyDtoTransformer,
        Security $security,
        LogService $logService,
        Survey $survey
    ): Response {
        /** @var Member $member */
        $member = $security->getUser();
        list($histories, $respondent, $form, $message, $redirect) = $setSurveyResponses->execute($request, $survey, $member);
        $logService->writeFromEntity($survey);

        return $this->render('survey/survey_responses.html.twig', [
            'survey' => $surveyDtoTransformer->fromEntity($survey, $histories),
            'respondent' => $respondent,
            'form' => ($form) ? $form->createView() : $form,
            'message' => $message,
            'redirect' => $redirect,
        ]);
    }

    #[Route('/mon-compte/sondages', name: 'user_surveys', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function surveys(
        SurveyRepository $surveyRepository,
        RespondentRepository $respondentRepository,
        GetResponsesByUser $getResponsesByUser,
    ): Response {
        /** @var ?Member $member */
        $member = $this->getUser();

        $userSurveys = $respondentRepository->findActiveSurveysByUser($member);
        $respondents = [];

        foreach ($userSurveys as $userSurvey) {
            $survey = $userSurvey->getSurvey();
            $respondents[$survey->getId()] = [
                'createdAt' => $userSurvey->getCreatedAt(),
                'responses' => $getResponsesByUser->execute($survey, $member),
            ];
        }

        return $this->render('survey/list.html.twig', [
            'surveys' => $surveyRepository->findActiveByUser($member),
            'respondents' => $respondents,
        ]);
    }
}
