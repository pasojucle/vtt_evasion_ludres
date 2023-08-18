<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\SurveyDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\SurveyResponse;
use App\Entity\User;
use App\Form\SurveyResponsesType;
use App\Repository\RespondentRepository;
use App\Repository\SurveyRepository;
use App\UseCase\Survey\GetResponsesByUser;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SurveyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('sondage/{survey}', name: 'survey', methods: ['GET', 'POST'])]
    public function show(Request $request, RespondentRepository $respondentRepository, Survey $survey): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $form = $message = $respondent = null;
        /** @var User $user */
        $user = $this->getUser();
        $surveyResponses = [];
        $now = new DateTime();
        if ($now < $survey->getStartAt()) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Le sondage sera accessible à partir du ' . $survey->getStartAt()->format('d/m/Y'),
            ];
        }
        if ($survey->getEndAt() < $now) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Le sondage est clôturé depuis le ' . $survey->getEndAt()->format('d/m/Y'),
            ];
        }
        if (!$message) {
            $respondent = $respondentRepository->findOneBySurveyAndUser($survey, $user);
        }
        if ($respondent) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Votre participation au sondage a déja été prise en compte le ' . $respondent->getCreatedAt()->format('d/m/Y') . ' a ' . $respondent->getCreatedAt()->format('H\hi'),
            ];
        }
        if (!$message) {
            $uuid = uniqid('', true);
            if (!$survey->getSurveyIssues()->isEmpty()) {
                foreach ($survey->getSurveyIssues() as $issue) {
                    $response = new SurveyResponse();
                    $response->setSurveyIssue($issue)
                        ->setUuid($uuid)
                    ;
                    $surveyResponses[] = $response;
                }
            }
            $form = $this->createForm(SurveyResponsesType::class, [
                'surveyResponses' => $surveyResponses,
            ]);
            $form->handleRequest($request);

            if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                if (!empty($data['surveyResponses'])) {
                    foreach ($data['surveyResponses'] as $response) {
                        if (!$survey->isAnonymous()) {
                            $response->setUser($user);
                        }
                        $this->entityManager->persist($response);
                    }
                }
                $respondent = new Respondent();
                $respondent->setUser($user)
                    ->setSurvey($survey)
                    ->setCreatedAt($now)
                ;
                $this->entityManager->persist($respondent);
                $this->entityManager->flush();

                $message = [
                    'class' => 'success',
                    'content' => 'Votre participation au sondage a bien été prise en compte.',
                ];
            }
        }

        return $this->render('survey/survey_responses.html.twig', [
            'survey' => $survey,
            'respondent' => $respondent,
            'form' => ($form) ? $form->createView() : $form,
            'message' => $message,
        ]);
    }

    #[Route('/mes_sondages', name: 'user_surveys', methods: ['GET'])]
    public function surveys(
        SurveyRepository $surveyRepository,
        RespondentRepository $respondentRepository,
        GetResponsesByUser $getResponsesByUser,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var ?User $user */
        $user = $this->getUser();

        $userSurveys = $respondentRepository->findActiveSurveysByUser($user);
        $respondents = [];

        foreach ($userSurveys as $userSurvey) {
            $survey = $userSurvey->getSurvey();
            $respondents[$survey->getId()] = [
                'createdAt' => $userSurvey->getCreatedAt(),
                'responses' => $getResponsesByUser->execute($survey, $user),
            ];
        }


        return $this->render('survey/list.html.twig', [
            'surveys' => $surveyRepository->findActive($user),
            'respondents' => $respondents,
        ]);
    }
}
