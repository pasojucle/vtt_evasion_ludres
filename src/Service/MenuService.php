<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class MenuService
{
    private array $menus;

    private array $user = [];

    private array $footer;

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function getMenus(): array
    {
        $fullName = $this->requestStack->getSession()->get('user_fullName');
        $this->menus = [
            [
                'label' => 'Le club',
                'route' => 'club',
                'pattern' => '/club/',
                'subMenus' => [],
                'display' => true,
            ],
            [
                'label' => 'L\'école VTT',
                'route' => null,
                'pattern' => '/school/',
                'subMenus' => [
                    [
                        'label' => 'Présentation',
                        'route' => 'school_overview',
                        'pattern' => '/school_overview/',
                    ],
                    [
                        'label' => 'Les disciplines',
                        'route' => 'school_practices',
                        'pattern' => '/school_practices/',
                    ],
                    [
                        'label' => 'Fonctionnement',
                        'route' => 'school_operating',
                        'pattern' => '/school_operating/',
                    ],
                    [
                        'label' => 'Équipement',
                        'route' => 'school_equipment',
                        'pattern' => '/school_equipment/',
                    ],
                    [
                        'label' => 'Documentation',
                        'route' => 'school_documentation',
                        'pattern' => '/school_documentation/',
                    ],
                ],
                'display' => true,
            ],
            [
                'label' => 'Programme du club',
                'route' => 'schedule',
                'pattern' => '/schedule/',
                'subMenus' => [],
                'display' => true,
            ],
            [
                'label' => 'Inscription',
                'route' => null,
                'pattern' => '/registration/',
                'subMenus' => [
                    [
                        'label' => 'S\'inscrire',
                        'route' => 'registration_detail',
                        'pattern' => '/registration_detail/',
                    ],
                    [
                        'label' => 'Les tarifs',
                        'route' => 'registration_membership_fee',
                        'pattern' => '/registration_membership_fee/',
                    ],
                    [
                        'label' => 'Tuto',
                        'route' => 'registration_tuto',
                        'pattern' => '/registration_tuto/',
                    ],
                ],
                'display' => true,
            ],
            [
                'label' => 'Boutique',
                'route' => 'products',
                'pattern' => '/product/',
                'subMenus' => [],
                'display' => true,
            ],
            [
                'label' => 'Occasions',
                'route' => 'second_hand_list',
                'pattern' => '/second_hand/',
                'subMenus' => [],
                'display' => null !== $fullName,
            ],
            [
                'label' => 'Liens',
                'route' => 'links',
                'pattern' => '/links/',
                'subMenus' => [],
                'display' => true,
            ],
            [
                'label' => 'Contacts',
                'route' => 'contact',
                'pattern' => '/contact/',
                'subMenus' => [],
                'display' => true,
            ],
        ];
        return $this->menus;
    }

    public function getUser(): array
    {
        $this->user = [
            [
                'label' => 'Mon programme perso',
                'route' => 'user_bike_rides',
            ],
            [
                'label' => 'Mes infos',
                'route' => 'user_account',
            ],
            [
                'label' => 'Mes commandes',
                'route' => 'user_orders',
            ],
            [
                'label' => 'Mes sondages',
                'route' => 'user_surveys',
            ],
            [
                'label' => 'Mes annonces',
                'route' => 'second_hand_user_list',
            ],
        ];

        $fullName = $this->requestStack->getSession()->get('user_fullName');

        return [
            'fullName' => $fullName,
            'menus' => ($fullName) ? $this->user : [],
        ];
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
}
