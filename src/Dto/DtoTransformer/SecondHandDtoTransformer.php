<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SecondHandDto;
use App\Entity\SecondHand;
use App\Model\Currency;
use App\Service\ProjectDirService;
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
    public function fromEntity(?SecondHand $secondHand): SecondHandDto
    {
        $secondHandDto = new SecondHandDto();
        if ($secondHand) {
            $secondHandDto->id = $secondHand->getId();
            $secondHandDto->name = $secondHand->getName();
            $secondHandDto->content = $secondHand->getContent();
            $secondHandDto->filename = $secondHand->getFilename();
            $secondHandDto->user = $this->userDtoTransformer->fromEntity($secondHand->getUser());
            $secondHandDto->price = (new Currency($secondHand->getPrice()))->toString();
            $secondHandDto->category = $secondHand->getCategory()->getName();
            $secondHandDto->pathName = $this->projectDirService->dir('', 'second_hands', $secondHandDto->filename);
            $secondHandDto->createdAt = $secondHand->getCreatedAt()->format('d-m-y');
            $secondHandDto->valid = $secondHand->isValid();
            $secondHandDto->validToString = $this->isValidToString($secondHand->isValid());
        }

        return $secondHandDto;
    }

    public function fromEntities(Paginator|Collection|array $secondHandEntities): array
    {
        $secondHands = [];
        foreach ($secondHandEntities as $secondHandEntity) {
            $secondHands[] = $this->fromEntity($secondHandEntity);
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

    private function isValidToString(bool $isValid): string
    {
        return ($isValid) ? 'Validé' : 'Non Validé';
    }
}
