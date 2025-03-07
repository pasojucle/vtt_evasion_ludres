<?php

declare(strict_types=1);

namespace App\UseCase\Content;

use App\Entity\Content;
use App\Repository\ContentRepository;
use App\Service\OrderByService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SetContent
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentRepository $contentRepository,
        private readonly UploadService $uploadService,
    ) {
    }

    public function execute(FormInterface $form, Request $request): Content
    {
        $content = $form->getData();
        $this->urlFromYoutubeEmbed($content);
        $this->order($content);
        $this->uploadFile($request, $content);

        $this->entityManager->persist($content);
        $this->entityManager->flush();

        return $content;
    }

    private function urlFromYoutubeEmbed(Content $content): void
    {
        $youtubeEmbed = $content->getYoutubeEmbed();
        if ($youtubeEmbed && 1 === preg_match('#^<iframe(?:.+)src="([a-zA-Z0-9\/:.]+)"(?:.+)<\/iframe>$#', $youtubeEmbed, $matches)) {
            $content->setUrl($matches[1]);
        }
    }

    private function order(Content $content): void
    {
        if (null === $content->getOrderBy()) {
            $content->setOrderBy(0);
            $order = $this->contentRepository->findNexOrderByRoute($content->getRoute(), $content->getKind());
            $content->setOrderBy($order);
        }
    }

    private function uploadFile(Request $request, Content $content): void
    {
        if ($request->files->get('content')) {
            $file = $request->files->get('content')['file'];
            if ($file) {
                $content->setFileName($this->uploadService->uploadFile($file));
            }
        }
    }
}
