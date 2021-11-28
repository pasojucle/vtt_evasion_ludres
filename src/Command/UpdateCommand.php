<?php

namespace App\Command;

use App\Repository\ParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    protected static $defaultName = 'website:update';
    protected static $defaultDescription = 'update website';

    private ParameterRepository $parameterRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(
        ParameterRepository $parameterRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
  
        $maintenance = $this->parameterRepository->findOneByName('MAINTENANCE_MODE');
        $io->writeln('Mise du site en maintenance');
        $maintenance->setValue(1);
        $this->entityManager->flush();

        $io->writeln('git reset --hard');
        $output = shell_exec('git reset --hard');
        $io->writeln($output);

        $io->writeln('git pull');
        $output = shell_exec('git pull');
        $io->writeln($output);
        
        if ('Déjà à jour.' !== $output) {
            $cmdComposer = 'composer install';
            $cmdMigration = 'php bin/console doctrine:migration:migrate -n';
            if (getcwd() !== '/home/patrick/Sites/vtt_evasion_ludres') {
                $cmdComposer = '/usr/bin/php8.0-cli composer.phar install';
                $cmdMigration = '/usr/bin/php8.0-cli  bin/console doctrine:migration:migrate -n';
            }

            $io->writeln('composer install');
            $output = shell_exec($cmdComposer);
            $io->writeln($output);

            $io->writeln('doctrine:migration:migrate');
            $output = shell_exec($cmdMigration);
            $io->writeln($output);
        }
        
        $io->writeln('Suppression du mode maintenance');
        $maintenance->setValue(0);
        $this->entityManager->flush();

        $io->success('Mise à jour effectuée.');

        return Command::SUCCESS;
    }
}
