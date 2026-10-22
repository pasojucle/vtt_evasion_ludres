<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Entity\BikeRideType;
use App\Form\Admin\ActivityTypeType;
use App\State\ActivityType\Processor\ActivityTypeUpdateProcessor;
use App\State\ActivityType\Provider\ActivityTypeUpdateProvider;
use App\State\ActivityType\Processor\ActivityTypeDeleteProcessor;
use App\State\ActivityType\Processor\ActivityTypeRestoreProcessor;
use App\State\ActivityType\Provider\ActivityTypeDeleteProvider;
use App\State\ActivityType\Provider\ActivityTypeListProvider;
use App\State\ActivityType\Provider\ActivityRestoreProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/types-rando/', name: 'admin_bike_ride_type_', methods: ['GET'])]
class BikeRideTypeController extends AbstractCrudController
{
    #[Route('liste', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminList(
        Request $request,
        ActivityTypeListProvider $provider,
    ): Response {
        return $this->handleListPaginedAction(
            $provider,
            $request
        );
    }

    #[Route('', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAdd(
        Request $request,
        ActivityTypeUpdateProvider $provider,
        ActivityTypeUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new BikeRideType(),
            $provider,
            $processor,
            ActivityTypeType::class,
        );
    }

    #[Route('/{bikeRideType}', name: 'edit', methods: ['GET', 'POST'], requirements: ['bikeRideType' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(
        Request $request,
        BikeRideType $bikeRideType,
        ActivityTypeUpdateProvider $provider,
        ActivityTypeUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $bikeRideType,
            $provider,
            $processor,
            ActivityTypeType::class,
        );
    }

    #[Route(path: '/delete/{bikeRideType}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        BikeRideType $bikeRideType,
        ActivityTypeDeleteProcessor $processor,
        ActivityTypeDeleteProvider $provider,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $bikeRideType,
            $provider,
            $processor
        );
    }

    #[Route('/restaure/sortie/{bikeRideType}', name: 'restore', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRestaure(
        Request $request,
        ActivityRestoreProvider $provider,
        ActivityTypeRestoreProcessor $processor,
        BikeRideType $bikeRideType,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $bikeRideType,
            $provider,
            $processor,
        );
    }
}
