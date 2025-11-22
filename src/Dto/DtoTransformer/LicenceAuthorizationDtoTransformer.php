<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceAuthorizationDto;
use App\Entity\LicenceAuthorization;
use Doctrine\Common\Collections\Collection;

class LicenceAuthorizationDtoTransformer
{
    public function fromEntity(?LicenceAuthorization $licenceAuthorization): LicenceAuthorizationDto
    {
        $licenceAuthorizationDto = new LicenceAuthorizationDto();
        if ($licenceAuthorization) {
            $licenceAuthorizationDto->id = $licenceAuthorization->getId();
            $licenceAuthorizationDto->title = $licenceAuthorization->getAuthorization()->getTitle();
            $licenceAuthorizationDto->value = $licenceAuthorization->getValue();
            $licenceAuthorizationDto->toString = ($licenceAuthorization->getValue()) ? 'autorise' : 'n\'autorise pas';
            $licenceAuthorizationDto->toHtml = $this->toHtml($licenceAuthorization);
            $licenceAuthorizationDto->content = $licenceAuthorization->getAuthorization()->getContent();
        }

        return $licenceAuthorizationDto;
    }

    public function fromEntities(Collection|array $licenceAuthorizationEntities): array
    {
        $licencesAuthorizations = [];
        foreach ($licenceAuthorizationEntities as $licenceAuthorizationEntity) {
            $licenceAuthorization = $this->fromEntity($licenceAuthorizationEntity);
            $licencesAuthorizations[$licenceAuthorizationEntity->getAuthorization()->getId()] = $licenceAuthorization;
        }

        return $licencesAuthorizations;
    }

    private function toHtml(LicenceAuthorization $licenceAuthorization): array
    {
        $authorization = $licenceAuthorization->getAuthorization();
        return ($licenceAuthorization->getValue())
            ? [
                'color' => 'success',
                'icon' => $authorization->getAuthorizationIcon(),
                'message' => $authorization->getAuthorizationMessage(),
            ]
            : [
                'color' => 'danger',
                'icon' => $authorization->getRejectionIcon(),
                'message' => $authorization->getRejectionMessage(),
            ];
    }
}
