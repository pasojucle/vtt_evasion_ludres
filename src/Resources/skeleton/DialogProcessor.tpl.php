<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Processor;

use App\Entity\<?= $entity_name ?>;
use App\Service\FilterDecoderService;
use Doctrine\ORM\EntityManagerInterface;
use App\State\DialogProcessorInterface;


class <?= $entity_name ?><?= $action_name ?>Processor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilterDecoderService $filterDecoder,
    ) {}

    public function process(object $entity, ?string $filter): ProcessorResult
    {
        // $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new ProcessorResult(
            success: true,
            targetRoute: '<?= $route ?>',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: '<?= $entity_name ?>.flash.success.<?= $entity_name ?>',
            flashType: 'success',
        );
    }
}