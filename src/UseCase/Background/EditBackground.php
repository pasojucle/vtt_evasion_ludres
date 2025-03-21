<?php

declare(strict_types=1);

namespace App\UseCase\Background;

use App\Entity\Background;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class EditBackground
{
    public function __construct(
        private UploadService $uploadService,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function execute(Background $background, Request $request, ?string $currentFilename): void
    {
        if ($request->files->get('background') && $request->files->get('background')['backgroundFile']) {
            $file = $request->files->get('background')['backgroundFile'];
            $background->setFileName($this->uploadService->uploadFile($file, 'backgrounds_directory_path'));
            if ($currentFilename) {
                $finder = new Finder();
                $finder->files()->name($currentFilename)->in($this->parameterBag->get('backgrounds_directory_path'));
                if ($finder->hasResults()) {
                    foreach ($finder as $file) {
                        unlink($file->getPathname());
                    }
                }
            }
            $this->makeAllSizes($background);
            $this->entityManager->persist($background);
            $this->entityManager->flush();
        }
    }

    public function makeAllSizes(Background $background): void
    {
        $sizes = [
            ['positions' => $background->getLandscapePosition(), 'outputWidth' => 1920, 'outputHeight' => 1080, 'outputDir' => 'landscape_xl'],
            ['positions' => $background->getLandscapePosition(), 'outputWidth' => 800, 'outputHeight' => 450, 'outputDir' => 'landscape_md'],
            ['positions' => $background->getLandscapePosition(), 'outputWidth' => 400, 'outputHeight' => 225, 'outputDir' => 'landscape_xs'],
            ['positions' => $background->getPortraitPosition(), 'outputWidth' => 450, 'outputHeight' => 800, 'outputDir' => 'portrait_md'],
            ['positions' => $background->getPortraitPosition(), 'outputWidth' => 225, 'outputHeight' => 400, 'outputDir' => 'portrait_xs'],
            ['positions' => $background->getSquarePosition(), 'outputWidth' => 850, 'outputHeight' => 850, 'outputDir' => 'square'],
        ];

        foreach ($sizes as $size) {
            $this->resizeBackground($background->getFilename(), $size['positions'], $size['outputWidth'], $size['outputHeight'], $size['outputDir']);
        }
    }

    public function resizeBackground(string $filename, array $positions, int $outputWidth, int $outputHeight, string $outputDir): bool
    {
        $inputPath = $this->parameterBag->get('backgrounds_directory_path') . $filename;
        list($originWidth, $originHeight, $type) = getimagesize($inputPath);

        $ratio = ($outputWidth / $outputHeight < $originWidth / $originHeight)
            ? $outputHeight / $originHeight
            : $outputWidth / $originWidth;

        $imageSrc = (IMAGETYPE_JPEG == $type) ? imagecreatefromjpeg($inputPath) : imagecreatefrompng($inputPath);
        $imageBlack = imagecreatetruecolor($outputWidth, $outputHeight);

        $this->mkdirIfNotExists($outputDir);

        $outputPath = $this->parameterBag->get('backgrounds_directory_path') . $outputDir . DIRECTORY_SEPARATOR . $filename;
        imagecopyresampled($imageBlack, $imageSrc, 0, 0, (int) round($positions['positionX']), (int) round($positions['positionY']), (int) round($originWidth * $ratio), (int) round($originHeight * $ratio), $originWidth, $originHeight);

        if (!$imageBlack = $this->uploadService->imageRotate($inputPath, $imageBlack)) {
            return false;
        }

        if (!imagejpeg($imageBlack, $outputPath) || !imagepng($imageBlack, $outputPath)) {
            return false;
        }

        return true;
    }

    private function mkdirIfNotExists(string $outputDir): void
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->parameterBag->get('backgrounds_directory_path') . $outputDir)) {
            $filesystem->mkdir($this->parameterBag->get('backgrounds_directory_path') . $outputDir, 0775);
        }
    }
}
