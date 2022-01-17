<?php

namespace App\Controller\Admin;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Form\Admin\VoteType;
use App\UseCase\Vote\VoteExport;
use App\Service\ParameterService;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\VoteResponseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('admin/vote')]
class VoteController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }
    #[Route('s', name: 'admin_votes', methods: ['GET'])]
    public function list(VoteRepository $voteRepository): Response
    {
        return $this->render('vote/admin/list.html.twig', [
            'votes' => $voteRepository->findAll(),
        ]);
    }

    #[Route('/edite/{vote}', name: 'admin_vote_edit', methods: ['GET', 'POST'], defaults:['vote' => null])]
    public function edit(Request $request, ParameterService $parameterService, ?Vote $vote): Response
    {
        if (!$vote) {
            $vote = new Vote();
            $voteIssues = $parameterService->getParameterByName('VOTE_ISSUES');
            $vote->setContent($parameterService->getParameterByName('VOTE_CONTENT'));
            if (!empty($voteIssues)) {
                foreach ($voteIssues as $voteIssue) {
                    $issue = new VoteIssue();
                    $issue->setContent($voteIssue);
                    $vote->addVoteIssue($issue);
                }
            }
        }
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

    #[Route('export/{vote}', name: 'admin_vote_export', methods: ['GET'])]
    public function export(VoteExport $voteExport, VoteResponseRepository $voteResponseRepository, Vote $vote): Response
    {  
        $content = $voteExport->execute($vote, $voteResponseRepository->findResponsesByUuid($vote));

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_vote_a_g.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/disable/{vote}', name: 'admin_vote_disable', methods: ['GET','POST'])]
    public function delete(Request $request, Vote $vote): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_vote_disable', 
                [
                    'vote'=> $vote->getId(),
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
}
