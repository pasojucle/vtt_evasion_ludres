<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NavService
{
    private array $menus;
    private array $menusAdmin;
    private array $footer;
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
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
                    ['label' => 'Présentation',
                    'route' => 'school_overview',
                    'pattern' => '/school/',],
                    ['label' => 'Les disciplines',
                    'route' => 'school_practices',
                    'pattern' => '/school/',],
                    ['label' => 'Fonctionnement',
                    'route' => 'school_operating',
                    'pattern' => '/school/',],
                    ['label' => 'Équipement',
                    'route' => 'school_equipment',
                    'pattern' => '/school/',],
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
                    ['label' => 'S\'inscrire',
                    'route' => 'registration_detail',
                    'pattern' => '/registration/',],
                    ['label' => 'Les tarifs',
                    'route' => 'registration_membership_fee',
                    'pattern' => '/registration/',],
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
        $this->menusAdmin = [
            [
                'label' => 'Gestion du programme',
                 'route' => 'admin_events',
                 'pattern' => '/event/',
                 'subMenus' => [],
                 'role' => 'ROLE_FRAME',
            ],
            [
                'label' => 'Gestion des adhérents',
                 'route' => 'admin_users',
                 'pattern' => '/user/',
                 'subMenus' => [],
                 'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Gestion des inscriptions',
                 'route' => 'admin_registrations',
                 'pattern' => '/admin_registrations/',
                 'subMenus' => [],
                 'role' => 'ROLE_REGISTER',
            ],
            [
                'label' => 'Gestion de la boutique',
                 'route' => 'admin_products',
                 'pattern' => '/produit/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Gestion des commandes',
                 'route' => 'admin_orders',
                 'pattern' => '/commande/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Gestion des niveaux',
                 'route' => 'admin_levels',
                 'pattern' => '/level/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Gestion de la page d\'accueil',
                 'route' => 'admin_home_contents',
                 'pattern' => '/admin_home/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Gestion des contenus',
                 'route' => 'admin_contents',
                 'pattern' => '/admin_content/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Gestion des liens',
                 'route' => 'admin_links',
                 'pattern' => '/link/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
            ],
            [
                'label' => 'Paramètrage des inscriptions',
                 'route' => 'admin_registration_steps',
                 'pattern' => '/registration_step/',
                 'subMenus' => [],
                 'role' => 'ROLE_ADMIN',
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

    public function getMenusAdmin(): array
    {
        return $this->menusAdmin;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

    public function getMaintenance()
    {
        $maintenance = $this->parameterBag->get('maintenance');

        return ($maintenance) ? filter_var($maintenance['status'], FILTER_VALIDATE_BOOL) : false;
    }

    public function getIndexableRoutes(): array
    {
        $routes = [];
        $allMenus = array_merge($this->getMenus(), $this->getFooter());
        foreach($allMenus as $menu) {
            if (empty($menu['subMenus'])) {
                $routes[] = $menu['route'];
            } else {
                foreach($menu['subMenus'] as $subMenu) {
                    $routes[] = $subMenu['route'];
                }
            }
        }

        return $routes;
    }
}