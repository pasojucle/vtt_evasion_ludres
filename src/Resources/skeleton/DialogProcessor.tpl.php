<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\<?= $entity_name ?>;
use App\Service\UrlContextService;
use Doctrine\ORM\EntityManagerInterface;
use App\State\Interface\FormRedirectProcessorInterface;


class <?= $entity_name ?><?= $action_name ?>Processor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
    * @implements FormRedirectProcessorInterface<<?= $entity_name ?>>
    */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        // $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: '<?= $entity_name ?>.flash.success.<?= $action_name ?>',
            flashType: 'success',
        );
    }
}