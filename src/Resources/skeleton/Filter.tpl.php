<?= '<?php' ?>

declare(strict_types=1);

namespace App\Dto\Filter;

readonly class <?= $entity_name ?>Filter extends AbstractFilter
{
    public function __construct(
        // TODO: Ajoutez les propriétés de votre filtre ici
        // public ?string $search = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}