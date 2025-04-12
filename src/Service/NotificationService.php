<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Documentation;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly MessageService $messageService,
        private readonly BikeRideDtoTransformer $bikeRideDtoTransformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ReplaceKeywordsService $replaceKeywordsService,
    ) {
    }

    public function getIndex(Survey|OrderHeader|Notification|Licence|BikeRide|string|array $entity)
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

    public function setSurveyChanged(int $survey): array
    {
        $survey = $this->entityManager->getRepository(Survey::class)->find($survey);

        return [
            'index' => sprintf('NOTIFY_SURVEY_CHANGED_%s', $survey->getId()),
            'title' => 'Notifier les changements du sondage',
            'content' => sprintf('Le sondage <b>%s</b> a été modifié alors que certain adhérents ont déja répondu.</p><p>Voulez vous leur notifier les changements ?</p>', $survey->getTitle()),
            'route' => 'admin_survey_history_notify',
            'routeParams' => ['survey' => $survey->getId()],
            'labelBtn' => 'Notifier les changements',
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
            'labelBtn' => 'Consulter',
            'modalLink' => $this->getModalLinkFromEntity($survey),
        ];
    }

    public function getClusterExport(Cluster $cluster): array
    {
        return [
            'index' => sprintf('CLUSTER_EXPORT_%s', $cluster->getId()),
            'title' => sprintf('Export la liste %s', $cluster->getTitle()),
            'content' => str_replace('{{ groupe }}', $cluster->getTitle(), $this->messageService->getMessageByName('CLUSTER_EXPORT_MESSAGE')),
            'route' => 'admin_cluster_export',
            'routeParams' => ['cluster' => $cluster->getId()],
            'labelBtn' => 'Télécharger',
            'modalLink' => $this->getModalLinkFromEntity($cluster),
            'closeAfter' => true,
        ];
    }

    public function getSessionRegistred(Session|int $session): array
    {
        if (is_int($session)) {
            $session = $this->entityManager->getRepository(Session::class)->find($session);
        }
        $messageName = ($session->getAvailability()) ? 'NEW_SESSION_FRAMER' : 'NEW_SESSION_MEMBER';
        $content = $this->messageService->getMessageByName($messageName);
        $bikeRideDto = $this->bikeRideDtoTransformer->fromEntity($session->getCluster()->getBikeRide());
        $additionalParams = [
            '{{ rando }}' => sprintf('%s du %s', $bikeRideDto->title, $bikeRideDto->period),
        ];

        return [
            'index' => sprintf('Session_%s', $session->getId()),
            'title' => 'Inscription à une sortie',
            'content' => $this->replaceKeywordsService->replaceWhithParams($content, $additionalParams),
            'modalLink' => $this->getModalLinkFromEntity($session),
        ];
    }

    public function sessionToArray(string $name): array
    {
        $session = $this->requestStack->getSession();
        $value = $session->get($name);
        return (null !== $value) ? json_decode($value, true) : [];
    }

    public function getPendingNotifications(int $total): array
    {
        return [
            'index' => 'pending',
            'title' => 'Notifications en attentes',
            'content' => sprintf('Vous avez %d notifications en attente de traitement', $total),
            'url' => '#',
            'labelBtn' => 'Voir Les notifications',
            'toggle' => 'notifications',
        ];
    }

    public function getDocumentation(Documentation $documentation): array
    {
        return [
            'index' => sprintf('documentation-%s', $documentation->getId()),
            'title' => $documentation->getName(),
            'content' => $this->messageService->getMessageByName('DOCUMENTATION_LINK_WARNING_MESSAGE'),
            'labelBtn' => 'Consulter',
            'url' => $documentation->getLink(),
            'target' => '_blank',
        ];
    }

    public function getModalLinkFromEntity(string|Survey|Notification|Licence|OrderHeader|BikeRide|Cluster|Session $entity): string
    {
        return $this->urlGenerator->generate('notification_show', [
            'entityName' => (new ReflectionClass($entity))->getShortName(),
            'entityId' => $entity->getId(),
        ]);
    }
}
