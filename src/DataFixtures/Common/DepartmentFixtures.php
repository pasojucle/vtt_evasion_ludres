<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DepartmentFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const DEPT_54 = 'department_54';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $dept = new Department();
        $dept->setId('54');
        $dept->setName('Meurthe-et-Moselle');

        $manager->persist($dept);
        $this->addReference(self::DEPT_54, $dept);

        $manager->flush();
    }
}
