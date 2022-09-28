<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CommandLineService
{
    private ?string $phpVersion = null;

    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }
    
    public function getRequirePhpVersion(): string
    {
        $composer = json_decode(file_get_contents($this->parameterBag->get('project_directory') . 'composer.json'), true);
        preg_match('#([0-9.]+)$#', $composer['require']['php'], $matches);

        return $this->phpVersion = $matches[1];
    }
    
    public function getPhpVersion(): string
    {
        return PHP_VERSION;
    }
    
    public function getBinConsole(): string|false
    {
        return $this->getBinay() . ' bin/console';
    }
    
    public function getBinay(): string|false
    {
        return PHP_BINARY;
    }
}
