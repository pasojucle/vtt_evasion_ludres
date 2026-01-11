<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\DataFixtures\Common\MembershipFeeFixtures;
use App\Entity\MembershipFee;
use App\Entity\MembershipFeeAmount;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MembershipFeeAmountFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const MEMBERSHIP_FEE_AMOUNT_1 = 'membership_fee_amount_1';
    public const MEMBERSHIP_FEE_AMOUNT_2 = 'membership_fee_amount_2';
    public const MEMBERSHIP_FEE_AMOUNT_3 = 'membership_fee_amount_3';
    public const MEMBERSHIP_FEE_AMOUNT_4 = 'membership_fee_amount_4';
    public const MEMBERSHIP_FEE_AMOUNT_5 = 'membership_fee_amount_5';
    public const MEMBERSHIP_FEE_AMOUNT_6 = 'membership_fee_amount_6';
    public const MEMBERSHIP_FEE_AMOUNT_7 = 'membership_fee_amount_7';
    public const MEMBERSHIP_FEE_AMOUNT_8 = 'membership_fee_amount_8';
    public const MEMBERSHIP_FEE_AMOUNT_9 = 'membership_fee_amount_9';
    public const MEMBERSHIP_FEE_AMOUNT_10 = 'membership_fee_amount_10';
    public const MEMBERSHIP_FEE_AMOUNT_11 = 'membership_fee_amount_11';
    public const MEMBERSHIP_FEE_AMOUNT_12 = 'membership_fee_amount_12';
    public const MEMBERSHIP_FEE_AMOUNT_13 = 'membership_fee_amount_13';


    public static function getGroups(): array
    {
        return ['test'];
    }


    public function getDependencies(): array
    {
        return [
            MembershipFeeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $membershipFeesAmounts = [
            self::MEMBERSHIP_FEE_AMOUNT_1 => [MembershipFeeFixtures::MEMBERSHIP_FEE_NEW, "100", "1"],
            self::MEMBERSHIP_FEE_AMOUNT_2 => [MembershipFeeFixtures::MEMBERSHIP_FEE_NEW, "100", "2"],
            self::MEMBERSHIP_FEE_AMOUNT_3 => [MembershipFeeFixtures::MEMBERSHIP_FEE_NEW, "150", "3"],
            self::MEMBERSHIP_FEE_AMOUNT_4 => [MembershipFeeFixtures::MEMBERSHIP_FEE_NEW_ADDITIONNAL, "90", "1"],
            self::MEMBERSHIP_FEE_AMOUNT_5 => [MembershipFeeFixtures::MEMBERSHIP_FEE_NEW_ADDITIONNAL, "90", "2"],
            self::MEMBERSHIP_FEE_AMOUNT_6 => [MembershipFeeFixtures::MEMBERSHIP_FEE_NEW_ADDITIONNAL, "140", "3"],
            self::MEMBERSHIP_FEE_AMOUNT_7 => [MembershipFeeFixtures::MEMBERSHIP_FEE_RENEW, "80", "1"],
            self::MEMBERSHIP_FEE_AMOUNT_8 => [MembershipFeeFixtures::MEMBERSHIP_FEE_RENEW, "80", "2"],
            self::MEMBERSHIP_FEE_AMOUNT_9 => [MembershipFeeFixtures::MEMBERSHIP_FEE_RENEW, "130", "3"],
            self::MEMBERSHIP_FEE_AMOUNT_10 => [MembershipFeeFixtures::MEMBERSHIP_FEE_RENEW_ADDITIONNAL, "70", "1"],
            self::MEMBERSHIP_FEE_AMOUNT_11 => [MembershipFeeFixtures::MEMBERSHIP_FEE_RENEW_ADDITIONNAL, "70", "2"],
            self::MEMBERSHIP_FEE_AMOUNT_12 => [MembershipFeeFixtures::MEMBERSHIP_FEE_RENEW_ADDITIONNAL, "120", "3"],
            self::MEMBERSHIP_FEE_AMOUNT_13 => [MembershipFeeFixtures::MEMBERSHIP_FEE_OPTION, "28", null],
        ];

        foreach ($membershipFeesAmounts as $ref => [$membershipFee, $amount, $coverage]) {
            $membershipFeeRef = $this->getReference($membershipFee, MembershipFee::class);


            $membershipFeeAmount = new MembershipFeeAmount();
            $membershipFeeAmount->setMembershipFee($membershipFeeRef)
                ->setAmount((float) $amount)
                ->setCoverage((int) $coverage);

            $manager->persist($membershipFeeAmount);
            $this->addReference($ref, $membershipFeeAmount);
        }

        $manager->flush();
    }
}
