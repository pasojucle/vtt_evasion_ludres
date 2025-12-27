<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Agreement;
use App\Entity\RegistrationStep;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RegistrationStepAgreementFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const REGISTRATION_STEP_AGREEMENT_1 = 'registration_step_agreement_1';
    public const REGISTRATION_STEP_AGREEMENT_2 = 'registration_step_agreement_2';
    public const REGISTRATION_STEP_AGREEMENT_3 = 'registration_step_agreement_3';
    public const REGISTRATION_STEP_AGREEMENT_4 = 'registration_step_agreement_4';
    public const REGISTRATION_STEP_AGREEMENT_5 = 'registration_step_agreement_5';
    public const REGISTRATION_STEP_AGREEMENT_6 = 'registration_step_agreement_6';
    public const REGISTRATION_STEP_AGREEMENT_7 = 'registration_step_agreement_7';
    public const REGISTRATION_STEP_AGREEMENT_8 = 'registration_step_agreement_8';
    public const REGISTRATION_STEP_AGREEMENT_9 = 'registration_step_agreement_9';
    public const REGISTRATION_STEP_AGREEMENT_10 = 'registration_step_agreement_10';
    public const REGISTRATION_STEP_AGREEMENT_11 = 'registration_step_agreement_11';
    public const REGISTRATION_STEP_AGREEMENT_12 = 'registration_step_agreement_12';
    public const REGISTRATION_STEP_AGREEMENT_13 = 'registration_step_agreement_13';
    public const REGISTRATION_STEP_AGREEMENT_14 = 'registration_step_agreement_14';
    public const REGISTRATION_STEP_AGREEMENT_15 = 'registration_step_agreement_15';
    public const REGISTRATION_STEP_AGREEMENT_16 = 'registration_step_agreement_16';
    public const REGISTRATION_STEP_AGREEMENT_17 = 'registration_step_agreement_17';
    public const REGISTRATION_STEP_AGREEMENT_18 = 'registration_step_agreement_18';
    public const REGISTRATION_STEP_AGREEMENT_19 = 'registration_step_agreement_19';
    public const REGISTRATION_STEP_AGREEMENT_20 = 'registration_step_agreement_20';
    public const REGISTRATION_STEP_AGREEMENT_21 = 'registration_step_agreement_21';
    public const REGISTRATION_STEP_AGREEMENT_22 = 'registration_step_agreement_22';
    public const REGISTRATION_STEP_AGREEMENT_23 = 'registration_step_agreement_23';
    public const REGISTRATION_STEP_AGREEMENT_24 = 'registration_step_agreement_24';
    public const REGISTRATION_STEP_AGREEMENT_25 = 'registration_step_agreement_25';
    public const REGISTRATION_STEP_AGREEMENT_26 = 'registration_step_agreement_26';
    public const REGISTRATION_STEP_AGREEMENT_27 = 'registration_step_agreement_27';
    public const REGISTRATION_STEP_AGREEMENT_28 = 'registration_step_agreement_28';
    public const REGISTRATION_STEP_AGREEMENT_29 = 'registration_step_agreement_29';
    public const REGISTRATION_STEP_AGREEMENT_30 = 'registration_step_agreement_30';
    public const REGISTRATION_STEP_AGREEMENT_31 = 'registration_step_agreement_31';
    public const REGISTRATION_STEP_AGREEMENT_32 = 'registration_step_agreement_32';
    public const REGISTRATION_STEP_AGREEMENT_33 = 'registration_step_agreement_33';
    

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            RegistrationStepFixtures::class,
            AgreementsFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $registrationStepAgreements = [
            self::REGISTRATION_STEP_AGREEMENT_1 => [RegistrationStepFixtures::REGIRSTRATION_STEP_3,AgreementsFixtures::AGREEMENT_HEALTH_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_2 => [RegistrationStepFixtures::REGIRSTRATION_STEP_3,AgreementsFixtures::AGREEMENT_HEALTH_ADULT_2],
            self::REGISTRATION_STEP_AGREEMENT_3 => [RegistrationStepFixtures::REGIRSTRATION_STEP_3,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_4 => [RegistrationStepFixtures::REGIRSTRATION_STEP_3,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL_2],
            self::REGISTRATION_STEP_AGREEMENT_5 => [RegistrationStepFixtures::REGIRSTRATION_STEP_8,AgreementsFixtures::AGREEMENT_HEALTH_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_6 => [RegistrationStepFixtures::REGIRSTRATION_STEP_8,AgreementsFixtures::AGREEMENT_BACK_HOME_ALONE],
            self::REGISTRATION_STEP_AGREEMENT_7 => [RegistrationStepFixtures::REGIRSTRATION_STEP_8,AgreementsFixtures::AGREEMENT_EMERGENCY_CARE_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_8 => [RegistrationStepFixtures::REGIRSTRATION_STEP_8,AgreementsFixtures::AGREEMENT_EMERGENCY_CARE_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_9 => [RegistrationStepFixtures::REGIRSTRATION_STEP_8,AgreementsFixtures::AGREEMENT_IMAGE_USE_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_10 => [RegistrationStepFixtures::REGIRSTRATION_STEP_8,AgreementsFixtures::AGREEMENT_PARENTAL_CONSENT],
            self::REGISTRATION_STEP_AGREEMENT_11 => [RegistrationStepFixtures::REGIRSTRATION_STEP_10,AgreementsFixtures::AGREEMENT_BACK_HOME_ALONE],
            self::REGISTRATION_STEP_AGREEMENT_12 => [RegistrationStepFixtures::REGIRSTRATION_STEP_10,AgreementsFixtures::AGREEMENT_EMERGENCY_CARE_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_13 => [RegistrationStepFixtures::REGIRSTRATION_STEP_10,AgreementsFixtures::AGREEMENT_EMERGENCY_CARE_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_14 => [RegistrationStepFixtures::REGIRSTRATION_STEP_10,AgreementsFixtures::AGREEMENT_IMAGE_USE_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_15 => [RegistrationStepFixtures::REGIRSTRATION_STEP_10,AgreementsFixtures::AGREEMENT_IMAGE_USE_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_16 => [RegistrationStepFixtures::REGIRSTRATION_STEP_10,AgreementsFixtures::AGREEMENT_PARENTAL_CONSENT],
            self::REGISTRATION_STEP_AGREEMENT_17 => [RegistrationStepFixtures::REGIRSTRATION_STEP_11,AgreementsFixtures::AGREEMENT_RULES],
            self::REGISTRATION_STEP_AGREEMENT_18 => [RegistrationStepFixtures::REGIRSTRATION_STEP_12,AgreementsFixtures::AGREEMENT_HEALTH_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_19 => [RegistrationStepFixtures::REGIRSTRATION_STEP_12,AgreementsFixtures::AGREEMENT_HEALTH_ADULT_2],
            self::REGISTRATION_STEP_AGREEMENT_20 => [RegistrationStepFixtures::REGIRSTRATION_STEP_12,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_21 => [RegistrationStepFixtures::REGIRSTRATION_STEP_12,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL_2],
            self::REGISTRATION_STEP_AGREEMENT_22 => [RegistrationStepFixtures::REGIRSTRATION_STEP_15,AgreementsFixtures::AGREEMENT_HEALTH_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_23 => [RegistrationStepFixtures::REGIRSTRATION_STEP_15,AgreementsFixtures::AGREEMENT_HEALTH_ADULT_2],
            self::REGISTRATION_STEP_AGREEMENT_24 => [RegistrationStepFixtures::REGIRSTRATION_STEP_15,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_25 => [RegistrationStepFixtures::REGIRSTRATION_STEP_15,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL_2],
            self::REGISTRATION_STEP_AGREEMENT_26 => [RegistrationStepFixtures::REGIRSTRATION_STEP_16,AgreementsFixtures::AGREEMENT_HEALTH_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_27 => [RegistrationStepFixtures::REGIRSTRATION_STEP_16,AgreementsFixtures::AGREEMENT_HEALTH_ADULT_2],
            self::REGISTRATION_STEP_AGREEMENT_28 => [RegistrationStepFixtures::REGIRSTRATION_STEP_16,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_29 => [RegistrationStepFixtures::REGIRSTRATION_STEP_16,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL_2],
            self::REGISTRATION_STEP_AGREEMENT_30 => [RegistrationStepFixtures::REGIRSTRATION_STEP_17,AgreementsFixtures::AGREEMENT_HEALTH_ADULT],
            self::REGISTRATION_STEP_AGREEMENT_31 => [RegistrationStepFixtures::REGIRSTRATION_STEP_17,AgreementsFixtures::AGREEMENT_HEALTH_ADULT_2],
            self::REGISTRATION_STEP_AGREEMENT_32 => [RegistrationStepFixtures::REGIRSTRATION_STEP_17,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL],
            self::REGISTRATION_STEP_AGREEMENT_33 => [RegistrationStepFixtures::REGIRSTRATION_STEP_17,AgreementsFixtures::AGREEMENT_HEALTH_SCHOOL_2],
        ];

        foreach ($registrationStepAgreements as $ref => [$registrationStep, $agreement]) {

            $registrationStepRef = $this->getReference($registrationStep, RegistrationStep::class);
            $agreementRef = $this->getReference($agreement, Agreement::class);
            $agreementRef->addRegistrationStep($registrationStepRef);
            $manager->persist($agreementRef);

            $this->addReference($ref, $agreementRef);
        }

        $manager->flush();
    }
}
