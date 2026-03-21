<?php

declare(strict_types=1);

namespace App\UseCase\Skill;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\DtoTransformer\UserSkillDtoTransformer;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Repository\MemberSkillRepository;
use App\UseCase\User\GetMembersFiltered;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class GetUsersSkills
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MemberSkillRepository $memberSkillRepository,
        private readonly GetMembersFiltered $getMembersFiltered,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly UserSkillDtoTransformer $userSkillDtoTransformer,
    ) {
    }

    public function export(): Response
    {
        $usersSkills = $this->getUsersSkills();

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
        /** @var Member $member */
        foreach ($this->getMembersFiltered->getQuery($filters)->getQuery()->getResult() as $member) {
            $users[] = $member->getId();
        }

        return $users;
    }

    private function getUsersSkills(): array
    {
        $session = $this->requestStack->getSession();
        $filters = $session->get($this->getMembersFiltered->filterName);
        $users = $this->getUsers($filters);


        return $this->memberSkillRepository->findByUsers($users);
    }

    private function getExportContent(array $membersSkills): string
    {
        $content = [];
        $row = ['date d\'évaluation', 'compétence', 'évaluation'];
        $content[] = implode(',', $row);

        $prevUserId = null;
        /** @var MemberSkill $memberSkill */
        foreach ($membersSkills as $memberSkill) {
            if (!$memberSkill->getEvaluateAt()) {
                continue;
            }
            $userId = $memberSkill->getMember()->getId();
            if ($prevUserId !== $userId) {
                $prevUserId = $userId;
                $userDto = $this->userDtoTransformer->fromEntity($memberSkill->getMember());
                $content[] = '';
                $content[] = sprintf('%s - %s', $userDto->licenceNumber, $userDto->member->fullName);
            }
            $userSkillDto = $this->userSkillDtoTransformer->fromEntity($memberSkill);
            $row = [$userSkillDto->evaluateAt, sprintf('"%s"', strip_tags($userSkillDto->content)), $userSkillDto->evaluation['value']];
            $content[] = implode(',', $row);
        }

        return implode(PHP_EOL, $content);
    }
}
