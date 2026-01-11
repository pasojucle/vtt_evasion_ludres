<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\RegistrationStepGroup;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RegistrationStepGroupFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const REGIRSTRATION_STEP_GROUP_REGISTRATION = 'regirstration_step_group_registration';
    public const REGIRSTRATION_STEP_GROUP_INFO = 'regirstration_step_group_info';
    public const REGIRSTRATION_STEP_GROUP_WARRANTY = 'regirstration_step_group_warranty';
    public const REGIRSTRATION_STEP_GROUP_MEMBERSHIP_FEE = 'regirstration_step_group_membership_fee';
    public const REGIRSTRATION_STEP_GROUP_COVERAGE = 'regirstration_step_group_coverage';
    public const REGIRSTRATION_STEP_GROUP_AUTHORIZATION = 'regirstration_step_group_authorization';
    public const REGIRSTRATION_STEP_GROUP_HEATH = 'regirstration_step_group_heath';
    public const REGIRSTRATION_STEP_GROUP_VALIDATE = 'regirstration_step_group_validate';
    public const REGIRSTRATION_STEP_GROUP_DOWNLOAD = 'regirstration_step_group_download';


    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $registrationStepGroups = [
            self::REGIRSTRATION_STEP_GROUP_REGISTRATION => ['Dossier d\'inscription', 0],
            self::REGIRSTRATION_STEP_GROUP_INFO => ['Informations', 1],
            self::REGIRSTRATION_STEP_GROUP_WARRANTY => ['Tableaux des garanties', 2],
            self::REGIRSTRATION_STEP_GROUP_MEMBERSHIP_FEE => ['Tarifs', 3],
            self::REGIRSTRATION_STEP_GROUP_COVERAGE => ['Assurance', 4],
            self::REGIRSTRATION_STEP_GROUP_AUTHORIZATION => ['Autorisations', 5],
            self::REGIRSTRATION_STEP_GROUP_HEATH => ['Santé', 6],
            self::REGIRSTRATION_STEP_GROUP_VALIDATE => ['Validation', 7],
            self::REGIRSTRATION_STEP_GROUP_DOWNLOAD => ['Téléchargement', 8],
        ];

        foreach ($registrationStepGroups as $ref => [$title, $orderBy]) {
            $registrationStepGroup = new RegistrationStepGroup();
            $registrationStepGroup->setTitle($title)
                ->setOrderBy($orderBy);

            $manager->persist($registrationStepGroup);
            $this->addReference($ref, $registrationStepGroup);
        }

        $manager->flush();
    }
}
