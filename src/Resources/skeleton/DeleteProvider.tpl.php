<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\<?= $entity_name ?>;
use App\Mapper\DestructiveModalMapper;

class <?= $entity_name ?>DeleteProvider
{
     public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }

    public function mapToView(<?= $entity_name ?> $entity): DialogModalView
    {

        return $this->destructiveModalMapper->mapToView(sprintf('<?= $message ?>', $entity-><?= $getter ?>));
    }
}