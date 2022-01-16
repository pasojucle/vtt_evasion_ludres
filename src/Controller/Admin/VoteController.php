<?php

namespace App\Controller\Admin;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Form\Admin\VoteType;
use App\Repository\VoteRepository;
use App\Service\ParameterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route('/{vote}', name: 'admin_vote_edit', methods: ['GET', 'POST'], defaults:['vote' => null])]
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

    #[Route('/delete/{vote}', name: 'admin_vote_delete', methods: ['POST'])]
    public function delete(Request $request, Vote $vote): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_vote_list');
    }
}
