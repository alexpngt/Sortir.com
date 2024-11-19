<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            VilleFixtures::class, // Dépendance à VilleFixtures
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 10; $i++) { // Créons 10 lieux
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);

            // Associer une ville (références commencent à 1)
            $ville = $this->getReference('ville_' . $faker->numberBetween(1, 5)); // Références des villes
            $lieu->setVille($ville);

            // Ajouter une référence pour l'utiliser dans Sortie
            $this->addReference('lieu_' . $i, $lieu);

            $manager->persist($lieu);
        }

        $manager->flush();
    }
}