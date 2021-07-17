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
                'pattern' => '/school/',
                'subMenus' => [],
            ],
            [
                'label' => 'Programe du club',
                'route' => 'schedule',
                'pattern' => '/schedule/',
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