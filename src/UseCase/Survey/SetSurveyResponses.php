<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\SurveyResponse;
use App\Entity\User;
use App\Form\SurveyResponsesType;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class SetSurveyResponses
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly SurveyResponseRepository $surveyResponseRepository,
        private readonly RespondentRepository $respondentRepository,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function execute(Request $request, Survey $survey)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $respondent = null;
        $now = new DateTime();
        $message = $this->validate($survey, $now);
        if ($message) {
            return [null, null, $message];
        }
        $respondent = $this->respondentRepository->findOneBySurveyAndUser($survey, $user);
        if ($respondent) {
            return $this->addResponses($request, $survey, $respondent, $user, $message);
        }

        return $this->editResponses($request, $survey, $user, $now, $message);
    }

    private function validate(Survey $survey, DateTime $now): ?array
    {
        $result = null;

        if ($now < $survey->getStartAt()) {
            $result = [
                'class' => 'alert-warning',
                'content' => 'Le sondage sera accessible à partir du ' . $survey->getStartAt()->format('d/m/Y'),
            ];
        }
        if ($survey->getEndAt() < $now) {
            $result = [
                'class' => 'alert-warning',
                'content' => 'Le sondage est clôturé depuis le ' . $survey->getEndAt()->format('d/m/Y'),
            ];
        }

        return $result;
    }

    private function newSurveyResponses(Survey $survey)
    {
        $uuid = uniqid('', true);
        $surveyResponses = [];
        foreach ($survey->getSurveyIssues() as $issue) {
            $response = new SurveyResponse();
            $response->setSurveyIssue($issue)
                ->setUuid($uuid)
            ;
            $surveyResponses[] = $response;
        }
        
        return $surveyResponses;
    }

    private function addResponses(Request $request, Survey $survey, Respondent $respondent, User $user, ?array $message): array
    {
        $form = $this->formFactory->create(SurveyResponsesType::class, [
            'surveyResponses' => $this->surveyResponseRepository->findResponsesByUserAndSurvey($user, $survey),
        ]);
        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['surveyResponses'])) {
                foreach ($data['surveyResponses'] as $response) {
                    if (!$survey->isAnonymous()) {
                        $response->setUser($user);
                    }
                }
            }
            $this->entityManager->flush();

            $message = [
                'class' => 'success',
                'content' => 'Votre participation au sondage a bien été modifiée.',
            ];
        }
        return [$respondent, $form, $message];
    }

    private function editResponses(Request $request, Survey $survey, User $user, DateTime $now, ?array $message): array
    {
        $respondent = null;

        $form = $this->formFactory->create(SurveyResponsesType::class, [
            'surveyResponses' => $this->newSurveyResponses($survey),
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

        return [$respondent, $form, $message];
    }
}
