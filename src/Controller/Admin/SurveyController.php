<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Dto\DtoTransformer\SurveyDtoTransformer;
use App\Dto\DtoTransformer\SurveyResponseDtoTransformer;
use App\Dto\Filter\SurveyFilter;
use App\Entity\History;
use App\Entity\Survey;
use App\Form\Admin\SurveyFilterType;
use App\Form\Admin\SurveyType;
use App\Repository\SurveyIssueRepository;
use App\State\Survey\Processor\SurveyDeleteProcessor;
use App\State\Survey\Processor\SurveyDisableProcessor;
use App\State\Survey\Provider\SurveyAdminListProvider;
use App\State\Survey\Provider\SurveyAdminProvider;
use App\State\Survey\Provider\SurveyDeleteProvider;
use App\State\Survey\Provider\SurveyDisableProvider;
use App\UseCase\Survey\GetAnonymousSurveyResults;
use App\UseCase\Survey\GetSurvey;
use App\UseCase\Survey\GetSurveyResults;
use App\UseCase\Survey\SetSurvey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/sondage')]
class SurveyController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SurveyResponseDtoTransformer $surveyResponseDtoTransformer,
        private readonly SurveyDtoTransformer $surveyDtoTransformer,
    ) {
    }

    #[Route('s', name: 'admin_survey_list', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function list(
        Request $request,
        SurveyAdminListProvider $provider,
    ): Response {

        return $this->handleListAction(
            'admin_survey_list',
            SurveyFilter::class,
            $provider,
            'survey/admin/list.html.twig',
            $request
        );
      }

    #[Route('/', name: 'admin_survey_add', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_ADD')]
    public function add(Request $request, GetSurvey $getSurvey, SetSurvey $setSurvey): Response
    {
        $survey = null;
        $getSurvey->execute($survey);

        $form = $this->createForm(SurveyType::class, $survey, [
            'display_disabled' => false,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $setSurvey->execute($form, true);

            return $this->redirectToRoute('admin_survey_list');
        }

        return $this->render('survey/admin/edit.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edite/{survey}', name: 'admin_survey_edit', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function edit(Request $request, SetSurvey $setSurvey, Survey $survey): Response
    {
        $form = $this->createForm(SurveyType::class, $survey, [
            'display_disabled' => !$survey->getRespondents()->isEmpty(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $setSurvey->execute($form);
            
            return $this->redirectToRoute('admin_survey_list');
        }

        return $this->render('survey/admin/edit.html.twig', [
            'survey' => $this->surveyDtoTransformer->fromEntity($survey),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/copy/{survey}', name: 'admin_survey_copy', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function copy(Request $request, GetSurvey $getSurvey, SetSurvey $setSurvey, Survey $survey): Response
    {
        $copy = $getSurvey->copy($survey);
        $form = $this->createForm(SurveyType::class, $copy, [
            'display_disabled' => false,
        ]);
        
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $setSurvey->execute($form);
            
            return $this->redirectToRoute('admin_survey_list');
        }

        return $this->render('survey/admin/edit.html.twig', [
            'survey' => $this->surveyDtoTransformer->fromEntity($copy),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{survey}', name: 'admin_survey', methods: ['GET', 'POST'], requirements: ['survey' => '\d+'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function show(
        GetSurveyResults $getSurveyResults,
        Request $request,
        SurveyIssueRepository $surveyIssueRepository,
        SurveyDtoTransformer $surveyDtoTransformer,
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
            'survey' => $surveyDtoTransformer->fromEntityToResponses($survey),
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
            $emails[] = $response->user['mainEmail'];
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

    #[Route('/export/{survey}', name: 'admin_survey_export', methods: ['GET'])]
    #[IsGranted('SURVEY_VIEW', 'survey')]
    public function export(
        SurveyAdminProvider $provider,
        Survey $survey
    ): Response
    {
        $response = new StreamedResponse(function() use ($provider, $survey) {
            $provider->streamExportContent($survey);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export_inscriptions.csv"');

        return $response;
    }

    #[Route('disable/{survey}', name: 'admin_survey_disable', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function disable(
        Request $request, 
        SurveyDisableProcessor $processor,
        SurveyDisableProvider $provider,
        Survey $survey): Response
    {
        return $this->handleDialogAction(
            $request,
            $survey,
            $provider,
            $processor,
        );
    }

    #[Route('/history/notify/{survey}', name: 'admin_survey_history_notify', methods: ['GET'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function adminNotifySurveyHistory(
        Request $request,
        Survey $survey,
    ): jsonResponse|Response {
        $histories = $this->entityManager->getRepository(History::class)->findNotifiableBySurvey($survey->getId());
        /** @var History $history */
        foreach ($histories as $history) {
            $history->setNotify(true);
        }
        $this->entityManager->flush();
        $this->addFlash('success', 'La notification est bien activée');

        return $this->redirectToRoute('admin_survey_list');
    }

    #[Route('detele/{survey}', name: 'admin_survey_delete', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function delete(
        Request $request,
        SurveyDeleteProcessor $processor,
        SurveyDeleteProvider $provider,
        Survey $survey
    ): Response {
        return $this->handleDialogAction(
            $request,
            $survey,
            $provider,
            $processor
        );
    }
}
