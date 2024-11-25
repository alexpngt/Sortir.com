<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\NameFilterType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/ville')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'ville_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        EntityManagerInterface $em,
        VilleRepository $villeRepository): Response
    {
        // Créer le formulaire de filtres
        $filterForm = $this->createForm(NameFilterType::class);
        $filterForm->handleRequest($request);

        //Appliquer les filtres si le formulmaire est soumis
        $filters = $filterForm->isSubmitted() && $filterForm->isValid() ? $filterForm->getData() : [];

        // Récupération des villes en BDD
        $villes = $villeRepository->findByFilters($filters);

        // Traitement du formulaire d'ajout
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);
        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $ville = $villeForm->getData();
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'La ville a bien été ajoutée');
            return $this->redirectToRoute('ville_show');
        }
        // Autres traitements si nécessaire

        // Affichage de la vue avec les données
        return $this->render('ville/show.html.twig', [
            'villes' => $villes,
            'villeForm' => $villeForm,
            "filterForm" => $filterForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'ville_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        int $id,
        VilleRepository $villeRepository): Response
    {
        // Récupération des villes en BDD
        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);

        // Récupération de la ville à modifier
        $ville = $villeRepository->find($id);
        if (!$ville) {
            throw $this->createNotFoundException('La ville n\'existe pas');
        }

        // Traitement du formulaire de modification
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);
        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $ville = $villeForm->getData();
            $em->persist($ville);
            $em->flush();
            $this->addFlash('success', 'Modification de la ville réussie');

            return $this->redirectToRoute('ville_show');
        }

        return $this->render('ville/edit.html.twig', [
            'villeToEdit' => $ville,
            'villes' => $villes,
            'villeForm' => $villeForm,
        ]);
    }

    #[Route('/{id}/delete', name: 'ville_delete', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        int $id,
        VilleRepository $villeRepository): Response
    {
        // Récupération de la ville en BDD
        $ville = $villeRepository->find($id);
        if (!$ville) {
            throw $this->createNotFoundException('La ville n\'existe pas');
        }

        // On vérifie le token CSRF avant de supprimer la ville
        if ($this->isCsrfTokenValid('delete'.$id, $request->query->get('token'))) {
            $em->remove($ville);
            $em->flush();
            $this->addFlash('success', 'Cette ville a bien été supprimée');
        } else {
            $this->addFlash('danger', 'Impossible de supprimer la ville');
        }

        return $this->redirectToRoute('ville_show');
    }
}
