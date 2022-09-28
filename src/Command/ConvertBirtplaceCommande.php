<?php

namespace App\Command;

use App\Entity\Commune;
use App\Entity\Identity;
use App\Repository\IdentityRepository;
use App\Service\GeoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'geo:convert:birthplace',
    description: 'Commande pour convertir les lieux de naissance en commune',
)]
class ConvertBirtplaceCommande extends Command
{

    private ProgressBar $progressBar;
    private OutputInterface $output;
    private SymfonyStyle $ssio;

    public function __construct(private GeoService $geoService, private EntityManagerInterface $entityManager, private IdentityRepository $identityRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Convertir les lieux de naissance en commune')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->ssio = new SymfonyStyle($input, $this->output);

        $identities = $this->identityRepository->findAllBirthplaceToConvert();
        $count = count($identities);
        $this->progressBar = new ProgressBar($this->output, $count);
        $this->progressBar->start();

        $communesFound = [];
        $communesnotFound = [];
        while(!empty($identities)) {
            $identitiesToCReload = [];
            foreach($identities as $identity) {
                $communeCode = $this->searchCommune($identity);
                if (false === $communeCode) {
                    $identitiesToCReload[] = $identity;
                } elseif (!is_null($communeCode)) {
                    $commune = $this->entityManager->getRepository(Commune::class)->find($communeCode);
                    $communesFound[] = $commune;
                    $identity->setBirthCommune($commune);
                } else {
                    $communesnotFound[] = $identity->getBirthplace();
                }
            }
            $identities = $identitiesToCReload;
        }

        $this->entityManager->flush();

        $this->progressBar->finish();

        $this->ssio->writeln(count($communesFound).' / '.$count);
        if (!empty($communesnotFound)) {
            
            $this->ssio->writeln('Communes non trouvées');
            foreach ($communesnotFound as $commune) {
                $this->ssio->writeln($commune);
            }
        }
        
        $this->ssio->success('Conversion terminée');

        return Command::SUCCESS;
    }

    private function searchCommune(Identity $identity): string|false|null|int
    {
        $birthplace = $identity->getBirthplace();

        $commune = array_search(strtolower($birthplace), [
            '54395' => 'nancy',
            '97411' => 'st denis de la réunion',
            '10387' => 'troyes'
        ]);
     
        if (false !== $commune) {
            return $commune;
        }
        
        $communes = $this->geoService->getCommunesByName($birthplace);

        if (is_array($communes)) {
            $this->progressBar->advance();
            return match(count($communes)) {
                0 => null,
                1 => $communes[0]['code'],
                default => $this->searchByDepartment($communes, $identity->getBirthDepartment())
            };
        }

        return false;

    }

    private function searchByDepartment(array $communes, ?string $department): ?string
    {
        if (!is_null($department)) {
            foreach ($communes as $commune) {
                if (strtolower($commune['departement']['nom']) === strtolower($department)) {
                    return $commune['code'];
                }
            }
        }

        return null;
    }
}
