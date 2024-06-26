<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\StringService;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class ExportBikeRide
{
    private array $fileContent = [];
    private BikeRide $bikeRide;

    public function __construct(
        private SessionRepository $sessionRepository,
        private StringService $stringService,
        private UserDtoTransformer $userDtoTransformer
    ) {
    }

    private const SEPARATOR = ',';

    public function execute(BikeRide $bikeRide): Response
    {
        $this->bikeRide = $bikeRide;
        $this->addHeader();
        $this->addSessions();

        return $this->getResponse();
    }

    private function addHeader(): void
    {
        $this->fileContent[] = $this->bikeRide->getTitle() . ' - ' . $this->bikeRide->getStartAt()->format('d/m/Y');
        $this->fileContent[] = '';
        $row = ['n° de Licence', 'Nom', 'Prénom', 'Date de naissance', 'Niveau', 'Présent'];
        $this->fileContent[] = implode(self::SEPARATOR, $row);
    }

    private function addSessions(): void
    {
        $sessions = $this->sessionRepository->findByBikeRide($this->bikeRide);
        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                if (Session::AVAILABILITY_UNAVAILABLE !== $session->getAvailability()) {
                    $user = $this->userDtoTransformer->fromEntity($session->getUser());
                    $present = ($session->isPresent()) ? 'oui' : 'non';
                    $row = [
                        $user->licenceNumber,
                        $user->member->name, $user->member->firstName,
                        $user->member->birthDate,
                        $user->level->title,
                        $present,
                    ];
                    $this->fileContent[] = implode(self::SEPARATOR, $row);
                }
            }
        }
    }

    private function getResponse(): Response
    {
        $filename = $this->bikeRide->getTitle() . '_' . $this->bikeRide->getStartAt()->format('Y_m_d');
        $filename = $this->stringService->clean($filename) . '.csv';
        $response = new Response(implode(PHP_EOL, $this->fileContent));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
