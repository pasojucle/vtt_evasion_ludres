<?php

namespace App\Service;

class HeaderService
{
    private array $menus;

    public function __construct()
    {
        $this->menus = [
            [
                'label' => 'Accueil',
                 'route' => 'home',
                 'subMenus' => [],
            ],
            [
                'label' => 'Le club',
                'route' => 'club',
                'subMenus' => [],
            ],
            [
                'label' => 'Inscription',
                'route' => 'registration_detail',
                'subMenus' => [],
            ],
            [
                'label' => 'L\'école VTT',
                'route' => 'school',
                'subMenus' => [],
            ],
            [
                'label' => 'Rando',
                'route' => 'bike_rides',
                'subMenus' => [],
            ],
            [
                'label' => 'Les partenaires',
                'route' => 'links',
                'subMenus' => [],
            ],
            [
                'label' => 'Programe du club',
                'route' => 'program',
                'subMenus' => [],
            ],
            [
                'label' => 'Contacts',
                'route' => 'contact',
                'subMenus' => [],
            ],
        ];
    }

    public function getMenus()
    {
        return $this->menus;
    }
}