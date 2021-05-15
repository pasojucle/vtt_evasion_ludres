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
                        'label' => 'Bulletin d\'inscription adulte',
                        'route' => 'subscription',
                        'params' => ['type' => 'adulte'],
                    ],
                    [
                        'label' => 'Bulletin d\'inscription mineur',
                        'route' => 'subscription',
                        'params' => ['type' => 'mineur'],
                    ],
                    [
                        'label' => 'Bulletin d\'inscription 3 séances gratuites',
                        'route' => 'subscription',
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
                        'route' => 'subscription',
                        'params' => ['type' => 'seances_gratuites'],
                    ],
                ],
            ],
            [
                'label' => 'Liens partenaires, institutionnels',
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