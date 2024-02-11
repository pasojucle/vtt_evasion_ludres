<?php

namespace App\Command;

use App\Entity\Cluster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'database:short:clusters',
    description: 'Short clusters by levels order',
)]
class ShortClustersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clusters = $this->entityManager->getRepository(Cluster::class)->findAll();
        $clustersByBikeRide = [];
        foreach ($clusters as $cluster) {
            $clustersByBikeRide[$cluster->getBikeRide()->getId()][] = $cluster;
        }
        foreach($clustersByBikeRide as $clusters) {
            foreach($clusters as $key => $cluster) {
                $position = $this->getPosition($key, $cluster);
                $cluster->setPosition($position);
            } 
        }

        $this->entityManager->flush();

        $io->success('Clusters have been ordered successful by levels with success.');

        return Command::SUCCESS;
    }

    private function getPosition(int $key, Cluster $cluster): int
    {
        if ('ROLE_FRAME' === $cluster->getRole()) {
            return 0;
        }

        $position = $key;
        $bikeRideType = $cluster->getBikeRide()->getBikeRideType();
        if ($bikeRideType->isUseLevels() && $cluster->getLevel()) {
            $position = $cluster->getLevel()?->getOrderBy();
        }

        if (!$bikeRideType->isUseLevels() && $bikeRideType->getClusters()) {
            $position = array_search($cluster->getTitle(),  $bikeRideType->getClusters());
        }

        if ($cluster->getBikeRide()->getBikeRideType()->isNeedFramers()) {
            ++$position;
        }

        return $position;
    }
}
