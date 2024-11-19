<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    # Pour que toutes les fixtures des autres entités soient chargées avant les sorties [dépendance]
    public function getDependencies(): array
    {
        return [
            EtatFixtures::class,
            CampusFixtures::class,
            LieuFixtures::class,
            UserFixtures::class,
        ];
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= 50; $i++) {
            $sortie = new Sortie();
            $sortie->setName($faker->sentence(3));

            $dateStart = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+1 year'));
            $sortie->setDateStart($dateStart);

            $sortie->setDuration($faker->numberBetween(1, 8));

            $dateLimitInscription = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+6 months'));
            $sortie->setDateLimitInscription($dateLimitInscription);

            $sortie->setNbMaxInscription($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->text());

            $lieu = $this->getReference('lieu_' . $faker->numberBetween(1, 10));
            $sortie->setLieu($lieu);


            # état en fonction des 5 états crées
            $etat = $this->getReference('etat_' . $faker->numberBetween(0, 5)); // Correspond aux clés dans EtatFixtures
            $sortie->setEtat($etat);

            # campus en fonction des 3 campus
            $campusOrganisateur = $this->getReference('campus_' . $faker->numberBetween(1, 3)); // Campus aléatoire
            $sortie->setCampusOrganisateur($campusOrganisateur);

            // Nombre maximum de participants
            $nbMaxInscription = $faker->numberBetween(5, 30); // Définir ici
            $sortie->setNbMaxInscription($nbMaxInscription);

            // Ajouter des participants (au maximum nbMaxInscription)
            $nbParticipants = $faker->numberBetween(0, $nbMaxInscription); // Participants aléatoires, mais ≤ nbMaxInscription
            for ($j = 0; $j < $nbParticipants; $j++) {
                $participant = $this->getReference('user_' . $faker->numberBetween(1, 19)); // Supposez que vous avez 20 utilisateurs dans UserFixtures
                $sortie->addParticipant($participant);
            }

            // Ajouter un organisateur
            $organisateur = $this->getReference('user_' . $faker->numberBetween(1, 19)); // Supposez que vous avez 20 utilisateurs dans UserFixtures
            $sortie->setOrganisateur($organisateur);

            $manager->persist($sortie);

            // Ajouter une référence pour l'utiliser dans UserFixtures
            $this->addReference('sortie_' . $i, $sortie);
        }

        $manager->flush();
    }

}