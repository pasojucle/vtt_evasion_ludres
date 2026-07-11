<?php

declare(strict_types=1);

namespace App\State\Survey\Processor;

use App\Dto\Payload\SurveyToggleDto;
use App\Dto\State\ComponentProcessorResult;
use App\Mapper\Survey\SurveyStatusMapper;
use App\Service\CsrfTokenService;
use App\Service\DisableService;
use App\State\Interface\ComponentProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SurveyToggleProcessor implements ComponentProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
        private DisableService $disableService,
        private SurveyStatusMapper $surveyStatusMapper,
    ) {
    }
    
    /**
     * @implements ComponentProcessorInterface<SurveyToggleDto>
     */
    public function process(object $entity): ComponentProcessorResult
    {
        $tokenId = $this->csrfTokenService->getTokenId($entity->survey);
        $csrfToken = new CsrfToken($tokenId, $entity->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new ComponentProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        $survey = $entity->survey;
        $this->disableService->toggle($survey);

        $this->entityManager->flush();

        return new ComponentProcessorResult(
            success: true,
            messageKey: $survey->isDisabled() 
                ? 'survey.flash.success.disabled'
                : 'survey.flash.success.enabled',
            flashType: 'success',
            component: $this->surveyStatusMapper->mapToView($survey, $tokenId)
        );
    }
}
