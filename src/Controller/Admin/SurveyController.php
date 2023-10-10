<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Survey;
use App\Form\Admin\SurveyType;
use App\Service\PaginatorService;
use App\UseCase\Survey\GetSurvey;
use App\UseCase\Survey\SetSurvey;
use App\Form\Admin\SurveyFilterType;
use App\Repository\SurveyRepository;
use App\UseCase\Survey\ExportSurvey;
use App\UseCase\Survey\GetSurveyResults;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SurveyIssueRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\UseCase\Survey\GetAnonymousSurveyResults;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\SurveyResponseDtoTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/sondage')]
class SurveyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseDtoTransformer $surveyResponseDtoTransformer,
    ) {
    }

    #[Route('s', name: 'admin_surveys', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function list(
        Request $request,
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        SurveyRepository $surveyRepository
    ): Response {
        $query = $surveyRepository->findAllDESCQuery();
        $surveys = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('survey/admin/list.html.twig', [
            'surveys' => $surveys,
            'paginator' => $paginatorDtoTransformer->fromEntities($surveys),
        ]);
    }

    #[Route('/edite/{survey}', name: 'admin_survey_edit', methods: ['GET', 'POST'], defaults:['survey' => null])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function edit(Request $request, GetSurvey $getSurvey, SetSurvey $setSurvey, ?Survey $survey): Response
    {
        $getSurvey->execute($survey);

        $form = $this->createForm(SurveyType::class, $survey, [
            'display_disabled' => !$survey->getRespondents()->isEmpty(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $setSurvey->execute($form);

            return $this->redirectToRoute('admin_surveys');
        }

        return $this->render('survey/admin/edit.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{survey}', name: 'admin_survey', methods: ['GET', 'POST'], requirements: ['survey' => '\d+'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function show(
        GetSurveyResults $getSurveyResults,
        Request $request,
        SurveyIssueRepository $surveyIssueRepository,
        Survey $survey
    ): Response {
        $session = $request->getSession();
        $issues = $surveyIssueRepository->findBySurvey($survey);

        $filter = ['issue' => $issues[0]];
        $form = $this->createForm(SurveyFilterType::class, $filter, [
            'issues' => $issues,
        ]);
        $form->handleRequest($request);
        $responses = [];
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filter = $form->getData();
        }
        $session->set('admin_survey_filter', $filter);
        $responses = $getSurveyResults->execute($filter);
        return $this->render('survey/admin/show.html.twig', [
            'survey' => $survey,
            'responses' => $this->surveyResponseDtoTransformer->fromEntities($responses),
            'form' => $form->createView(),
        ]);
    }


    #[Route('/emails', name: 'admin_survey_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function adminEmailSurvey(
        GetSurveyResults $getSurveyResults,
        Request $request
    ): JsonResponse {
        $session = $request->getSession();
        $filter = $session->get('admin_survey_filter');
        $responses = $getSurveyResults->execute($filter);
        $surveyResponsesDto = $this->surveyResponseDtoTransformer->fromEntities($responses);

        $emails = [];
        foreach ($surveyResponsesDto as $response) {
            $emails[] = $response->user->mainEmail;
        }

        return new JsonResponse(implode(',', $emails));
    }

    #[Route('/anonyme/{survey}/{tab}', name: 'admin_anonymous_survey', methods: ['GET'], defaults: ['tab' => 0])]
    #[IsGranted('SURVEY_VIEW', 'survey')]
    public function showAnonymous(GetAnonymousSurveyResults $getAnonymousSurveyResults, Survey $survey, int $tab): Response
    {
        return $this->render('survey/admin/show_anonymous.html.twig', [
            'survey' => $survey,
            'results' => $getAnonymousSurveyResults->execute($survey),
            'tabs' => ['Réponses', 'Participants'],
            'tab' => $tab,
        ]);
    }

    #[Route('export/{survey}', name: 'admin_survey_export', methods: ['GET'])]
    #[IsGranted('SURVEY_VIEW', 'survey')]
    public function export(ExportSurvey $export, Survey $survey): Response
    {
        $content = $export->execute($survey);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_survey_a_g.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('disable/{survey}', name: 'admin_survey_disable', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function delete(Request $request, Survey $survey): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_survey_disable',
                [
                    'survey' => $survey->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $survey->setDisabled(true);
            $this->entityManager->persist($survey);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_surveys');
        }

        return $this->render('survey/admin/disable.modal.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/survey/issue/list/select2', name: 'survey_issue_list_select2', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function userListSelect2(
        SurveyIssueRepository $surveyIssueRepository,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');
        $surveyId = (int) $request->query->get('surveyId');

        $issues = $surveyIssueRepository->findBySurveyAndContent($surveyId, $query);

        $response = [];

        foreach ($issues as $issue) {
            $response[] = [
                'id' => $issue->getId(),
                'text' => $issue->getContent(),
            ];
        }

        return new JsonResponse($response);
    }
}
