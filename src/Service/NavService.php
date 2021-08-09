<?php

namespace App\Service;

class NavService
{
    private array $menus;
    private array $menusAdmin;
    private array $footer;

    public function __construct()
    {
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
                'label' => 'Programe du club',
                'route' => 'schedule',
                'pattern' => '/schedule/',
                'subMenus' => [],
            ],
            [
                'label' => 'Inscription',
                'route' => 'registration_detail',
                'pattern' => '/registration/',
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
            [
                'label' => 'Mon compte',
                'route' => 'user_account',
                'pattern' => '/user/',
                'subMenus' => [],
            ],
        ];
        $this->menusAdmin = [
            [
                'label' => 'Gestion du programme',
                 'route' => 'admin_events',
                 'pattern' => '/event/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Gestion des adhérents',
                 'route' => 'admin_users',
                 'pattern' => '/user/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Gestion des niveaux',
                 'route' => 'admin_events',
                 'pattern' => '/session/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Gestion de la page d\'accueil',
                 'route' => 'admin_home_contents',
                 'pattern' => '/admin_home/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Gestion des contenus',
                 'route' => 'admin_contents',
                 'pattern' => '/admin_content/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Gestion des liens',
                 'route' => 'admin_links',
                 'pattern' => '/link/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Paramètrage des inscriptions',
                 'route' => 'admin_registration_steps',
                 'pattern' => '/registration/',
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

    public function getMenusAdmin(): array
    {
        return $this->menusAdmin;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }
}