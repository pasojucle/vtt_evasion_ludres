<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\VoteResponse;
use App\Entity\VoteUser;
use App\Form\VoteResponsesType;
use App\Repository\VoteRepository;
use App\Repository\VoteUserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoteController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        
    }
    #[Route('vote/{vote}', name: 'vote', methods: ['GET', 'POST'])]
    public function show(Request $request, VoteUserRepository $voteUserRepository, Vote $vote): Response
    {
        $form = $message = $voteUser = null;
        $user = $this->getUser();
        $voteResponses = [];
        $now = new DateTime();
        if ($now < $vote->getStartAt()) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Le vote sera accessible à partir du '.$vote->getStartAt()->format('d/m/Y'),
            ];
        }
        if ($vote->getEndAt() < $now) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Le vote est clôturé depuis le '.$vote->getEndAt()->format('d/m/Y'),
            ];
        }
        if (!$message) {
            $voteUser = $voteUserRepository->findOneByVoteAndUser($vote, $user);
        }
        if ($voteUser) {
            $message = [
                'class' => 'alert-warning',
                'content' => 'Votre participation au vote a déja été prise en compte le '.$voteUser->getCreatedAt()->format('d/m/Y').' a '.$voteUser->getCreatedAt()->format('H\hi'),
            ];
        }
        if (!$message) {
            $uuid = uniqid('', true);
            if (!$vote->getVoteIssues()->isEmpty()) {
                foreach($vote->getVoteIssues() as $issue) {
                    $response = new VoteResponse();
                    $response->setVoteIssue($issue)
                        ->setUuid($uuid);
                    $voteResponses[] = $response;
                }
            }
            $form = $this->createForm(VoteResponsesType::class, ['voteResponses' => $voteResponses]);
            $form->handleRequest($request);

            if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                if (!empty($data['voteResponses'])) {
                    foreach ($data['voteResponses'] as $response) {
                        $this->entityManager->persist($response);
                    }
                }
                $voteUser = new VoteUser();
                $voteUser->setUser($user)
                    ->setVote($vote)
                    ->setCreatedAt($now);
                $this->entityManager->persist($voteUser);
                $this->entityManager->flush();

                $message = [
                    'class' => 'success',
                    'content' => 'Votre participation au vote à bien été prise en compte.',
                ];
            }
        }

        return $this->render('vote/vote_responses.html.twig', [
            'vote' => $vote,
            'voteUser' => $voteUser,
            'form' => ($form) ? $form->createView(): $form,
            'message' => $message,
        ]);
    }


    /**
     * @Route("/mes_votes", name="user_votes")
     */
    public function votes(
        VoteRepository $voteRepository
    ): Response
    {

        return $this->render('vote/list.html.twig', [
            'votes' => $voteRepository->findActive($this->getUser()),
            'user_votes' => $voteRepository->findActiveVotesByUser($this->getUser()),
        ]);
    }
}
