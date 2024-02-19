<?php

declare(strict_types=1);

namespace App\UseCase\Cluster;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Level;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class GetUsersOffSite
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function execute(Request $request, Cluster $cluster): array
    {
        /** @var BikeRide $bikeRide */
        $bikeRide = $cluster->getBikeRide();
        $usersOffSite = [];
        $response = null;
        if (!$cluster->isComplete() && BikeRideType::REGISTRATION_SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
            foreach ($cluster->getSessions() as $session) {
                $level = $session->getUser()->getLevel();
                $levelType = (null !== $level) ? $level->getType() : Level::TYPE_SCHOOL_MEMBER;
                if (!$session->isPresent() && Level::TYPE_SCHOOL_MEMBER === $levelType) {
                    $userIdentifiers = $this->userDtoTransformer->identifiersFromEntity($session->getUser());
                    $usersOffSite['users'][] = $userIdentifiers;
                    $usersOffSite['fullnames'][] = $userIdentifiers->member->fullName;
                }
            }
            $this->askConfirmResponse($request, $cluster, $usersOffSite, $response);
        }
        dump($usersOffSite, $response);
        return [$usersOffSite, $response];
    }

    private function askConfirmResponse(Request $request, Cluster $cluster, array $usersOffSite, ?Response &$response): void
    {
        if (!empty($usersOffSite)) {
            $form = $this->formFactory->create(FormType::class, null, [
                'action' => $this->urlGenerator->generate('admin_cluster_complete', ['cluster' => $cluster->getId()]),
            ]);

            if ($request->isMethod('GET')) {
                $response = new JsonResponse([
                    'codeError' => 0,
                    'modal' => $this->twig->render('cluster/export.modal.html.twig', [
                        'form' => $form->createView(),
                        'users_off_site' => $usersOffSite['fullnames'],
                    ]),
                ]);
            }
        }
    }
}
