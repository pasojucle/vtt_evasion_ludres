<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SurveyDto;
use App\Entity\History;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\User;
use App\UseCase\Survey\GetResponsesByUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;

class SurveyDtoTransformer
{
    public function __construct(
        private readonly GetResponsesByUser $getResponsesByUser,
        private readonly Security $security,
    ) {
    }

    public function fromEntity(Survey $survey, ?array $histories = null): SurveyDto
    {
        $surveyDto = new surveyDto();
        $surveyDto->id = $survey->getId();
        $surveyDto->entity = $survey;
        $surveyDto->title = $survey->getTitle();
        $surveyDto->content = $survey->getContent();
        $surveyDto->issues = $this->getIssues($survey->getSurveyIssues());
        if (!empty($histories)) {
            $this->formatHistories($histories, $surveyDto);
        }
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user) {
            $surveyDto->responses = $this->getResponsesByUser->execute($survey, $user);
        }
        
        return $surveyDto;
    }

    public function fromEntities(Paginator|Collection|array $surveyEntities): array
    {
        $surveys = [];
        foreach ($surveyEntities as $surveyEntity) {
            $surveys[] = $this->fromEntity($surveyEntity);
        }

        return $surveys;
    }

    private function getIssues(Collection $issues): array
    {
        $issuesById = [];
        /** @var SurveyIssue $issue */
        foreach ($issues as $issue) {
            $issuesById[$issue->getId()] = $issue->getContent();
        }

        return $issuesById;
    }

    private function formatHistories(array $histories, SurveyDto &$surveyDto): void
    {
        $reflectionClass = new ReflectionClass($surveyDto);
        /** @var History $history */
        foreach ($histories as $history) {
            if ('Survey' === $history->getEntity()) {
                foreach ($history->getValue() as $property => $value) {
                    if ('content' === $property) {
                        $surveyDto->$property = $this->getDecoratedChangesFromHtml($value);
                    } elseif ($reflectionClass->hasProperty($property) && is_string($surveyDto->$property)) {
                        $surveyDto->$property = $this->getDecoratedChangesFromText($value);
                    }
                }
            }
            if ('SurveyIssue' === $history->getEntity()) {
                $value = $history->getValue();
                $decorateChanges = $this->getDecoratedChangesFromText($value['content']);
                $surveyDto->issues[$history->getEntityId()] = $decorateChanges;
            }
        }
    }

    private function getDecoratedChangesFromHtml(array $value): string
    {
        preg_match_all('#<p>(.*?)</p>#', $value[0], $paragraphesOld);
        preg_match_all('#<p>(.*?)</p>#', $value[1], $paragraphesNew);
        $paragraphChanged = array_diff($paragraphesNew[1], $paragraphesOld[1]);

        $paragraphes = '';
        foreach ($paragraphesNew[1] as $key => $paragraph) {
            if (array_key_exists($key, $paragraphChanged)) {
                $paragraph = sprintf('<ins style="background-color:#ccffcc">%s</ins>', $paragraph);
            }
            $paragraphes .= sprintf('<p>%s</p>', $paragraph);
        }

        return $paragraphes;
    }

    private function getDecoratedChangesFromText(array $value): string
    {
        return sprintf('<ins style="background-color:#ccffcc">%s</ins>', $value[1]);
    }
}
