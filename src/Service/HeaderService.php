<?php

namespace App\Service;

class HeaderService
{
    private array $menus;
    private array $menusAdmin;

    public function __construct()
    {
        $this->menus = [
            [
                'label' => 'Accueil',
                 'route' => 'home',
                 'pattern' => '/home/',
                 'subMenus' => [],
            ],
            [
                'label' => 'Le club',
                'route' => 'club',
                'pattern' => '/club/',
                'subMenus' => [],
            ],
            [
                'label' => 'Inscription',
                'route' => 'registration_detail',
                'pattern' => '/registration/',
                'subMenus' => [],
            ],
            [
                'label' => 'L\'école VTT',
                'route' => 'school',
                'pattern' => '/home/',
                'subMenus' => [],
            ],
            [
                'label' => 'Rando',
                'route' => 'bike_rides',
                'pattern' => '/home/',
                'subMenus' => [],
            ],
            [
                'label' => 'Liens',
                'route' => 'links',
                'pattern' => '/links/',
                'subMenus' => [],
            ],
            [
                'label' => 'Programe du club',
                'route' => 'program',
                'pattern' => '/home/',
                'subMenus' => [],
            ],
            [
                'label' => 'Contacts',
                'route' => 'contact',
                'pattern' => '/home/',
                'subMenus' => [],
            ],
        ];
        $this->menusAdmin = [
            [
                'label' => 'Gestion du calendrier',
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
                'label' => 'Gestion des accompagnateurs',
                 'route' => 'admin_events',
                 'pattern' => '/session/',
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
                 'route' => 'admin_events',
                 'pattern' => '/session/',
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
    }

    public function getMenus(): array
    {
        return $this->menus;
    }

    public function getMenusAdmin(): array
    {
        return $this->menusAdmin;
    }
}