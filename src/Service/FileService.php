<?php

declare(strict_types=1);

namespace App\Service;

class FileService
{
    private const UNITS = ['b', 'k', 'm', 'g', 't'];

    public function bytesToHuman($bytes): string
    {
        if ($bytes == 0) {
            return "0.00 B";
        }

        $exponent = floor(log($bytes, 1024));

        return sprintf('%s%s', round($bytes / pow(1024, $exponent), 2), strtoupper(self::UNITS[$exponent]));
    }


    public function humanToBytes(string $value): ?int
    {
        if (1 === preg_match('#^(\d+)([kmgt])#i', $value, $matches)) {
            $exponent = array_search(strtolower($matches[2]), self::UNITS);

            return (int) $matches[1] * pow(1024, $exponent);
        }
        return null;
    }
}
