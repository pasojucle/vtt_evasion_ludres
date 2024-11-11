<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'database:clear:userskill',
    description: 'Delete duplicates userSkill',
)]
class ClearUserSkillCommand extends Command
{
    private array $duplicatesByCompountKey = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->getduplicatesByCompountKey();
        $this->clean();


        $io->success('Delete duplicates userSkill succefully');

        return Command::SUCCESS;
    }

    private function getUserSkills(): array
    {
        $query = 'SELECT * FROM `user_skill`';
        $stmt = $this->entityManager->getConnection()->executeQuery($query);

        return $stmt->fetchAllAssociative();
    }

    private function getduplicatesByCompountKey(): void
    {
        $userSkills = $this->getUserSkills();
        
        foreach ($userSkills as $userSkill) {
            $compountKey = sprintf('%s-%s', $userSkill['user_id'], $userSkill['skill_id']);
            $this->addDuplicate($compountKey, $userSkill['id']);
        }
    }

    private function addDuplicate(string $compountKey, int $userSkillId): void
    {
        if (array_key_exists($compountKey, $this->duplicatesByCompountKey)) {
            $this->duplicatesByCompountKey[$compountKey][] = $userSkillId;
            return;
        }
        $this->duplicatesByCompountKey[$compountKey] = [];
    }

    private function clean(): void
    {
        $connection = $this->entityManager->getConnection();
        foreach ($this->duplicatesByCompountKey as $duplicates) {
            foreach ($duplicates as $userSkilId) {
                $stmt = $connection->prepare('DELETE FROM `user_skill` WHERE `id` = ?');
                $stmt->bindValue(1, $userSkilId);
                $stmt->executeQuery();
            }
        }
    }
}
