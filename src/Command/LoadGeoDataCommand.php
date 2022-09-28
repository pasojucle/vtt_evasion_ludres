<?php

namespace App\Command;

use App\Entity\Commune;
use App\Entity\Department;
use App\Repository\CommuneRepository;
use App\Repository\DepartmentRepository;
use App\Service\GeoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'geo:load:data',
    description: 'Commande pour charger les départements et communes en bd',
)]
class LoadGeoDataCommand extends Command
{
    private array $departmentsToReload = [];
    private array $departments;
    private array $departmentIds;
    private ProgressBar $progressBar;
    private OutputInterface $output;
    private SymfonyStyle $ssio;

    public function __construct(
        private GeoService $geoService,
        private EntityManagerInterface $entityManager,
        private CommuneRepository $communeRepository,
        private DepartmentRepository $departmentRepository
    ) {
        parent::__construct();
        $this->departmentIds = array_merge(range(1, 19), range(21, 95), range(971, 974), ['2A', '2B', 976]);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Charger départements et communes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->ssio = new SymfonyStyle($input, $this->output);

        $this->communeRepository->deleteAll();
        $this->departmentRepository->deleteAll();
        $this->setDepartments($this->departmentIds);
        $this->setCommunes($this->departmentIds);
        
        $this->ssio->success('Les départments et communes ont bien été charchés en base de données');

        return Command::SUCCESS;
    }

    private function setDepartments(array $departmentIds)
    {
        $this->progressBar = new ProgressBar($this->output, count($this->departmentIds));
        $this->output->writeln('Chargement des departements');
        $this->progressBar->start();
        while (!empty($departmentIds)) {
            $this->departmentsToReload = [];
            foreach ($departmentIds as $departmentId) {
                $departmentId = (strlen((string) $departmentId) === 1) ? '0' . $departmentId : $departmentId;
                if (!$this->addDepartment($departmentId)) {
                    $this->departmentsToReload[] = $departmentId;
                } else {
                    $this->progressBar->advance();
                }
            }
            $departmentIds = $this->departmentsToReload;
        }

        $this->entityManager->flush();
        $this->progressBar->finish();
        $this->ssio->writeln('');
    }

    private function addDepartment(string|int $departmentId): bool
    {
        $data = $this->geoService->getDepartmentByCode($departmentId);

        if ($data) {
            $department = new Department();
            $department->setId($data['code'])
                ->setName($data['nom']);
            $this->entityManager->persist($department);
            $this->departments[$data['code']] = $department;
            
            return true;
        }
        return false;
    }

    private function setCommunes(array $departmentIds)
    {
        $this->progressBar = new ProgressBar($this->output, count($this->departmentIds));
        $this->output->writeln('Chargement des communess');
        $this->progressBar->start();
        while (!empty($departmentIds)) {
            $this->departmentsToReload = [];
            foreach ($departmentIds as $departmentId) {
                $departmentId = (strlen((string) $departmentId) === 1) ? '0' . $departmentId : $departmentId;
                if (!$this->addCommunesByDepartment($departmentId)) {
                    $this->departmentsToReload[] = $departmentId;
                } else {
                    $this->progressBar->advance();
                }
            }
            $departmentIds = $this->departmentsToReload;
        }
        $this->progressBar->finish();
    }

    private function addCommunesByDepartment(string|int $departmentId): bool
    {
        $dataCommunes = $this->geoService->getCommunesByDepartment($departmentId);

        if ($dataCommunes) {
            $this->addCommunes($dataCommunes);

            return true;
        }
        
        return false;
    }

    private function addCommunes(array $dataCommunes): void
    {
        if (!empty($dataCommunes)) {
            foreach ($dataCommunes as $data) {
                if (!empty($data['code'])) {
                    $commune = new Commune();
                    $commune->setId($data['code'])
                        ->setName($data['nom'])
                        ->setDepartment($this->departments[$data['codeDepartement']]);
                    $this->entityManager->persist($commune);
                }
            }
        }
        $this->entityManager->flush();
    }
}
