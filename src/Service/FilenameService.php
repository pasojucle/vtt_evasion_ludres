<?php

declare(strict_types=1);

namespace App\Service;

class FilenameService
{
    public function clean(string $filename)
    {
        //On va remplacer tous les caractères accentués par leur équivalent sans accent pour le nom des champs du formulaire
        $utf8 = [
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
        ];
        $filename = preg_replace(array_keys($utf8), array_values($utf8), $filename);
        //Pour le nom du champ du formulaire on ne laisse que les lettres, chiffres et underscore
        return preg_replace('/[^a-zA-Z0-9]+/', '_', $filename);
    }
}
