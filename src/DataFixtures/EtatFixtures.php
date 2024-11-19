<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

// Liste des états
        $etats = [
            'Créée',
            'Ouverte',
            'Clôturée',
            'Activité en cours',
            'Passée',
            'Annulée',
        ];

// Créer et persister chaque état
        foreach ($etats as $key => $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);

            $manager->persist($etat);

// Ajouter une référence pour pouvoir y accéder dans d'autres fixtures
            $this->addReference('etat_' . $key, $etat);
        }

        $manager->flush();
    }
}