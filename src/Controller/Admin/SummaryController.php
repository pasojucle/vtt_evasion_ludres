<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDialogHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\DetailHandler;
use App\Entity\BikeRide;
use App\Entity\Summary;
use App\Form\Admin\SummaryType;
use App\State\Summary\Processor\SummaryDeleteProcessor;
use App\State\Summary\Processor\SummaryUpdateProcessor;
use App\State\Summary\Provider\SummaryCreateProvider;
use App\State\Summary\Provider\SummaryDeleteProvider;
use App\State\Summary\Provider\SummaryReadProvider;
use App\State\Summary\Provider\SummaryUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/actualite/', name: 'admin_summary_')]
class SummaryController extends AbstractCrudController
{
    #[Route('list/{bikeRide}', name: 'list', methods: ['GET'])]
    #[IsGranted('SUMMARY_EDIT', 'bikeRide')]
    public function list(
        Request $request,
        SummaryReadProvider $provider,
        BikeRide $bikeRide,
        DetailHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,   
            $bikeRide
        );
    }

    #[Route('add/{bikeRide}', name: 'add', requirements:['bikeRide' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('SUMMARY_ADD')]
    public function add(
        Request $request,
        SummaryCreateProvider $provider,
        SummaryUpdateProcessor $processor,
        BikeRide $bikeRide,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $bikeRide,
            $provider,
            $processor,
            SummaryType::class
        );
    }

    #[Route('edit/{summary}', name: 'edit', requirements:['summary' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('SUMMARY_EDIT', 'summary')]
    public function edit(
        Request $request,
        SummaryUpdateProvider $provider,
        SummaryUpdateProcessor $processor,
        Summary $summary,        
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $summary,
            $provider,
            $processor,
            SummaryType::class
        );
    }

    #[Route('delete/{summary}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('SUMMARY_EDIT', 'summary')]
    public function delete(
        Request $request,
        SummaryDeleteProvider $provider,
        SummaryDeleteProcessor $processor,
        Summary $summary,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $summary,
            $provider,
            $processor
        );
    }
}
