<?php

declare(strict_types=1);

namespace App\Service;

class GeoService
{
    public function __construct()
    {
        $this->departmentIds = array_merge(range(1,19), range(21,95),range(971,974), ['2A', '2B', 976]);
    }

    public function getCommunesByDepartment(string|int $departmentId): array|false
    {
        return $this->curlExecute(sprintf("https://geo.api.gouv.fr/departements/%s/communes", $departmentId));

    }

    public function getCommunesByName(string $commune): array|false
    {
        return $this->curlExecute(sprintf("https://geo.api.gouv.fr/communes?nom=%s&fields=departement,codesPostaux&format=json&limit=5", urlencode($commune)));

    }

    public function getCommunesByPostalCode(string $postlCode): array|false
    {
        return $this->curlExecute(sprintf("https://geo.api.gouv.fr/communes?codePostal=%s", urlencode($postlCode)));

    }

    public function getDepartmentByCode(string $code): array|false
    {
        return $this->curlExecute(sprintf("https://geo.api.gouv.fr/departements/%s", $code));
    }

    private function curlExecute(string $url): array|false
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
        $result = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);

        if (!empty($result) && !is_bool($result) && empty($error)) {
            return json_decode($result, true);
        }

        return false;
    }
}