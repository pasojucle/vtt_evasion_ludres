<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const CATEGORY_BIKE = 'category_bike';
    public const CATEGORY_COMPONENTS = 'category_component';
    public const CATEGORY_ACCESSORY = 'category_accessory';
    public const CATEGORY_CLOTHING = 'category_clothing';

    public const CATEGORIES = [
        self::CATEGORY_BIKE => ['Vélo'],
        self::CATEGORY_COMPONENTS => ['composant'],
        self::CATEGORY_ACCESSORY => ['Accessoire'],
        self::CATEGORY_CLOTHING => ['Vêtement'],
    ];

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $ref => [$name]) {
            $category = new Category();
  
            $category->setName($name);
                            
            $manager->persist($category);
            $this->addReference($ref, $category);
        }

        $manager->flush();
    }
}
