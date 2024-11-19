<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }

    // Injection du service de hachage de mot de passe via le constructeur
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        # Initialisation de Faker
        $faker = \Faker\Factory::create('fr_FR');

        // Récupération de tous les campus dans la BD
        $campusList = $manager->getRepository(Campus::class)->findAll();

        // Vérification qu'il existe au moins un campus
        if (empty($campusList)) {
            throw new \Exception('Aucun campus trouvé. Assurez-vous que CampusFixtures a été chargé avant UserFixtures.');
        }

        # Création d'un admin
        $admin = new User();
        $admin->setFirstname('Admin');
        $admin->setLastname('Admin');
        $admin->setUsername('Admin');
        $admin->setTelephone($faker->numerify('06 ## ## ## ##'));
        $admin->setEmail('admin@eni.fr');
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'admin'));
        $admin->setAdmin(true);
        $admin->setActive(true);
        $admin->setCampus($campusList[array_rand($campusList)]); // Campus aléatoire

        # campus en fonction des 3 campus
        $campus = $this->getReference('campus_' . $faker->numberBetween(1, 3)); // Campus aléatoire
        $admin->setCampus($campus);

        $manager->persist($admin);

        # Création de 30 participants
        for ($i = 1; $i <= 30; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName());
            $user->setLastname($faker->lastName());
            $user->setUsername($faker->userName());
            $user->setTelephone($faker->numerify('06 ## ## ## ##'));
            $user->setEmail("user$i@eni.fr");
            $user->setPassword($this->userPasswordHasher->hashPassword($user, '123456'));
            $user->setAdmin(false); // Les utilisateurs standards ne sont pas admin
            $user->setActive($faker->boolean(90)); // 90% de chances d'être actif

            # campus en fonction des 3 campus
            $campus = $this->getReference('campus_' . $faker->numberBetween(1, 3)); // Campus aléatoire
            $user->setCampus($campus);
//            // Attribuer des sorties aléatoires parmi celles créées dans SortieFixtures
//            $nbSortiesOrganisees = $faker->numberBetween(0, 3); // Entre 0 et 3 sorties organisées
//            $nbSortiesParticipees = $nbSortiesOrganisees + $faker->numberBetween(1, 5); // Toujours plus que sorties organisées
//
//            // Sorties organisées
//            for ($j = 0; $j < $nbSortiesOrganisees; $j++) {
//                $sortie = $this->getReference('sortie_' . $faker->numberBetween(1, 20));
//                $sortie->setOrganisateur($user); // Définir l'utilisateur comme organisateur
//                $user->addSortieOrganisee($sortie);
//                $user->addSortie($sortie); // Inclure dans les sorties participées
//            }
//
//            // Sorties participées
//            for ($k = 0; $k < $nbSortiesParticipees; $k++) {
//                $sortie = $this->getReference('sortie_' . $faker->numberBetween(1, 20));
//                $sortie->addParticipant($user); // Ajouter l'utilisateur comme participant
//                $user->addSortie($sortie);
//            }
            $this->addReference('user_' . $i, $user);


            $manager->persist($user);
        }

        $manager->flush();
    }

}