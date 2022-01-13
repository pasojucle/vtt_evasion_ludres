<?php

namespace App\Controller\Admin;

use App\Entity\Vote;
use App\Form\Admin\VoteType;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\RequestMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('admin//vote')]
class VoteController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }
    #[Route('s', name: 'admin_votes', methods: ['GET'])]
    public function list(VoteRepository $voteRepository): Response
    {
        return $this->render('vote/list.html.twig', [
            'votes' => $voteRepository->findAll(),
        ]);
    }

    #[Route('/{vote}', name: 'admin_vote_edit', methods: ['GET', 'POST'], defaults:['vote' => null])]
    public function edit(Request $request, ?Vote $vote): Response
    {
        $form = $this->createForm(VoteType::class, $vote);
        $form->handleRequest($request);

        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $vote = $form->getData();
            dump($vote);
            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            // return $this->redirectToRoute('admin_votes');
        }

        return $this->renderForm('vote/edit.html.twig', [
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
