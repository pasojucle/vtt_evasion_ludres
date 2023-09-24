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
    private array $departmentEntities = [];
    private array $communeEntities = [];
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

        $this->getDepartmentEntities();
        $this->getCommuneEntities();
        $this->setDepartments($this->departmentIds);
        $this->setCommunes($this->departmentIds);
        
        $this->ssio->success('Les départments et communes ont bien été charchés en base de données');

        return Command::SUCCESS;
    }

    private function getDepartmentEntities(): void
    {
        foreach ($this->departmentRepository->findAll() as $departmentEntity) {
            $this->departmentEntities[$departmentEntity->getId()] = $departmentEntity;
        }
    }

    private function getCommuneEntities(): void
    {
        foreach ($this->communeRepository->findAll() as $communeEntity) {
            $this->communeEntities[$communeEntity->getId()] = $communeEntity;
        }
    }

    private function setDepartments(array $departmentIds)
    {
        $this->progressBar = new ProgressBar($this->output, count($this->departmentIds));
        $this->output->writeln('Chargement des departements');
        $this->progressBar->start();
        while (!empty($departmentIds)) {
            $departmentsToReload = [];
            foreach ($departmentIds as $departmentId) {
                $departmentId = (strlen((string) $departmentId) === 1) ? '0' . $departmentId : $departmentId;
                if (!$this->addDepartment($departmentId)) {
                    $departmentsToReload[] = $departmentId;
                } else {
                    $this->progressBar->advance();
                }
            }
            $departmentIds = $departmentsToReload;
            $this->output->writeln(sprintf('Departements à rechercher : %s', implode(', ', $departmentIds)));
        }

        $this->entityManager->flush();
        $this->progressBar->finish();
        $this->ssio->writeln('');
    }

    private function addDepartment(string|int $departmentId): bool
    {
        $data = $this->geoService->getDepartmentByCode($departmentId);

        if ($data) {
            if (!array_key_exists($data['code'], $this->departmentEntities)) {
                $department = new Department();
                $department->setId($data['code']);
            } else {
                $department = $this->departmentEntities[$data['code']];
            }
            $department->setName($data['nom']);
            $this->entityManager->persist($department);
            $this->departments[$data['code']] = $department;
            
            return true;
        }
        return false;
    }

    private function setCommunes(array $departmentIds)
    {
        $this->progressBar = new ProgressBar($this->output, count($this->departmentIds));
        $this->output->writeln('Chargement des communes');
        $this->progressBar->start();

        while (!empty($departmentIds)) {
            $departmentsToReload = [];
            foreach ($departmentIds as $departmentId) {
                $departmentId = (strlen((string) $departmentId) === 1) ? '0' . $departmentId : $departmentId;
                if (!$this->addCommunesByDepartment($departmentId)) {
                    $departmentsToReload[] = $departmentId;
                } else {
                    $this->progressBar->advance();
                }
            }
            $departmentIds = $departmentsToReload;
            $this->output->writeln(sprintf('Communes à rechercher : %s', implode(', ', $departmentIds)));
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
        foreach ($dataCommunes as $data) {
            if (!empty($data['code'])) {
                if (!array_key_exists($data['code'], $this->communeEntities)) {
                    $commune = new Commune();
                    $commune->setId($data['code']);
                } else {
                    $commune = $this->communeEntities[$data['code']];
                }
                
                $commune
                    ->setName($data['nom'])
                    ->setDepartment($this->departments[$data['codeDepartement']])
                    ->setPostalCode(array_shift($data['codesPostaux']));
                $this->entityManager->persist($commune);
            }
        }

        $this->entityManager->flush();
    }
}
