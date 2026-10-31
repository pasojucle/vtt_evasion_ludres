<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ActionJsonHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Payload\SortablePayload;
use App\Entity\Agreement;
use App\Form\Admin\AgreementType;
use App\State\Agreement\Processor\AgreementOrderProcessor;
use App\State\Agreement\Processor\AgreementToggleProcessor;
use App\State\Agreement\Processor\AgreementUpdateProcessor;
use App\State\Agreement\Provider\AgreementListProvider;
use App\State\Agreement\Provider\AgreementToggleProvider;
use App\State\Agreement\Provider\AgreementUpdateProvider;
use App\UseCase\Agreement\AddAgreement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_agreement_')]
class AgreementController extends AbstractController
{
    #[Route('/autorisations', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        Request $request,
        AgreementListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/admin/agreement', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function add(
        Request $request,
        AgreementUpdateProvider $provider,
        AgreementUpdateProcessor $processor,
        AddAgreement $addAgreement,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Agreement(),
            $provider,
            $processor,
            AgreementType::class
        );
    }

    #[Route('/admin/agreement/{agreement}', name: 'edit', methods: ['GET', 'POST'], requirements:['agreement' => '\w+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        AgreementUpdateProvider $provider,
        AgreementUpdateProcessor $processor,
        Agreement $agreement,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $agreement,
            $provider,
            $processor,
            AgreementType::class
        );
    }

    #[Route('/ordonner/{agreement}', name: 'order', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function order(
        Request $request,
        AgreementOrderProcessor $processor,
        Agreement $agreement,
        ActionJsonHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            SortablePayload::fromRequest($agreement, $request),
            $processor
        );
    }

    #[Route('/admin/toggle/agreement/{agreement}', name: 'toggle', requirements:['agreement' => '\w+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAgreementToggle(
        Request $request,
        AgreementToggleProvider $provider,
        AgreementToggleProcessor $processor,
        Agreement $agreement,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $agreement,
            $provider,
            $processor
        );
    }
}
