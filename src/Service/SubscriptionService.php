<?php

namespace App\Service;

class SubscriptionService
{
    private array $steps;

    public function __construct()
    {
        $this->steps = [
            1 => [
                'title' => 'Tableaux des garanties',
                'types' => 'all',
                'filename' => 'Tableau_des_licences_2020.pdf',
                'form' => null,
                'template' => null,
            ],
            2 => [
                'title' => 'Cotisation',
                'types' => 'all',
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
            3 => [
                'title' => 'QS sport',
                'types' => 'all',
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
            4 => [
                'title' => 'Informations du parent ou tuteur de l\'enfant',
                'types' => ['mineur'],
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
            5 => [
                'title' => ['adulte' => 'Informations personnelles', 'mineur' => 'Informations de l\'enfant'],
                'types' => 'all',
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
            6 => [
                'title' => 'Santé de l\'enfant',
                'types' => ['mineur'],
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
            7 => [
                'title' => 'Licence',
                'types' => 'all',
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
            8 => [
                'title' => 'Droit à l\'image',
                'types' => 'all',
                'filename' => null,
                'form' => null,
                'template' => null,
            ],
        ];
    }

    public function getProgress($type, $step)
    {
        $progress = [];
        $progress['prev'] = null;
        $progress['next'] = null;

        foreach($this->steps as $key => $data) {
            if ($data['types'] === 'all' || (is_array($data['types']) && in_array($type, $data['types']))) {
                if ($key < $step) {
                    $data['class'] = 'is-done';
                    $progress['prev'] = $key;
                } elseif ($key === $step) {
                    $data['class'] = 'current';
                } else {
                    $data['class'] = null;
                    if (null === $progress['next']) {
                        $progress['next'] = $key;
                    }
                }
                if (is_array($data['title'])) {
                    $data['title'] = $data['title'][$type];
                }
                $progress['steps'][$key] = $data;
            }
        }

        return $progress;
    }
}