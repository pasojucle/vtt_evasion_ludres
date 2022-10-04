<?php

declare(strict_types=1);

namespace App\Service;

class MenuService
{
    private array $menus;

    private array $footer;

    public function __construct() {
        $this->menus = [
            [
                'label' => 'Le club',
                'route' => 'club',
                'pattern' => '/club/',
                'subMenus' => [],
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
                ],
            ],
            [
                'label' => 'Programme du club',
                'route' => 'schedule',
                'pattern' => '/schedule/',
                'subMenus' => [],
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
            ],
            [
                'label' => 'Boutique',
                'route' => 'products',
                'pattern' => '/product/',
                'subMenus' => [],
            ],
            [
                'label' => 'Liens',
                'route' => 'links',
                'pattern' => '/links/',
                'subMenus' => [],
            ],
            [
                'label' => 'Contacts',
                'route' => 'contact',
                'pattern' => '/contact/',
                'subMenus' => [],
            ],
        ];
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
    }

    public function getMenus(): array
    {
        return $this->menus;
    }

    public function getFooter(): array
    {
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
