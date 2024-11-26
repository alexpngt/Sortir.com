<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

// Classe finale PhotoService qui gère le téléchargement de fichiers
final class PhotoService
{
    // Constructeur qui initialise le répertoire cible pour le téléchargement
    public function __construct(private readonly string $targetDirectory)
    {
    }

    // télécharge une photo et retourne un nom unique
    public function upload(UploadedFile $photo): string
    {
        // Génère un nom de fichier unique avec l'extension d'origine du fichier
        $photoName = uniqid() . '.' . $photo->guessExtension();

        try {
            // Déplace le fichier vers le répertoire cible avec le nom unique généré
            $photo->move($this->getTargetDirectory(), $photoName);
        } catch (FileException $e) {
            throw new FileException('Une erreur est survenue lors du téléchargement de la photo.', 0, $e);
        }
        return $photoName;
    }


    public function delete(?string $photoName): void
    {
        if ($photoName && $photoName !== User::DEFAULT_PHOTO) {
            $filePath = $this->getTargetDirectory() . '/' . $photoName; //         // Construit le chemin complet du fichier dans le répertoire cible

            if (file_exists($filePath)) {
                unlink($filePath); // Supprime le fichier
            }
        }
    }

    // Fonction pour obtenir le répertoire cible où les fichiers seront stockés
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
