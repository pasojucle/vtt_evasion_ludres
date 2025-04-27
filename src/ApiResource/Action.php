<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\ActionDto;
use App\State\ActionStateProvider;

#[ApiResource(shortName: 'Action')]
#[GetCollection(
    name: 'action_collection',
    output: ActionDto::class,
    provider: ActionStateProvider::class,
    parameters: ['section' => new QueryParameter(), 'type' => new QueryParameter()]
)]

class Action
{
    private string $classRoute = '';

    private string $methodRoute = '';

    private string $type;

    private string $section;

    private ?string $icon = null;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }


    public function getMethodRoute(): string
    {
        return $this->methodRoute;
    }

    public function setMethodRoute(string $methodRoute): static
    {
        $this->methodRoute = $methodRoute;

        return $this;
    }

    public function getClassRoute(): string
    {
        return $this->classRoute;
    }

    public function setClassRoute(string $classRoute): static
    {
        $this->classRoute = $classRoute;

        return $this;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
