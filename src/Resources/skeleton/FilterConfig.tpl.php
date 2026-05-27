<?= '<?php' ?>

declare(strict_types=1);

namespace App\Service\Filter;

use App\Dto\Filter\<?= $entity_name ?>Filter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class <?= $entity_name ?>FilterConfig implements FilterConfigInterface
{
    public function getRouteName(): string
    {
        return '<?= $route ?>';
    }

    public function supports(string $route): bool
    {
        return $route === $this->getRouteName();
    }

    public function getEventSubscriber(): ?EventSubscriberInterface
    {
        // TODO: Ajoutez le subsciber si besoins
        return null;
    }

    public function getFields(): array
    {
        return [
            // TODO: Ajoutez les FilterFieldConfig ici
        ];
    }

    public function getAdvancedFields(): array
    {
        return [
            // TODO: Ajoutez les FilterFieldConfig ici
            new FilterFieldConfig(
                name: 'itemsPerPage',
                type: ChoiceType::class,
                options: [
                    'label' => 'Nombre de résultats',
                    'choices' => [
                        '15' => 15,
                        '25' => 25,
                        '50' => 50,
                        '100' => 100,
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
                chipCcomputed: true,
            ),
            new FilterFieldConfig(
                name: 'sort',
                type: ChoiceType::class,
                options: [
                    'label' => 'Tri',
                    'choices' => [
                        'Date (du plus ancien au plus récent)' => 'ASC',
                        'Date (du plus récent au plus ancien)' => 'DESC',
                    ],
                    'required' => false,
                    'row_attr' => ['class' => 'form-group not-last:border-border not-last:border-b not-last:pb-4'],
                    'attr' => ['class' => 'form-control']
                ],
            ),
        ];
    }

    public function getDataClass(): ?string
    {
        return <?= $entity_name ?>Filter::class;
    }
}