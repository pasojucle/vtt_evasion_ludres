<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Processor;

use App\Dto\State\HtmlProcessorResult;
use App\Entity\<?= $entity_name ?>;
use App\Service\UrlContextService;
use Doctrine\ORM\EntityManagerInterface;
use App\State\Interface\HtmlProcessorInterface;


class <?= $entity_name ?><?= $action_name ?>Processor implements HtmlProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
    * @implements HtmlProcessorInterface<<?= $entity_name ?>>
    */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): HtmlProcessorResult
    {
        // $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new HtmlProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: '<?= $entity_name ?>.flash.success.<?= $action_name ?>',
            flashType: 'success',
        );
    }
}