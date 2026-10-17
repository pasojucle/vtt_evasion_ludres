<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDialogHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Payload\SurveyToggleDto;
use App\Entity\History;
use App\Entity\Survey;
use App\Form\Admin\SurveyType;
use App\State\Survey\Processor\SurveyDeleteProcessor;
use App\State\Survey\Processor\SurveyToggleProcessor;
use App\State\Survey\Processor\SurveyUpdateProcessor;
use App\State\Survey\Provider\SurveyAdminListProvider;
use App\State\Survey\Provider\SurveyCopyProvider;
use App\State\Survey\Provider\SurveyDeleteProvider;
use App\State\Survey\Provider\SurveyUpdateProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/sondage')]
class SurveyController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('s', name: 'admin_survey_list', methods: ['GET'])]
    #[IsGranted('SURVEY_LIST')]
    public function list(
        Request $request,
        SurveyAdminListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/', name: 'admin_survey_add', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_ADD')]
    public function add(
        Request $request,
        SurveyUpdateProvider $provider,
        SurveyUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Survey(),
            $provider,
            $processor,
            SurveyType::class
        );
    }

    #[Route('/edite/{survey}', name: 'admin_survey_edit', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function edit(
        Request $request,
        Survey $survey,
        SurveyUpdateProvider $provider,
        SurveyUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $survey,
            $provider,
            $processor,
            SurveyType::class
        );
    }

    #[Route('/copy/{survey}', name: 'admin_survey_copy', methods: ['GET', 'POST'])]
    #[IsGranted('SURVEY_EDIT', 'survey')]
    public function copy(
        Request $request,
        Survey $survey,
        SurveyCopyProvider $provider,
        SurveyUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $survey,
            $provider,
            $processor,
            SurveyType::class
        );
    }

    // #[Route('/{survey}', name: 'admin_survey', methods: ['GET', 'POST'], requirements: ['survey' => '\d+'])]
    // #[IsGranted('SURVEY_EDIT', subject: 'survey')]
    // public function show(
    //     GetSurveyResults $getSurveyResults,
    //     Request $request,
    //     SurveyIssueRepository $surveyIssueRepository,
    //     SurveyDtoTransformer $surveyDtoTransformer,
    //     Survey $survey
    // ): Response {
    //     $session = $request->getSession();
    //     $issues = $surveyIssueRepository->findBySurvey($survey);

    //     $filter = ['issue' => $issues[0]];
    //     $form = $this->createForm(SurveyFilterType::class, $filter, [
    //         'issues' => $issues,
    //     ]);
    //     $form->handleRequest($request);
    //     $responses = [];
    //     if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
    //         $filter = $form->getData();
    //     }
    //     $session->set('admin_survey_filter', $filter);
    //     $responses = $getSurveyResults->execute($filter);
    //     return $this->render('survey/admin/show.html.twig', [
    //         'survey' => $surveyDtoTransformer->fromEntityToResponses($survey),
    //         'responses' => $this->surveyResponseDtoTransformer->fromEntities($responses),
    //         'form' => $form->createView(),
    //     ]);
    // }

    // #[Route('/anonyme/{survey}/{tab}', name: 'admin_anonymous_survey', methods: ['GET'], defaults: ['tab' => 0])]
    // #[IsGranted('SURVEY_VIEW', 'survey')]
    // public function showAnonymous(GetAnonymousSurveyResults $getAnonymousSurveyResults, Survey $survey, int $tab): Response
    // {
    //     return $this->render('survey/admin/show_anonymous.html.twig', [
    //         'survey' => $survey,
    //         'results' => $getAnonymousSurveyResults->execute($survey),
    //         'tabs' => ['Réponses', 'Participants'],
    //         'tab' => $tab,
    //     ]);
    // }


    #[Route('/toggle/{survey}', name: 'admin_survey_toggle', methods: ['POST'])]
    #[IsGranted('SURVEY_EDIT', subject: 'survey')]
    public function adminProduitDisbaled(
        Request $request,
        SurveyToggleProcessor $processor,
        Survey $survey
    ): Response {
        return $this->handleComponentProcessAction(
            new SurveyToggleDto($survey, $request->request->get('csrfToken')),
            $processor
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
        Survey $survey,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $survey,
            $provider,
            $processor
        );
    }
}
