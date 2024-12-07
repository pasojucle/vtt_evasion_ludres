<?php

declare(strict_types=1);

namespace App\UseCase\Skill;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\DtoTransformer\UserSkillDtoTransformer;
use App\Entity\User;
use App\Entity\UserSkill;
use App\Repository\UserSkillRepository;
use App\UseCase\User\GetMembersFiltered;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class GetUsersSkills
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UserSkillRepository $userSkillRepository,
        private readonly GetMembersFiltered $getMembersFiltered,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly UserSkillDtoTransformer $userSkillDtoTransformer,
    ) {
    }

    public function export(): Response
    {
        $usersSkills = $this->getUsersSkills();
        dump($usersSkills);

        $content = $this->getExportContent($usersSkills);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_evaluations.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function getUsers(array $filters): array
    {
        $users = [];
        /** @var User $user */
        foreach ($this->getMembersFiltered->getQuery($filters)->getQuery()->getResult() as $user) {
            $users[] = $user->getId();
        }

        return $users;
    }

    private function getUsersSkills(): array
    {
        $session = $this->requestStack->getSession();
        $filters = $session->get($this->getMembersFiltered->filterName);
        $users = $this->getUsers($filters);


        return $this->userSkillRepository->findByUsers($users);
    }

    private function getExportContent(array $usersSkills): string
    {
        $content = [];
        $row = ['date d\évaluation', 'compétence', 'évaluation'];
        $content[] = implode(',', $row);

        $prevUserId = null;
        /** @var UserSkill $userSkill */
        foreach ($usersSkills as  $userSkill) {
            if (!$userSkill->getEvaluateAt()) {
                continue;
            }
            $userId = $userSkill->getUser()->getId();
            if ($prevUserId !== $userId) {
                $prevUserId = $userId;
                $userDto = $this->userDtoTransformer->fromEntity($userSkill->getUser());
                $row = [$userDto->licenceNumber, $userDto->member->fullName];
                $content[] = implode('-', $row);
            }
            $userSkillDto = $this->userSkillDtoTransformer->fromEntity($userSkill);
            $row = [$userSkillDto->evaluateAt, sprintf('"%s"', $userSkillDto->skill['content']), $userSkillDto->evaluation['value']];
            $content[] = implode(',', $row);
        }

        return implode(PHP_EOL, $content);
    }
}
