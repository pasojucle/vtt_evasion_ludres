<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SecondHandDto;
use App\Entity\SecondHand;
use App\Entity\SecondHandImage;
use App\Model\Currency;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SecondHandDtoTransformer
{
    public const UN_VALIDED = 0;
    public const VALIDED = 1;
    public const TYPES = [
        self::UN_VALIDED => 'second_hand.un_valided',
        self::VALIDED => 'second_hand.valided',
    ];

    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private ProjectDirService $projectDirService
    ) {
    }
    public function fromEntity(?SecondHand $secondHand, bool $novelty = false): SecondHandDto
    {
        $secondHandDto = new SecondHandDto();
        if ($secondHand) {
            $secondHandDto->id = $secondHand->getId();
            $secondHandDto->name = $secondHand->getName();
            $secondHandDto->content = $secondHand->getContent();
            $secondHandDto->images = $this->getImages($secondHand->getImages());
            $secondHandDto->user = $this->userDtoTransformer->fromEntity($secondHand->getUser());
            $secondHandDto->price = (new Currency($secondHand->getPrice()))->__toString();
            $secondHandDto->category = $secondHand->getCategory()->getName();
            $secondHandDto->createdAt = $secondHand->getCreatedAt()->format('d/m/y');
            $secondHandDto->valid = null !== $secondHand->getValidedAt();
            $secondHandDto->disabled = $secondHand->isDisabled();
            $secondHandDto->status = $this->getStatus($secondHand);
            $secondHandDto->novelty = $novelty;
        }
        $secondHandDto->pathName = $this->getPath($secondHandDto->images);

        return $secondHandDto;
    }

    public function fromEntities(Paginator|Collection|array $secondHandEntities, array $secondHandViewedIds = []): array
    {
        $secondHands = [];
        foreach ($secondHandEntities as $secondHandEntity) {
            $secondHands[] = $this->fromEntity($secondHandEntity, in_array($secondHandEntity->getId(), $secondHandViewedIds));
        }

        return $secondHands;
    }

    public function fromEntitiesByValidate(Paginator|Collection|array $secondHandEntities): array
    {
        $secondHands = [
            self::UN_VALIDED => [],
            self::VALIDED => [],
        ];
        foreach ($secondHandEntities as $secondHandEntity) {
            $type = ($secondHandEntity->isValid) ? self::VALIDED : self::UN_VALIDED;
            $secondHands[$type][] = $this->fromEntity($secondHandEntity);
        }

        return $secondHands;
    }

    private function GetStatus(SecondHand $secondHand): string
    {
        if ($secondHand->isDisabled()) {
            return 'Désactivée';
        }
        return (null !== $secondHand->getValidedAt()) ? 'Validée' : 'Non Validée';
    }

    private function getPath(array $images): string
    {
        if (!empty($images)) {
            return $images[array_key_first($images)]['path'];
        }

        return $this->projectDirService->dir('', 'camera.jpg');
    }

    private function getImages(Collection $imageEntities): array
    {
        $images = [];
        /** @var SecondHandImage $image */
        foreach ($imageEntities as $image) {
            $images[$image->getId()] = [
                'path' => $this->projectDirService->dir('', 'second_hands', $image->getFilename()),
                'filename' => $image->getFilename()
            ];
        }

        return $images;
    }
}
