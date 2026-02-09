<?php

namespace App\DataFixtures;

use App\Entity\PlayerCategory;
use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = ['Baby', 'U6', 'U8', 'U10', 'U12'];

        foreach ($categories as $code) {
            $category = new PlayerCategory();
            $category->setCode($code);
            $category->setDescription($code);
            $manager->persist($category);
        }

        $season = new Season();
        $season->setStartDate(new \DateTime('2025-09-01'));
        $season->setEndDate(new \DateTime('2026-07-04'));
        $manager->persist($season);

        $manager->flush();
    }
}
