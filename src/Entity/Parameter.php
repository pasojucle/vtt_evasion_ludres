<?php

namespace App\Entity;

use ApiPlatform\OpenApi\Model;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Enum\ParameterKindEnum;
use App\State\ParameterStateProcessor;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ParameterRepository::class)]
#[ApiResource(
    shortName: 'Parameter',
    security: "is_granted('ROLE_USER')",
)]
#[GetCollection()]
#[Post(
    uriTemplate: 'parameters/fileupload/{name}',
    outputFormats: ['jsonld' => ['application/ld+json']],
    inputFormats: ['multipart' => ['multipart/form-data']],
    openapi: new Model\Operation(
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => [
                                'type' => 'string',
                                'format' => 'binary'
                            ]
                        ]
                    ]
                ]
            ])
        )
    ),
    normalizationContext: ['groups' => ['param:read']],
    denormalizationContext: ['groups' => ['param:write']],
    processor: ParameterStateProcessor::class,
)]
#[Patch(
    processor: ParameterStateProcessor::class,
)]

class Parameter
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "NONE")]
    #[ORM\Column(length: 50)]
    #[ApiProperty(identifier: true)]
    #[Groups(['param:read'])]
    private string $name = 'UNDEFINED';

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['param:read'])]
    private ?string $label = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['param:read'])]
    private ?string $value = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['param:read'])]
    private ?array $options = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['param:read'])]
    private ?int $position = null;

    #[ORM\Column(type: 'ParameterKind')]
    #[Groups(['param:read'])]
    private ParameterKindEnum $kind = ParameterKindEnum::TEXT;

    #[Groups(['param:write'])]
    public ?File $file = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getKind(): object
    {
        return $this->kind;
    }

    public function setKind(ParameterKindEnum $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): static
    {
        $this->file = $file;

        return $this;
    }
}
