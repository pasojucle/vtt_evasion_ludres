<?php

declare(strict_types=1);

namespace App\Mapper\Cluster;

use App\Dto\View\BadgeView;
use App\Dto\View\Cluster\Tab\ClusterView;
use App\Entity\Cluster;
use App\Entity\Enum\LevelType;
use App\Entity\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClusterTabMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ){}
    public function mapToView(Cluster $cluster): ClusterView
    {
        $pratice = $cluster->getPractice();
        $isNeedFramers = $cluster->getBikeRide()->getBikeRideType()->isNeedFramers();

        return new ClusterView(
            id: $cluster->getId(),
            url: $this->urlGenerator->generate('admin_cluster_show', ['cluster' => $cluster->getId()]),
            title: $cluster->getTitle(),
            pratice: new BadgeView(
                value: $pratice->trans($this->translator),
                variant: $pratice->variant(),
            ),
            total: $cluster->getSessions()->map(function(Session $session) use ($isNeedFramers) {
                if ($isNeedFramers) {
                    return $session->isPresent()
                    && LevelType::SCHOOL === $session->getUser()->getLevel()?->getType();
                }
                return $session->isPresent();
            })->count(),
        );
    }
}