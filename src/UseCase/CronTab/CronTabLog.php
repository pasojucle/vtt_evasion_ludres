<?php

declare(strict_types=1);

namespace App\UseCase\CronTab;

use App\Service\ProjectDirService;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;

class CronTabLog
{
    private const string FILENAME = 'crontab.log';

    public function __construct(
        private Filesystem $filesystem,
        private ProjectDirService $projectDir,
    ) {
    }
    public function write(int $codeError, array $context, ?string $message = null): void
    {
        $content = [
            'executeAt' => (new DateTime())->format('Y-m-d h:i:s'),
            'codeError' => $codeError,
            'context' => $context
        ];
        if ($message) {
            $content['message'] = $message;
        }

        $this->filesystem->appendToFile($this->projectDir->path('data', self::FILENAME), json_encode($content) . PHP_EOL);
    }

    public function read(): array
    {
        $filename = $this->projectDir->path('data', self::FILENAME);
        if (file_exists($filename)) {
            $content = explode(PHP_EOL, $this->filesystem->readFile($filename));

            return array_map(fn ($log): ?array => json_decode($log, true), array_reverse($content));
        }
        
        return [];
    }

    public function filemtime(): int
    {
        $filename = $this->projectDir->path('data', self::FILENAME);
        if (file_exists($filename)) {
            return filemtime($filename);
        }

        return 0;
    }
}
