<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Survey;
use App\Entity\SurveyResponse;
use App\Entity\SurveyUser;
use App\Form\SurveyResponsesType;
use App\Repository\SurveyRepository;
use App\Repository\SurveyUserRepository;
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
    public function show(Request $request, SurveyUserRepository $surveyUserRepository, Survey $survey): Response
    {
        $form = $message = $surveyUser = null;
        $user = $this->getUser();
        $surveyResponses = [];
        $now = new DateTime();
        if ($now < $survey->getStartAt()) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Le survey sera accessible à partir du '.$survey->getStartAt()->format('d/m/Y'),
            ];
        }
        if ($survey->getEndAt() < $now) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Le survey est clôturé depuis le '.$survey->getEndAt()->format('d/m/Y'),
            ];
        }
        if (!$message) {
            $surveyUser = $surveyUserRepository->findOneBySurveyAndUser($survey, $user);
        }
        if ($surveyUser) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Votre participation au survey a déja été prise en compte le '.$surveyUser->getCreatedAt()->format('d/m/Y').' a '.$surveyUser->getCreatedAt()->format('H\hi'),
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
                $surveyUser = new SurveyUser();
                $surveyUser->setUser($user)
                    ->setSurvey($survey)
                    ->setCreatedAt($now)
                ;
                $this->entityManager->persist($surveyUser);
                $this->entityManager->flush();

                $message = [
                    'class' => 'success',
                    'content' => 'Votre participation au survey à bien été prise en compte.',
                ];
            }
        }

        return $this->render('survey/survey_responses.html.twig', [
            'survey' => $survey,
            'surveyUser' => $surveyUser,
            'form' => ($form) ? $form->createView() : $form,
            'message' => $message,
        ]);
    }

    #[Route('/mes_sondages', name: 'user_surveys', methods: ['GET'])]
    public function surveys(
        SurveyRepository $surveyRepository,
        SurveyUserRepository $surveyUserRepository
    ): Response {
        return $this->render('survey/list.html.twig', [
            'surveys' => $surveyRepository->findActive($this->getUser()),
            'user_surveys' => $surveyUserRepository->findActiveSurveysByUser($this->getUser()),
        ]);
    }
}
