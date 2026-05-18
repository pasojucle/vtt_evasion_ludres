<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Entity\OrderLine;
use App\Service\OrderService;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderAdminListExportMapper
{
    private const CSV_SEPARATOR = ",";
    
    public function __construct(
        private OrderService $orderService,
        private TranslatorInterface $translator,
    ) {
    }

    public function streamToCsv(array $entities): void
    {
        $fp = fopen('php://output', 'w');
        
        $headers = ['Prénom', 'Nom', 'N°CDE', 'Produit', 'Ref', 'Taille', 'Quantité', 'Prix', 'Statut'];
        fputcsv($fp, $headers, self::CSV_SEPARATOR);

        foreach ($entities as $entity) {
            $member = $entity->getMember();
            $identity = $member->getIdentity();
            $status = $entity->getStatus();
            $mainRow = [
                    $identity->getFirstName(),
                    $identity->getName(), 
                    $entity->getId(),
                    '',
                    '', 
                    '', 
                    '', 
                    $this->orderService->getAmount($entity->getOrderLines(), $entity->getMember()), 
                    $status->trans($this->translator)
                ];
                fputcsv($fp, $mainRow, self::CSV_SEPARATOR);
            /** @var OrderLine $line */
            foreach ($entity->getOrderLines() as $line) {
                $product = $line->getProduct();
                $amount = $product->getCategory() === $member->getLastLicence()->getCategory()
                        ? $product->getDiscountPrice()
                        : $product->getPrice();
                $lineState = $line->getState();
                $lineRow = [
                    '',
                    '', 
                    '', 
                    $product->getName(), 
                    $product->getRef(), 
                    $line->getSize()->getName(), 
                    $line->getQuantity(), 
                    sprintf('%s €', number_format($amount, 2)),
                    $lineState->trans($this->translator)
                ];
                fputcsv($fp, $lineRow, self::CSV_SEPARATOR);
            }
            fputcsv($fp, [], self::CSV_SEPARATOR);
            flush();
        }

        fclose($fp);
    }
}
