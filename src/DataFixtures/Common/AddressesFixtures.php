<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Address;
use App\Entity\Commune;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AddressesFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const ADDRESS_ADMIN = 'address_admin';
    public const ADDRESS_ADULT = 'address_adult';
    public const ADDRESS_SCHOLL_MEMBER = 'address_school_member';

    public const ADDRESSES = [
        self::ADDRESS_ADMIN => ['9 rue de la transmission', CommuneFixtures::COMMUNE_LUDRES],
        self::ADDRESS_ADULT => ['Chemin du Single-track', CommuneFixtures::COMMUNE_BAINVILLE],
        self::ADDRESS_SCHOLL_MEMBER => ['Boulevard du Braquet', CommuneFixtures::COMMUNE_SEXEY_AUX_FORGES],
    ];

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            CommuneFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach(self::ADDRESSES as $ref => [$street, $commune]) {
            $communeRef = $this->getReference($commune, Commune::class);
            $address = new Address();
            $address
                ->setStreet($street)
                ->setCommune($communeRef)
                ->setTown($communeRef->getName());

            $manager->persist($address);
            $this->addReference($ref, $address);
        }

        $manager->flush();
    }
}
