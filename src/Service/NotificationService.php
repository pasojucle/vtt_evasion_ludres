<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Documentation;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Licence;
use App\Entity\Member;
use App\Entity\Notification;
use App\Entity\OrderHeader;
use App\Entity\Session;
use App\Entity\Survey;
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
        private readonly LogService $logService,
    ) {
    }

    public function getIndex(Survey|OrderHeader|Notification|Licence|BikeRide|string|array $entity)
    {
        /** @var Member $member */
        $member = $this->security->getUser();
        $id = (null !== $member) ? $member->getLicenceNumber() : 'PUBLIC_ACCESS';
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
            'url' => $this->urlGenerator->generate('user_registration_form', ['step' => 1]),
            'btnLabel' => 'S\'incrire',
            'modalLink' => $this->urlGenerator->generate('notification_show', ['entityName' => 'NEW_SEASON_RE_REGISTRATION_ENABLED']),
        ];
    }

    public function setSurveyChanged(int $survey): array
    {
        $survey = $this->entityManager->getRepository(Survey::class)->find($survey);

        return [
            'index' => sprintf('NOTIFY_SURVEY_CHANGED_%s', $survey->getId()),
            'title' => 'Notifier les changements du sondage',
            'content' => sprintf('Le sondage <b>%s</b> a été modifié alors que certain adhérents ont déja répondu.</p><p>Voulez vous leur notifier les changements ?</p>', $survey->getTitle()),
            'url' => $this->urlGenerator->generate('admin_survey_history_notify', ['survey' => $survey->getId()]),
            'btnLabel' => 'Notifier les changements',
            'modalLink' => $this->urlGenerator->generate('notification_show', [
                'entityName' => 'NOTIFY_SURVEY_CHANGED',
            ]),
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
            'url' => $this->urlGenerator->generate('survey', ['survey' => $survey->getId()]),
            'btnLabel' => 'Consulter',
            'modalLink' => $this->urlGenerator->generate('notification_show', [
                'entityName' => 'SURVEY_CHANGED',
                'entityId' => $survey->getId(),
            ]),
        ];
    }

    public function getClusterExport(Cluster $cluster): array
    {
        return [
            'index' => sprintf('CLUSTER_EXPORT_%s', $cluster->getId()),
            'title' => sprintf('Export la liste %s', $cluster->getTitle()),
            'content' => str_replace('{{ groupe }}', $cluster->getTitle(), $this->messageService->getMessageByName('CLUSTER_EXPORT_MESSAGE')),
            'btnLabel' => 'Télécharger',
            'modalLink' => $this->getModalLinkFromEntity($cluster),
            'action' => [
                'name' => 'click->modal#followLink',
                'url' => $this->urlGenerator->generate('admin_cluster_export', ['cluster' => $cluster->getId()]),
            ],
        ];
    }

    public function getSessionRegistred(Session|int $session): array
    {
        if (is_int($session)) {
            $session = $this->entityManager->getRepository(Session::class)->find($session);
        }
        $messageName = (AvailabilityEnum::NONE !== $session->getAvailability()) ? 'NEW_SESSION_FRAMER' : 'NEW_SESSION_MEMBER';
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
            'btnLabel' => 'J\'ai compris',
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
            'btnLabel' => 'Voir Les notifications',
            'action' => [
                'name' => 'click->notifications#toggleNotifications click->modal#close',
            ],
        ];
    }

    public function getDocumentation(Documentation $documentation): array
    {
        return [
            'index' => sprintf('documentation-%s', $documentation->getId()),
            'title' => $documentation->getName(),
            'content' => $this->messageService->getMessageByName('DOCUMENTATION_LINK_WARNING_MESSAGE'),
            'btnLabel' => 'Consulter',
            'modalLink' => $this->getModalLinkFromEntity($documentation),
            'form' => $this->logService->getForm([
                'entityName' => 'Documentation',
                'entityId' => $documentation->getId(),
            ]),
            'action' => [
                'name' => 'click->modal#followLink',
                'url' => $documentation->getLink(),
                'target' => '_blank',
                'frameId' => sprintf('notification-documentation-%s', $documentation->getId())
            ],
        ];
    }

    public function getModalLinkFromEntity(Survey|Notification|Licence|OrderHeader|BikeRide|Cluster|Session|Documentation $entity): string
    {
        return $this->urlGenerator->generate('notification_show', [
            'entityName' => (new ReflectionClass($entity))->getShortName(),
            'entityId' => $entity->getId(),
        ]);
    }
}
