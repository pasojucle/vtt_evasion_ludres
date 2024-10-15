<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OrderHeader;
use App\Entity\User;
use App\Service\Order\OrderGetService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuService
{
    private array $user = [];

    private array $footer;

    public function __construct(
        private RequestStack $requestStack,
        private MenuAdminService $menuAdminService,
        private OrderGetService $orderGetService,
        private Security $security
    ) {
    }

    public function getMenus(): array
    {
        return $this->menuAdminService->getMenusGranted([
            [
                'label' => 'Le club',
                'route' => null,
                'pattern' => '/club/',
                'subMenus' => $this->setSubMenusStatus($this->getClubSubMenu()),
                'role' => 'PUBLIC_ACCESS',
                'badge' => null,
            ],
            [
                'label' => 'L\'école VTT',
                'route' => null,
                'pattern' => '/school/',
                'subMenus' => $this->setSubMenusStatus($this->getSchoolSubMenu()),
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Programme du club',
                'route' => 'schedule',
                'pattern' => '/schedule/',
                'subMenus' => [],
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Inscription',
                'route' => null,
                'pattern' => '/registration/',
                'subMenus' => $this->menuAdminService->getMenusGranted($this->getRegistrationSubMenus()),
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Boutique',
                'route' => 'products',
                'pattern' => '/product/',
                'subMenus' => [],
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Occasions',
                'route' => 'second_hand_list',
                'pattern' => '/second_hand/',
                'subMenus' => [],
                'role' => 'SECOND_HAND_LIST',
                'badge' => 'notification_second_hand',
            ],
            [
                'label' => 'Liens',
                'route' => 'links',
                'pattern' => '/links/',
                'subMenus' => [],
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Contacts',
                'route' => 'contact',
                'pattern' => '/contact/',
                'subMenus' => [],
                'role' => 'PUBLIC_ACCESS',
            ],
        ]);
    }

    public function getUser(): array
    {
        $this->user = $this->menuAdminService->getMenusGranted([
            [
                'label' => 'Mon programme perso',
                'route' => 'user_sessions',
                'role' => 'BIKE_RIDE_LIST',
            ],
            [
                'label' => 'Mes infos',
                'route' => 'user_account',
                'role' => 'ROLE_USER',
            ],
            [
                'label' => 'Mon carnet de progression',
                'route' => 'user_account',
                'role' => 'ROLE_USER',
            ],
            [
                'label' => 'Mes commandes',
                'route' => 'user_orders',
                'role' => 'PRODUCT_LIST',
            ],
            [
                'label' => 'Mes sondages',
                'route' => 'user_surveys',
                'role' => 'SURVEY_LIST',
            ],
            [
                'label' => 'Mes annonces',
                'route' => 'second_hand_user_list',
                'role' => 'SECOND_HAND_LIST',
            ],
        ]);

        $fullName = $this->requestStack->getSession()->get('user_fullName');

        return [
            'fullName' => $fullName,
            'menus' => ($fullName) ? $this->user : [],
        ];
    }
    public function getClubSubMenu(): array
    {
        return [
            [
                'label' => 'Présentation',
                'route' => 'club_overview',
                'pattern' => '/club_overview/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Diaporama',
                'route' => 'club_slideshow',
                'pattern' => '/club_slideshow/',
                'role' => 'SLIDESHOW_LIST',
                'badge' => 'notification_slideshow',
            ],
            [
                'label' => 'Actualités',
                'route' => 'club_summary',
                'pattern' => '/club_summary/',
                'role' => 'SUMMARY_LIST',
                'badge' => 'notification_summary_list',
            ],
        ];
    }

    public function getSchoolSubMenu(): array
    {
        return [
            [
                'label' => 'Présentation',
                'route' => 'school_overview',
                'pattern' => '/school_overview/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Les disciplines',
                'route' => 'school_practices',
                'pattern' => '/school_practices/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Fonctionnement',
                'route' => 'school_operating',
                'pattern' => '/school_operating/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Équipement',
                'route' => 'school_equipment',
                'pattern' => '/school_equipment/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Documentation',
                'route' => 'school_documentation',
                'pattern' => '/school_documentation/',
                'role' => 'DOCUMENTATION_LIST',
            ],
        ];
    }

    public function getRegistrationSubMenus(): array
    {
        return [
            [
                'label' => 'S\'inscrire',
                'route' => 'registration_detail',
                'pattern' => '/registration_detail/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Les tarifs',
                'route' => 'registration_membership_fee',
                'pattern' => '/registration_membership_fee/',
                'role' => 'PUBLIC_ACCESS',
            ],
            [
                'label' => 'Tuto',
                'route' => 'registration_tuto',
                'pattern' => '/registration_tuto/',
                'role' => 'PUBLIC_ACCESS',
            ],
        ];
    }

    public function displayCart(): bool
    {
        return $this->orderGetService->getOrderByUser() instanceof OrderHeader;
    }

    public function accessAdmin(): bool
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            return false;
        }
        /** @var User $user */
        $user = $this->security->getUser();
        return $user->hasAtLeastOnePermission() || $this->security->isGranted('ROLE_ADMIN');
    }

    public function getFooter(): array
    {
        $this->footer = [
            [
                'label' => 'Réglement intérieur',
                'route' => 'rules',
                'pattern' => '/reglement/',
                'subMenus' => [],
            ],
            [
                'label' => 'Mentions légales',
                'route' => 'legal_notices',
                'pattern' => '/legales/',
                'subMenus' => [],
            ],
        ];
        return $this->footer;
    }

    public function getIndexableRoutes(): array
    {
        $routes = [];
        $menuByType = ['menus' => $this->getMenus(), 'footer' => $this->getFooter()];

        foreach ($menuByType as $type => $menus) {
            foreach ($menus as $menu) {
                $priority = ('menus' === $type) ? 1 : 0.5;
                if (empty($menu['subMenus'])) {
                    $routes[] = ['route' => $menu['route'], 'priority' => $priority];
                } else {
                    foreach ($menu['subMenus'] as $subMenu) {
                        $routes[] = ['route' => $subMenu['route'], 'priority' => $priority];
                    }
                }
            }
        }

        return $routes;
    }


    public function setSubMenusStatus(array $subMenus): array
    {
        foreach ($subMenus as $key => $subMenu) {
            if (!$this->security->isGranted($subMenu['role'])) {
                $subMenus[$key]['class'] = 'disabled';
            }
        }

        return $subMenus;
    }
}
