<?php

namespace App\Command;

use App\Entity\Address;
use App\Entity\Commune;
use App\Entity\Identity;
use App\Service\GeoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'geo:convert:town',
    description: 'Commande pour convertir les villes en commune',
)]
class ConvertAddressTownCommande extends Command
{
    private ProgressBar $progressBar;
    private OutputInterface $output;
    private SymfonyStyle $ssio;

    public function __construct(private GeoService $geoService, private EntityManagerInterface $entityManager)
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

        $addresses = $this->entityManager->getRepository(Address::class)->findAll();
        $count = count($addresses);
        $this->progressBar = new ProgressBar($this->output, $count);
        $this->progressBar->start();

        $communesFound = [];
        $communesnotFound = [];
        while (!empty($addresses)) {
            $addressesToReload = [];
            foreach ($addresses as $address) {
                $communeCode = $this->searchCommune($address);
                if (false === $communeCode) {
                    $addressesToReload[] = $address;
                } elseif (!is_null($communeCode)) {
                    $commune = $this->entityManager->getRepository(Commune::class)->find($communeCode);
                    $communesFound[] = $commune;
                    $address->setCommune($commune);
                } else {
                    $communesnotFound[] = $address->getTown();
                }
            }
            $addresses = $addressesToReload;
        }

        $this->entityManager->flush();

        $this->progressBar->finish();

        $this->ssio->writeln(count($communesFound) . ' / ' . $count);
        if (!empty($communesnotFound)) {
            $this->ssio->writeln('Communes non trouvées');
            foreach ($communesnotFound as $commune) {
                $this->ssio->writeln($commune);
            }
        }
        
        $this->ssio->success('Conversion terminée');

        return Command::SUCCESS;
    }

    private function searchCommune(Address $address): string|false|null
    {
        $town = $address->getTown();
        $communes = null;

        if (empty($town)) {
            return null;
        }

        $town = preg_replace('#\s#', '-', strtolower($town));
        $town = preg_replace(['#^st-#', '#-(st)-#'], ['saint-', '-saint-'], strtolower($town));
        $communes = $this->geoService->getCommunesByName($town);
        
        if (is_array($communes)) {
            $this->progressBar->advance();
            return match (count($communes)) {
                0 => null,
                1 => $communes[0]['code'],
                default => $this->searchByPostalCode($communes, $address->getPostalCode())
            };
        }

        return false;
    }

    private function searchByPostalCode(array $communes, ?string $postalCode): ?string
    {
        if (!is_null($postalCode)) {
            foreach ($communes as $commune) {
                if (in_array($postalCode, $commune['codesPostaux'])) {
                    return $commune['code'];
                }
            }
        }

        return null;
    }
}
