<?php

declare(strict_types=1);

namespace App\Service;

class ExportService
{
    public function exportUsers(array $users): string
    {
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach ($users as $user) {
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->getState()->isYearly()];
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }

    public function exportOrderHeaders(array $orderHeaders): string
    {
        $content = [];
        $row = ['Prénom', 'Nom', 'N°CDE', 'Produit', 'Ref', 'Taille', 'Quantité', 'Prix', 'Statut'];
        $content[] = implode(',', $row);

        foreach ($orderHeaders as $orderHeader) {
            foreach ($orderHeader->orderLines->lines as $orderLine) {
                $row = [$orderHeader->user->member->firstName, $orderHeader->user->member->name, $orderHeader->id, $orderLine->product->name, $orderLine->product->ref, $orderLine->size, $orderLine->quantity, $orderLine->amountToString, $orderHeader->statusToString];
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }

    public function exportSkills(array $skills): string
    {
        $content = [];
        $row = ['Descriptif', 'Catégorie', 'Niveau'];
        $content[] = implode(',', $row);

        foreach ($skills as $skill) {
            $row = [sprintf('"%s"', $skill->content), $skill->category['name'], $skill->level['title']];
            $content[] = implode(',', $row);
        }

        return implode(PHP_EOL, $content);
    }
}
