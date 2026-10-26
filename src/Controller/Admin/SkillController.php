<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionExportHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\AutocompleteHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\SkillAutocompleteFilter;
use App\Dto\Filter\SkillFilter;
use App\Entity\Skill;
use App\Form\Admin\SkillType;
use App\State\Skill\Processor\SkillDeleteProcessor;
use App\State\Skill\Processor\SkillRestoreProcessor;
use App\State\Skill\Processor\SkillUpdateProcessor;
use App\State\Skill\Provider\SkillAutocompleteProvider;
use App\State\Skill\Provider\SkillDeleteProvider;
use App\State\Skill\Provider\SkillExportProvider;
use App\State\Skill\Provider\SkillListProvider;
use App\State\Skill\Provider\SkillRestoreProvider;
use App\State\Skill\Provider\SkillUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/competences', name: 'admin_skill_')]
class SkillController extends AbstractController
{
    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    #[IsGranted('SKILL_LIST')]
    public function list(
        Request $request,
        SkillListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    #[IsGranted('SKILL_LIST')]
    public function adminOrderHeadersExport(
        Request $request,
        SkillExportProvider $provider,
        ActionExportHandler $handler,
    ): Response {
        return $handler->handle(
            request: $request,
            provider: $provider,
            filterClass: SkillFilter::class,
        );
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET', 'POST'])]
    #[IsGranted('IS_MEMBER')]
    public function adminSkillAutocomplete(
        Request $request,
        SkillAutocompleteProvider $provider,
        AutocompleteHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            SkillAutocompleteFilter::class,
        );
    }

    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_ADD')]
    public function add(
        Request $request,
        SkillUpdateProvider $provider,
        SkillUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Skill(),
            $provider,
            $processor,
            SkillType::class,
        );
    }

    #[Route(path: '/edit/{skill}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_EDIT', 'skill')]
    public function edit(
        Request $request,
        Skill $skill,
        SkillUpdateProvider $provider,
        SkillUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $skill,
            $provider,
            $processor,
            SkillType::class,
        );
    }

    #[Route(path: '/delete/{skill}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_EDIT', 'skill')]
    public function delete(
        Request $request,
        SkillDeleteProvider $provider,
        SkillDeleteProcessor $processor,
        Skill $skill,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $skill,
            $provider,
            $processor
        );
    }

    #[Route('/restaure/{skill}', name: 'restore', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelRestore(
        Request $request,
        SkillRestoreProvider $provider,
        SkillRestoreProcessor $processor,
        Skill $skill,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $skill,
            $provider,
            $processor
        );
    }
}
