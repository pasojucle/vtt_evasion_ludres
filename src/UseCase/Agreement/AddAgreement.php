<?php

declare(strict_types=1);

namespace App\UseCase\Agreement;

use App\Repository\AgreementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class AddAgreement
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AgreementRepository $agreementRepository,
    )
    {
    }

    public function execute(FormInterface $form): void
    {
        $agreement = $form->getData();

        if (null === $agreement->getOrderBy()) {
            $order = $this->agreementRepository->findNexOrder();
            $agreement->setOrderBy($order);
        }
        $this->entityManager->persist($agreement);
        $this->entityManager->flush();
    }
}