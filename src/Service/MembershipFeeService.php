<?php

namespace App\Service;

use DateTime;
use App\DataTransferObject\User;
use App\Entity\Licence;
use App\Service\LicenceService;
use App\Repository\MembershipFeeAmountRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class MembershipFeeService
{
    private MembershipFeeAmountRepository $membershipFeeRepository;
    private LicenceService $licenceService;
    private TranslatorInterface $translator;

    public function __construct(
        MembershipFeeAmountRepository $membershipFeeAmountRepository,
        LicenceService $licenceService,
        TranslatorInterface $translator
    )
    {
        $this->membershipFeeAmountRepository = $membershipFeeAmountRepository;
        $this->licenceService = $licenceService;
        $this->translator = $translator;
    }

    public function getAmount(User $user): string
    {
        $amount = null;
        $amountStr = '';

        $seasonLicence = $user->getSeasonLicence();
        dump($seasonLicence);
        if ($seasonLicence['isFinal']) {
            $coverage = $seasonLicence['coverage'];
            $hasFamilyMember = $seasonLicence['hasFamilyMember'];
            $isNewMember = $user->isNewMember();

            $membershipFee = $this->membershipFeeAmountRepository->findOneByLicence($coverage, $isNewMember, $hasFamilyMember);
            if (null !== $membershipFee) {
                $amount = $membershipFee->getAmount();
            }
            if (null !== $amount) {
                $coverageSrt = $this->translator->trans(Licence::COVERAGES[$coverage]);
                $amountStr = "Le montant de votre inscription pour la formule d'assurance $coverageSrt est de $amount €";
            }
        } else {
            $amountStr = "Votre inscription aux trois séances consécutives d'essai est gratuite";
        }

        return $amountStr;
    }
}