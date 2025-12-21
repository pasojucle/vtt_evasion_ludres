<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\ParameterGroup;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class ParameterGroupFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const PARAMETER_GROUP_CONTENT = 'parameter_group_content';
    public const PARAMETER_GROUP_REGISTRATION = 'parameter_group_registration';
    public const PARAMETER_GROUP_USER = 'parameter_group_user';
    public const PARAMETER_GROUP_TOOLS = 'parameter_group_tools';
    public const PARAMETER_GROUP_MAINTENANCE = 'parameter_group_maintenance';
    public const PARAMETER_GROUP_SECOND_HAND = 'parameter_group_second_hand';
    public const PARAMETER_GROUP_BIKE_RIDE= 'parameter_group_bike_ride';
    public const PARAMETER_GROUP_ORDER = 'parameter_group_order';
    public const PARAMETER_GROUP_MODAL = 'parameter_group_modal';
    public const PARAMETER_GROUP_SLIDESHOW = 'parameter_group_slideshow';
    public const PARAMETER_GROUP_BIKE_RIDE_TYPE = 'parameter_group_bike_ride_type';
    public const PARAMETER_GROUP_DOCUMENTATION= 'parameter_group_documentation';


    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $parameterGroups = [
            self::PARAMETER_GROUP_CONTENT => ["CONTENT","Contenu","NONE"],
            self::PARAMETER_GROUP_REGISTRATION => ["REGISTRATION","Inscription","NONE"],
            self::PARAMETER_GROUP_USER => ["USER","Adhérent","NONE"],
            self::PARAMETER_GROUP_TOOLS => ["TOOLS","Journal des erreurs","NONE"],
            self::PARAMETER_GROUP_MAINTENANCE=> ["MAINTENANCE","Maintenance","ROLE_SUPER_ADMIN"],
            self::PARAMETER_GROUP_SECOND_HAND => ["SECOND_HAND","Occasion","NONE"],
            self::PARAMETER_GROUP_BIKE_RIDE => ["BIKE_RIDE","Rando","NONE"],
            self::PARAMETER_GROUP_ORDER => ["ORDER","Commande","NONE"],
            self::PARAMETER_GROUP_MODAL => ["MODAL","Pop'up","NONE"],
            self::PARAMETER_GROUP_SLIDESHOW => ["SLIDESHOW","Diaporama","NONE"],
            self::PARAMETER_GROUP_BIKE_RIDE_TYPE => ["BIKE_RIDE_TYPE","Type d'événement","NONE"],
            self::PARAMETER_GROUP_DOCUMENTATION => ["DOCUMENTATION","Documentation","NONE"]
        ];

        foreach ($parameterGroups as $ref => [$name, $label, $role]) {
            $parameterGroup = new ParameterGroup();
            $parameterGroup->setName($name)
                ->setLabel($label)
                ->setRole($role);

            $manager->persist($parameterGroup);
            $this->addReference($ref, $parameterGroup);
        }

        $manager->flush();
    }
}
