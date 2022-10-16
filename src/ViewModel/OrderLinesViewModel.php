<?php

declare(strict_types=1);

namespace App\ViewModel;

use Symfony\Component\Form\Form;
use Doctrine\Common\Collections\Collection;

class OrderLinesViewModel extends AbstractViewModel
{
    public array $lines = [];

    public static function fromOrderLines(collection $orderLines, UserViewModel $orderUser, ServicesPresenter $services, ?Form $form = null)
    {
        $linesView = new self();
        if (!$orderLines->isEmpty()) {
            foreach ($orderLines as $key => $line) {
                $linesView->lines[] = OrderLineViewModel::fromOrderLine($line, $orderUser, $services, $linesView->getFormName($key, $form?->all()));
            }
        }

        return $linesView;
    }

    private function getFormName(int $key, ?array $form): ?string
    {
        if (null !== $form) {
            $formLine = $form[$key];
            return sprintf('%s[%s][%s][lineId]', $formLine->getParent()->getParent()->getName(), $formLine->getParent()->getName(), $key);
        }
        return null;
    }
}
