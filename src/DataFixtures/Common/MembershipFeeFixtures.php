<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\MembershipFee;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class MembershipFeeFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const MEMBERSHIP_FEE_NEW = 'membership_fee_new';
    public const MEMBERSHIP_FEE_NEW_ADDITIONNAL = 'membership_fee_new_additionnal';
    public const MEMBERSHIP_FEE_RENEW = 'membership_fee_renew';
    public const MEMBERSHIP_FEE_RENEW_ADDITIONNAL = 'membership_fee_renew_additionnal';
    public const MEMBERSHIP_FEE_OPTION = 'membership_fee_option';


    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $membershipFees = [
            self::MEMBERSHIP_FEE_NEW => ["Tarif nouvel adhérent","0","1", ""],
            self::MEMBERSHIP_FEE_NEW_ADDITIONNAL => ["Tarif nouvel adhérent supplémentaire","1","1", "Pour un membre supplémentaire d'une même famille (Père-mère-enfants)"],
            self::MEMBERSHIP_FEE_RENEW => ["Tarif ré-inscription","0","0", ""],
            self::MEMBERSHIP_FEE_RENEW_ADDITIONNAL => ["Tarif ré-inscription membre supplémentaire","1s","0", "Pour un membre supplémentaire d'une même famille (Père-mère-enfants)"],
            self::MEMBERSHIP_FEE_OPTION => ["Option : Abonnement à la revue “Cyclotourisme” éditée par la FFCT (11 numéros)",null,null, ""],
        ];

        foreach ($membershipFees as $ref => [$title, $additionnalFamilyMember, $newMember, $content]) {
            $membershipFee = new MembershipFee();
            $membershipFee->setTitle($title)
                ->setAdditionalFamilyMember((bool) $additionnalFamilyMember)
                ->setNewMember((bool) $newMember)
                ->setContent($content);

            $manager->persist($membershipFee);
            $this->addReference($ref, $membershipFee);
        }

        $manager->flush();
    }
}
