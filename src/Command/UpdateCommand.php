<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ParameterRepository;
use App\Service\CommandLineService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'website:update',
    description: 'update website',
)]
class UpdateCommand extends Command
{
    public function __construct(
        private ParameterRepository $parameterRepository,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private CommandLineService $commandLineService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $maintenance = $this->parameterRepository->findOneByName('MAINTENANCE_MODE');
        $io->writeln('Mise du site en maintenance');
        $maintenance->setValue('1');
        $this->entityManager->flush();

        if ('patrick' !== getenv('USER')) {
            $io->writeln('git reset --hard');
            $output = shell_exec('git reset --hard');
            $io->writeln($output);

            $io->writeln('git pull');
            $output = shell_exec('git pull');
            $io->writeln($output);
        }

        if ('Déjà à jour.' !== $output) {
            $io->writeln('version php :' . $this->commandLineService->getPhpVersion());

            $cmdComposer = ('patrick' === getenv('USER'))
                ? 'composer install'
                : $this->commandLineService->getBinay() . ' ../composer.phar install';

            $io->writeln($cmdComposer);
            $output = shell_exec($cmdComposer);
            $io->writeln($output);

            $commands = [
                ['cmd' => 'doctrine:migration:migrate -n', 'onlyOne' => false],
                ['cmd' => 'geo:load:data', 'onlyOne' => true],
                ['cmd' => 'geo:convert:birthplace', 'onlyOne' => true],
                ['cmd' => 'geo:convert:town', 'onlyOne' => true],
            ];

            foreach ($commands as $command) {
                $filename = $this->parameterBag->get('cmd_directory_path') . str_replace([':', ' '], '', $command['cmd']);
                if (!($command['onlyOne'] && file_exists($filename))) {
                    $cmd = $this->commandLineService->getBinConsole() . ' ' . $command['cmd'];
                    $io->writeln($cmd);
                    $output = shell_exec($cmd);
                    $io->writeln($output);
                }
                if ($command['onlyOne']) {
                    file_put_contents($filename, '');
                }
            }
        }

        $io->writeln('Suppression du mode maintenance');
        $maintenance->setValue('0');
        $this->entityManager->flush();

        $io->success('Mise à jour effectuée.');

        return Command::SUCCESS;
    }
}
