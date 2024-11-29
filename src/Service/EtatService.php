<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;

// Classe qui gère le changement d'état d'une sortie
class EtatService
{

    public function __construct()
    {
    }

    // Fonction qui retourne l'état recherché
    public function getEtatDB(string $nomEtat, EtatRepository $etatRepository) : ?Etat
    {
        try {
            $etat = $etatRepository->findOneBy(['libelle' => $nomEtat]);
        } catch (\Exception $exception) {
            throw new \Exception('Erreur : Etat non trouvé', 404, $exception);
        }
        return $etat;
    }


    public function updateEtat(Sortie $sortie, EtatRepository $etatRepository, EntityManagerInterface $em)
    {
        $etat = $sortie->getEtat();
        $dateTimeNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $dateTimeNow = $dateTimeNow->modify('+1 hour');
        /*
         * Logique à appliquer à un Etat "Ouverte"
         * - Si le nombre d'inscrits maximum est atteint
         * ou date > dateLimitInscription -> "Clôturée"
         * - Si date = date de l'activité -> "Activité en cours"
         */
        if ($etat->getLibelle() === "Ouverte" || $etat->getLibelle() === "Créée") {
            if ($sortie->getParticipants()->count() === $sortie->getNbMaxInscription()
                || $dateTimeNow >= $sortie->getDateLimitInscription())
            {
                $sortie->setEtat($this->getEtatDB("Clôturée", $etatRepository));
            }
            if ($dateTimeNow > $sortie->getDateStart())
            {
                $sortie->setEtat($this->getEtatDB("Activité en cours", $etatRepository));
            }
        }

        /*
         * Logique à appliquer à un Etat "Clôturée"
         * - Si le nombre d'inscrits < nbLimitInscription
         * et que date < dateLimitInscription -> Ouverte
         * - Si date = date de l'activité -> "Activité en cours"
         */
        if ($etat->getLibelle() === "Clôturée") {
            if ($sortie->getParticipants()->count() < $sortie->getNbMaxInscription()
            && $dateTimeNow <= $sortie->getDateLimitInscription())
            {
                $sortie->setEtat($this->getEtatDB("Ouverte", $etatRepository));
            }
            if ($dateTimeNow >= $sortie->getDateStart())
            {
                $sortie->setEtat($this->getEtatDB("Activité en cours", $etatRepository));
            }
        }

        /*
         * Logique à appliquer à un Etat "Activité en cours"
         * - Si date >= dateStart + duration -> "Passée"
         */
        if ($etat->getLibelle() === "Activité en cours") {
            $datePassee = $sortie->getDateStart()->add(new \DateInterval('PT'.$sortie->getDuration().'M'));
            //$datePassee = $sortie->getDateStart()->modify('+1 hour');
            if ($dateTimeNow >= $datePassee)
            {
                $sortie->setEtat($this->getEtatDB("Passée", $etatRepository));
            }
        }

        $em->persist($sortie);
        $em->flush();
    }
}