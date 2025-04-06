<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\LinkDtoTransformer;
use App\Entity\Link;
use App\Entity\User;
use App\Repository\ContentRepository;
use App\Repository\LinkRepository;
use App\Service\LogService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
        private readonly LinkDtoTransformer $linkDtoTransformer,
    ) {
    }

    #[Route('/liens', name: 'links', methods: ['GET'])]
    public function list(LinkRepository $linkRepository, UserService $userService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $novelties = ($user && $userService->licenceIsActive($user))
            ? $linkRepository->findNoveltiesByUserIds($user)
            : [];

        return $this->render('link/list.html.twig', [
            'links' => $this->linkDtoTransformer->fromEntities($linkRepository->findByPosition(Link::POSITION_LINK_PAGE), $novelties),
            'backgrounds' => $this->contentRepository->findOneByRoute('links')?->getBackgrounds(),
        ]);
    }

    #[Route('/lien/{link}', name: 'link_show', methods: ['GET'])]
    public function show(LogService $logService, Link $link): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $logService->write('Link', $link->getId(), $user);

        if (!$link->getContent()) {
            return $this->redirect($link->getUrl());
        }
                
        return $this->render('link/show.html.twig', [
            'link' => $this->linkDtoTransformer->fromEntity($link),
            'backgrounds' => $this->contentRepository->findOneByRoute('links')?->getBackgrounds(),
        ]);
    }
}
