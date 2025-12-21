<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

abstract class AbstractFixture extends Fixture
{
    protected function persistAndFlush(ObjectManager $manager, array $entities): void
    {
        foreach ($entities as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
