<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\SecondHandImageRepository;
use App\Service\FileLocation\FileLocationResolver;
use App\Service\FileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:media:migrate-second-hand',
    description: 'Déplace les images d’occasion de public/upload vers le nouveau dossier sécurisé data/',
)]
class MigrateSecondHandFilesCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private FileLocationResolver $resolver,
        private FileService $fileService,
        private SecondHandImageRepository $secondHandImageRepository,
        private Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Migration des fichiers d’annonces d’occasion (Filesystem)');

        $oldDir = $this->fileService->join($this->projectDir, 'public', 'upload', 'second_hand');
        $newDir = $this->resolver->resolveDirectory('second-hand')->getDirectory();

        if (!$newDir) {
            $io->error('Impossible de trouver le répertoire cible via le FileLocationResolver.');
            return Command::FAILURE;
        }

        if (!$this->filesystem->exists($oldDir)) {
            $io->warning(sprintf('Le dossier source "%s" n’existe pas ou a déjà été migré.', $oldDir));
            return Command::SUCCESS;
        }

        if (!$this->filesystem->exists($newDir)) {
            $this->filesystem->mkdir($newDir, 0755);
        }

        $images = $this->secondHandImageRepository->findAll();
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
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        try {
            $this->filesystem->remove($oldDir);
            $io->note('L’ancien dossier public/upload/second_hand a été supprimé avec succès.');
        } catch (IOExceptionInterface $exception) {
            $io->warning(sprintf('Impossible de supprimer l’ancien dossier : %s', $exception->getMessage()));
        }

        $io->success(sprintf('%d fichier(s) ont été déplacés vers %s.', $count, $newDir));

        return Command::SUCCESS;
    }
}
