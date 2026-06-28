<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Background;
use App\Entity\BikeRide;
use App\Entity\Content;
use App\Entity\Identity;
use App\Entity\Interface\UploadableInterface;
use App\Entity\Product;
use App\Entity\RegistrationStep;
use App\Entity\SecondHandImage;
use App\Service\FileLocation\FileLocationResolver;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'media:migrate-location',
    description: 'Déplace les images d’occasion de public/upload vers le nouveau dossier sécurisé data/',
)]
class MigrateLocationFilesCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private FileLocationResolver $resolver,
        private FileService $fileService,
        private Filesystem $filesystem,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $migrations = [
            ['name' => 'annonces d\’occasion', 'oldDir' => ['uploads', 'second_hands'], 'newDir' => 'second_hand', 'entityClass' => SecondHandImage::class],
            ['name' => 'boutique', 'oldDir' => ['uploads', 'products'], 'newDir' => 'product', 'entityClass' => Product::class],
            ['name' => 'image de fond', 'oldDir' => ['uploads', 'images', 'background'], 'newDir' => 'background', 'entityClass' => Background::class],
            ['name' => 'bikeRide', 'oldDir' => ['uploads', 'images'], 'newDir' => 'bike-ride', 'entityClass' => BikeRide::class],
            ['name' => 'contenu', 'oldDir' => ['uploads', 'images'], 'newDir' => 'content', 'entityClass' => Content::class],
            ['name' => 'identité', 'oldDir' => ['uploads', 'images'], 'newDir' => 'identity', 'entityClass' => Identity::class],
            ['name' => 'étape d\'inscription', 'oldDir' => ['uploads', 'images'], 'newDir' => 'registration_step', 'entityClass' => RegistrationStep::class],
        ];
        foreach($migrations as $migration) {
            if (!$this->migrate($migration, $io)) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    private function migrate(array $migration, SymfonyStyle $io): bool
    {
        $io->title(sprintf('Migration des fichiers %s (Filesystem)', $migration['name']));

        if (!is_subclass_of($migration['entityClass'], UploadableInterface::class)) {
            $io->error(sprintf('La classe %s n\'implémente pas UploadableInterface.', $migration['entityClass']));
            return false;
        }

        $oldDir = $this->fileService->join($this->projectDir, 'public', ...$migration['oldDir']);
        $location = $this->resolver->resolveDirectory($migration['newDir']);
        if (!$location) {
            $io->error('Impossible de trouver le répertoire cible via le FileLocationResolver.');
            return false;
        }
        $newDir = $location->getDirectory();

        if (!$this->filesystem->exists($oldDir)) {
            $io->warning(sprintf('Le dossier source "%s" n’existe pas ou a déjà été migré.', $oldDir));
            return true;
        }

        if (!$this->filesystem->exists($newDir)) {
            $this->filesystem->mkdir($newDir, 0755);
        }

        $images = $this->entityManager->getRepository($migration['entityClass'])->findAll();
        $count = 0;

        $io->progressStart(count($images));

        foreach ($images as $image) {
            $filename = $image->getFilename();
            if (!$filename) {
                $io->progressAdvance();
                continue;
            }

            $sourcePath = $this->fileService->join($oldDir, $filename);
            $targetPath = $this->fileService->join($newDir, $filename);

            if ($this->filesystem->exists($sourcePath)) {
                try {
                    $this->filesystem->rename($sourcePath, $targetPath);
                    $count++;
                } catch (IOExceptionInterface $exception) {
                    $io->error(sprintf('Échec du déplacement pour le fichier : %s. Erreur : %s', $filename, $exception->getMessage()));
                }
            }else {
                $io->warning(sprintf('Fichier introuvable sur le disque : %s', $sourcePath));
            }
            $io->progressAdvance();
        }

        $io->progressFinish();


        $io->success(sprintf('%d fichier(s) ont été déplacés vers %s.', $count, $newDir));

        return true;
    }
}
