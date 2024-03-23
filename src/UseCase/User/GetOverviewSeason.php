<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\UserDto;
use App\Form\Admin\OverviewSaisonMemberType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetOverviewSeason
{
    public const TAB_NEW_REGISTRATIONS = 0;
    public const TAB_RE_REGISTRATIONS = 1;
    public const TAB_UNREGISTRATIONS = 2;
    private const TABS = [
        self::TAB_NEW_REGISTRATIONS => 'Nouvelles',
        self::TAB_RE_REGISTRATIONS => 'Renouvelées',
        self::TAB_UNREGISTRATIONS => 'Non renouvelés',
    ];

    public function __construct(
        private UserRepository $userRepository,
        private UserDtoTransformer $userDtoTransformer,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getMembers(Request $request, bool $filtered, int $tab): array
    {
        list($season) = $this->getSeason($request, $filtered);

        $form = $this->formFactory->create(OverviewSaisonMemberType::class, ['season' => sprintf('SEASON_%s', $season)]);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $season = (int) str_replace('SEASON_', '', $form->get('season')->getData());
        }
        $request->getSession()->set('admin_overview_season', json_encode([$season, $tab]));

        return [
            'users' => [
                self::TAB_NEW_REGISTRATIONS => $this->userDtoTransformer->fromEntities($this->userRepository->findNewRegisteredBySeason($season)),
                self::TAB_RE_REGISTRATIONS => $this->userDtoTransformer->fromEntities($this->userRepository->findReRegisteredBySeason($season)),
                self::TAB_UNREGISTRATIONS => $this->userDtoTransformer->fromEntities($this->userRepository->findUnRegisteredBySeason($season)),
            ],
            'season' => $season,
            'form' => $form->createView(),
            'tab' => $tab,
            'tabs' => self::TABS,
        ];
    }

    public function getSeason(Request $request, bool $filtered): array
    {
        if (!$filtered) {
            return [
                $request->getSession()->get('currentSeason') - 1,
                self::TAB_NEW_REGISTRATIONS,
            ];
        }
        return json_decode($request->getSession()->get('admin_overview_season'), true);
    }

    public function export(Request $request): Response
    {
        list($season) = $this->getSeason($request, true);
        $users = [
            self::TAB_NEW_REGISTRATIONS => $this->userRepository->findNewRegisteredBySeason($season),
            self::TAB_RE_REGISTRATIONS => $this->userRepository->findReRegisteredBySeason($season),
            self::TAB_UNREGISTRATIONS => $this->userRepository->findUnRegisteredBySeason($season),
        ];

        $content = [];
        $content[] = sprintf('Season %s - liste des licences', $season);
        foreach (array_keys(self::TABS) as $tab) {
            $content[] = '';
            $content[] = sprintf('%s - %s', self::TABS[$tab], count($users[$tab]));
            $this->addExportContent($content, $users[$tab]);
        }

        $response = new Response(implode(PHP_EOL, $content));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('export_synthese_saison_%s.csv', $season)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function addExportContent(array &$content, array $users): void
    {
        $row = ['Licence', 'Nom', 'Saisons'];
        $content[] = implode(',', $row);

        /** @var UserDto $user */
        foreach ($this->userDtoTransformer->fromEntities($users) as $user) {
            $row = [$user->licenceNumber, $user->member->fullName, $user->seasons];
            $content[] = implode(',', $row);
        }
    }

    public function emailsToClipboard(Request $request): string
    {
        list($season, $tab) = $this->getSeason($request, true);
        $users = match ($tab) {
            self::TAB_NEW_REGISTRATIONS => $this->userRepository->findNewRegisteredBySeason($season),
            self::TAB_RE_REGISTRATIONS => $this->userRepository->findReRegisteredBySeason($season),
            self::TAB_UNREGISTRATIONS => $this->userRepository->findUnRegisteredBySeason($season),
            default => [],
        };
        $emails = [];
        foreach ($users as $user) {
            $emails[] = $this->userDtoTransformer->mainEmailFromEntity($user);
        }
        return implode(',', $emails);
    }
}
