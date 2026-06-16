<?= '<?php' ?>

declare(strict_types=1);

namespace App\State\<?= $entity_name ?>\Provider;

use App\Dto\View\DialogModalView;
use App\Dto\Enum\DialogType;
use App\Entity\<?= $entity_name ?>;
use App\Mapper\DestructiveModalMapper;
use App\State\DialogProviderInterface;


class <?= $entity_name ?><?= $action_name ?>Provider implements DialogProviderInterface
{
     public function __construct(
        // private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }

    public function mapToView(object  $entity): DialogModalView
    {

        // return $this->destructiveModalMapper->mapToView(sprintf('<?= $message ?>', $entity-><?= $getter ?>));
        /** @var <?= $entity_name ?> $entity*/

        return new DialogModalView(
            type: DialogType::SUCCESS,
            title: 'Mon titre',
            action: 'Action',
            message: sprintf('<?= $message ?>', $entity-><?= $getter ?>),
            icon: 'lucide:square-check-big'
        );
    }
}