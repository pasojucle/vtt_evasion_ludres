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
                'subMenus' => [
                    [
                        'label' => 'Inscription',
                        'route' => 'registration',
                        'params' => [],
                    ],
                    [
                        'label' => 'Bulletin d\'inscription adulte',
                        'route' => 'registration_form',
                        'params' => ['type' => 'adulte'],
                    ],
                    [
                        'label' => 'Bulletin d\'inscription mineur',
                        'route' => 'registration_form',
                        'params' => ['type' => 'mineur'],
                    ],
                    [
                        'label' => 'Bulletin d\'inscription 3 séances gratuites',
                        'route' => 'registration_form',
                        'params' => ['type' => 'seances_gratuites'],
                    ],
                ],
            ],
            [
                'label' => 'L\'école VTT',
                'route' => 'school',
                'subMenus' => [],
            ],
            [
                'label' => 'Rando',
                'route' => 'bike_rides',
                'subMenus' => [
                    [
                        'label' => 'Rando dimanche matin',
                        'route' => 'registration_form',
                        'params' => ['type' => 'seances_gratuites'],
                    ],
                ],
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