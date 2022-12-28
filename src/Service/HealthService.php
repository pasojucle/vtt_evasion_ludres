<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\HealthQuestion;
use App\Entity\Licence;
use App\Repository\RegistrationStepRepository;
use Doctrine\Common\Collections\ArrayCollection;

class HealthService
{
    public function __construct(private RegistrationStepRepository $registrationStepRepository)
    {
    }
    public function getHealthQuestionsCount(int $category): int
    {
        $healthQuestionsCount = 0;
        $registrationSteps = $this->registrationStepRepository->findByGroupAndCategory(7, $category);
        foreach ($registrationSteps as $registrationStep) {
            preg_match_all('#{{ question_\d+ }}#', $registrationStep->getContent(), $matches, PREG_SET_ORDER);
            $healthQuestionsCount += count($matches);
        }

        return $healthQuestionsCount;
    }

    public function createHealthQuestions(int $formQuestionCount): ArrayCollection
    {
        /** @var ArrayCollection<int, HealthQuestion> $healthQuestions */
        $healthQuestions = new ArrayCollection();
        foreach (range(0, $formQuestionCount - 1) as $number) {
            $healthQuestion = new HealthQuestion();
            $healthQuestion->setField($number);
            $healthQuestions[] = $healthQuestion;
        }

        return $healthQuestions;
    }
}
