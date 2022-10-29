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
    private bool $isMaintenance;
    private SymfonyStyle $ssio;

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
        $this->ssio = new SymfonyStyle($input, $output);

        $maintenance = $this->parameterRepository->findOneByName('MAINTENANCE_MODE');
        $this->isMaintenance = (bool) $maintenance->getValue();

        $this->setMaintenance('1');

        if ('patrick' !== getenv('USER')) {
            $this->ssio->writeln('git reset --hard');
            $output = shell_exec('git reset --hard');
            $this->ssio->writeln($output);

            $this->ssio->writeln('git pull');
            $output = shell_exec('git pull');
            $this->ssio->writeln($output);
        }

        if ('Déjà à jour.' !== $output) {
            $this->ssio->writeln('version php :' . $this->commandLineService->getPhpVersion());

            $cmdComposer = ('patrick' === getenv('USER'))
                ? 'composer install'
                : $this->commandLineService->getBinay() . ' ../composer.phar install';

            $this->ssio->writeln($cmdComposer);
            $output = shell_exec($cmdComposer);
            $this->ssio->writeln($output);

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
                    $this->ssio->writeln($cmd);
                    $output = shell_exec($cmd);
                    $this->ssio->writeln($output);
                }
                if ($command['onlyOne']) {
                    file_put_contents($filename, '');
                }
            }
        }

        $this->setMaintenance('0');

        $this->ssio->success('Mise à jour effectuée.');

        return Command::SUCCESS;
    }

     private function setMaintenance(string $value): void
     {
        if (false === $this->isMaintenance) {
            $messsage = ('0' === $value) ? 'Suppression du mode maintenance' : 'Mise du site en maintenance';
            $this->entityManager->flush();
            $this->ssio->writeln($messsage);
        }
     }
}
