<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Commune;
use App\Entity\Department;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\Common\DepartmentFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommuneFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const COMMUNE_NANCY = 'commune_nanct';
    public const COMMUNE_LUDRES = 'commune_ludres';
    public const COMMUNE_BAINVILLE = 'commune_bainville';
    public const COMMUNE_MAIZIERES = 'commune_maizieres';
    public const COMMUNE_PONT_SAINT_VINCENT = 'commune_pont_saint_vincent';
    public const COMMUNE_SEXEY_AUX_FORGES = 'commune_sexey_aux_forges';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            DepartmentFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {        
        $department = $this->getReference(DepartmentFixtures::DEPT_54, Department::class);
    
        $communes = [
            self::COMMUNE_NANCY => ['54395', 'Nancy', '54000'],
            self::COMMUNE_LUDRES => ['54328', 'Ludres', '54710'],
            self::COMMUNE_BAINVILLE => ['54043',"Bainville-sur-Madon",'54550'],
            self::COMMUNE_MAIZIERES => ['54336',"Maizières",'54550'],
            self::COMMUNE_PONT_SAINT_VINCENT => ['54432',"Pont-Saint-Vincent",'54550'],
            self::COMMUNE_SEXEY_AUX_FORGES => ['54505',"Sexey-aux-Forges",'54550'],
        ];

        foreach ($communes as $ref => [$id,$name, $postalCode]) {
            $commune = new Commune();
            $commune->setId($id)
                ->setDepartment($department)
                ->setName($name)
                ->setPostalCode($postalCode);

            $manager->persist($commune);
            $this->addReference($ref, $commune);
        }

        $manager->flush();
    }
}
