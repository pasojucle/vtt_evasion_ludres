<?php

declare(strict_types=1);

namespace App\UseCase\Licence;

use App\Entity\Licence;
use App\Entity\User;
use App\Model\Currency;
use App\Service\IndemnityService;
use App\Repository\MembershipFeeAmountRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetAmount
{
    public function __construct(
        private MembershipFeeAmountRepository $membershipFeeAmountRepository,
        private IndemnityService $indemnityService,
        private TranslatorInterface $translator,
    )
    {
        
    }

    public function execute(Licence $licence): array
    {
        $amount = null;
        $amountStr = '';
        $indemnities = null;

        if ($licence->isFinal()) {
            $isNewMember = $this->isNewMember($licence->getUser());
            $membershipFee = (null !== $licence->getCoverage() && null !== $licence->getAdditionalFamilyMember() && null !== $isNewMember())
                ? $this->membershipFeeAmountRepository->findOneByLicence($licence->getCoverage(), $isNewMember, $licence->getAdditionalFamilyMember())
                : null;
            if (null !== $membershipFee) {
                $amount = $membershipFee->getAmount();
            }
            $indemnities = $this->indemnityService->getUserIndemnities($licence->getUser(), $licence->getSeason() - 1);

            if (null !== $amount && null !== $indemnities) {
                $amount -= $indemnities->getAmount();
            }

            if (null !== $amount) {
                $amount = new Currency($amount);
                $coverageSrt = $this->translator->trans(Licence::COVERAGES[$licence->getCoverage()]);
                $amountStr = "Le montant de votre inscription pour la formule d'assurance {$coverageSrt} est de {$amount->toString()}";
            }
        } else {
            $amountStr = "Votre inscription aux trois séances consécutives d'essai est gratuite.<br>Votre assurance gratuite est garantie sur la formule Mini-braquet.";
        }

        return [
            'value' => $amount,
            'str' => $amountStr,
            'indemnities' => $indemnities,
        ];
    }

    public function isNewMember(User $user): bool
    {
        return 2 > $user->getLicences()->count();
    }
}