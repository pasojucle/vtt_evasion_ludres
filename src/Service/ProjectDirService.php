<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProjectDirService
{

    private array $directories;

    public function __construct(private ParameterBagInterface $parameterBag)
    {
        $this->directories = $this->parameterBag->get('project_directories');
    }


    public function dir(... $dirNames): string
    {
        $dirs = [];
        foreach ($dirNames as $dirName) {
            $dirs[] = $this->getDir($dirName);
        }

        return $this->join($dirs);
    }


    public function path(... $dirNames): string
    {
        $dirs = [];
        $dirs[] = $this->directories['project'];
        foreach ($dirNames as $dirName) {
            $dirs[] = $this->getDir($dirName);
        }

        return $this->join($dirs);
    }

    private function getDir(string $dirName): string
    {
        if (array_key_exists($dirName, $this->directories)) {
            $dirs[] = $dirName;
        }
        return $dirName;
    }


    private function join(array $dirs): string
    {
        return implode(DIRECTORY_SEPARATOR, $dirs);
    }
}