<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campus = new Campus();
        $campus->setName('SAINT HERBLAIN');
        $manager->persist($campus);
        $this->addReference('campus_1', $campus);

        $campus = new Campus();
        $campus->setName('CHARTRES DE BRETAGNE');
        $manager->persist($campus);
        $this->addReference('campus_2', $campus);

        $campus = new Campus();
        $campus->setName('LA ROCHE SUR YON');
        $manager->persist($campus);
        $this->addReference('campus_3', $campus);


        $manager->flush();
    }
}