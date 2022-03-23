<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Vote;
use App\Form\Admin\VoteType;
use App\UseCase\Vote\GetVote;
use App\UseCase\Vote\ExportVote;
use App\Repository\VoteRepository;
use App\Form\Admin\SurveyFilterType;
use App\UseCase\Vote\GetSurveyResults;
use App\Repository\VoteIssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UseCase\Vote\GetAnonymousSurveyResults;
use App\ViewModel\SurveyResponsesPresenter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/vote')]
class VoteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('s', name: 'admin_votes', methods: ['GET'])]
    public function list(VoteRepository $voteRepository): Response
    {
        return $this->render('vote/admin/list.html.twig', [
            'votes' => $voteRepository->findAll(),
        ]);
    }

    #[Route('/edite/{vote}', name: 'admin_vote_edit', methods: ['GET', 'POST'], defaults:[
        'vote' => null,
    ])]
    public function edit(Request $request, GetVote $getVote, ?Vote $vote): Response
    {
        $getVote->execute($vote);
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $vote = $form->getData();
            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_votes');
        }

        return $this->renderForm('vote/admin/edit.html.twig', [
            'vote' => $vote,
            'form' => $form,
        ]);
    }

    #[Route('/{survey}', name: 'admin_surveys', methods: ['GET', 'POST'])]
    public function show(
        GetSurveyResults $getSurveyResults,
        Request $request,
        SurveyResponsesPresenter $surveyResponsesPresenter,
        VoteIssueRepository $voteIssueRepository,
        Vote $survey
    ): Response
    {
        $issues = $voteIssueRepository->findByVote($survey);
        
        $filter = ['issue' => $issues[0]];
        $form = $this->createForm(SurveyFilterType::class, $filter,  [
            'issues' => $issues,
        ]);
        $form->handleRequest($request);
        $responses = [];
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filter = $form->getData();
        }
        $responses = $getSurveyResults->execute($filter);
        $surveyResponsesPresenter->present($responses);
        return $this->render('vote/admin/show.html.twig', [
            'vote' => $survey,
            'responses' => $surveyResponsesPresenter->viewModel()->surveyResponses,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/anonyme/{survey}/{tab}', name: 'admin_anonymous_surveys', methods: ['GET'], defaults: [
        'tab' => 0,
    ])]
    public function showAnonymous(GetAnonymousSurveyResults $getAnonymousSurveyResults, Vote $survey, int $tab): Response
    {

        return $this->render('vote/admin/show_anonymous.html.twig', [
            'vote' => $survey,
            'results' => $getAnonymousSurveyResults->execute($survey),
            'tabs' => ['Réponses', 'Participants'],
            'tab' => $tab,
        ]);
    }

    #[Route('export/{survey}', name: 'admin_survey_export', methods: ['GET'])]
    public function export(ExportVote $export, Vote $survey): Response
    {
        
        $content = $export->execute($survey);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_vote_a_g.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('disable/{vote}', name: 'admin_vote_disable', methods: ['GET', 'POST'])]
    public function delete(Request $request, Vote $vote): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_vote_disable',
                [
                    'vote' => $vote->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $vote->setDisabled(true);
            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_votes');
        }

        return $this->render('vote/admin/disable.modal.html.twig', [
            'vote' => $vote,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/survey/issue/list/select2', name: 'survey_issue_list_select2', methods: ['GET'])]
    public function userListSelect2(
        VoteIssueRepository $voteIssueRepository,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');
        $surveyId = (int) $request->query->get('surveyId');

        $issues = $voteIssueRepository->findBySurveyAndContent($surveyId, $query);

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
