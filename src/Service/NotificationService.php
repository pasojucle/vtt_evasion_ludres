<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\Licence;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Session;
use App\Entity\Survey;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class NotificationService
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly MessageService $messageService,
        private readonly BikeRideDtoTransformer $bikeRideDtoTransformer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getIndex(Survey|OrderHeader|Notification|Licence|string|array $entity)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $id = (null !== $user) ? $user->getLicenceNumber() : 'PUBLIC_ACCESS';
        return match (true) {
            is_string($entity) => sprintf('%s-%s', $id, $entity),
            is_array($entity) => $entity['index'],
            default => sprintf('%s-%s-%s', $id, (new ReflectionClass($entity))->getShortName(), $entity->getId())
        };
    }

    public function unNotify(OrderHeader|Licence $entity): void
    {
        $name = 'notifications_consumed';
        $notificationsConsumed = $this->sessionToArray($name);
        $notificationsConsumed[] = $this->getIndex($entity);
        $session = $this->requestStack->getSession();
        $session->set($name, json_encode($notificationsConsumed));
    }

    public function notify(Session|Survey $entity): void
    {
        $session = $this->requestStack->getSession();
        $notification = [
            'entity' => (new ReflectionClass($entity))->getShortName(),
            'entityId' => $entity->getId(),
        ];

        $session->set('notification', json_encode($notification));
    }

    public function clear(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('notification');
    }

    public function getNewSeasonReRegistration(): array
    {
        $season = $this->requestStack->getSession()->get('currentSeason');
        return [
            'index' => 'NEW_SEASON_RE_REGISTRATION_ENABLED',
            'title' => sprintf('Inscription à la saison %s', $season),
            'content' => $this->messageService->getMessageByName('NEW_SEASON_RE_REGISTRATION_ENABLED_MESSAGE'),
            'route' => 'user_registration_form',
            'routeParams' => ['step' => 1],
            'labelBtn' => 'S\'incrire'
        ];
    }

    public function getSurveyChanged(Survey|int $survey): array
    {
        if (is_int($survey)) {
            $survey = $this->entityManager->getRepository(Survey::class)->find($survey);
        }

        return [
            'index' => sprintf('SURVEY_CHANGED_%s', $survey->getId()),
            'title' => sprintf('Modification du sondage %s', $survey->getTitle()),
            'content' => str_replace('{{ sondage }}', $survey->getTitle(), $this->messageService->getMessageByName('SURVEY_CHANGED_MESSAGE')),
            'route' => 'survey',
            'routeParams' => ['survey' => $survey->getId()],
            'labelBtn' => 'Consulter'
        ];
    }

    public function getSessionRegistred(Session|int $session): array
    {
        if (is_int($session)) {
            $session = $this->entityManager->getRepository(Session::class)->find($session);
        }
        $content = ($session->getAvailability())
        ? '<p>Votre disponibilité à la sortie %s du %s a bien été prise en compte.</p><p> En cas de changement, il est impératif de se modifier sa disponibilité (voir dans Mon programme perso et faire "Modifier)"</p>'
        : '<p>Votre inscription à la sortie %s du %s a bien été prise en compte.</p><p> Si vous ne pouvez plus participez pas à cette sortie, il est impératif de se désinsrire (voir dans Mon programme perso et faire "Se désinscrire)"</p>';

        $bikeRideDto = $this->bikeRideDtoTransformer->fromEntity($session->getCluster()->getBikeRide());
        return [
            'index' => sprintf('Session_%s', $session->getId()),
            'title' => 'Inscription à une sortie',
            'content' => sprintf($content, $bikeRideDto->title, $bikeRideDto->period),

        ];
    }

    public function sessionToArray(string $name): array
    {
        $session = $this->requestStack->getSession();
        $value = $session->get($name);
        return (null !== $value) ? json_decode($value, true) : [];
    }
}
