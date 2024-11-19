<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        # Initialisation de Faker
        $faker = \Faker\Factory::create('fr_FR');

        // Créer et persister des villes
        for ($i = 1; $i <= 100; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->city()); // Génère un nom de ville française
            $ville->setCodePostal($faker->postcode()); // Génère un code postal français
            $manager->persist($ville);

            // Ajouter une référence pour pouvoir y accéder dans d'autres fixtures
            $this->addReference('ville_' . $i, $ville);
        }

        $manager->flush();
    }
}