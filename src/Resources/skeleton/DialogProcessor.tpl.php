<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Processor;

use App\Dto\State\ProcessorResult;
use App\Entity\<?= $entity_name ?>;
use App\Service\UrlContextService;
use Doctrine\ORM\EntityManagerInterface;
use App\State\DialogProcessorInterface;


class <?= $entity_name ?><?= $action_name ?>Processor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
    * @implements DialogProcessorInterface<<?= $entity_name ?>>
    */
    public function process(object $entity, ?string $targetUrl = null): ProcessorResult
    {
        // $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: '<?= $entity_name ?>.flash.success.<?= $action_name ?>',
            flashType: 'success',
        );
    }
}