<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\NameFilterType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campus')]
class CampusController extends AbstractController
{
    #[Route('/', name: 'campus_show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        EntityManagerInterface $em,
        CampusRepository $campusRepository
    ): Response
    {
        // Créer le formulaire de filtres
        $filterForm = $this->createForm(NameFilterType::class);
        $filterForm->handleRequest($request);

        //Appliquer les filtres si le formulmaire est soumis
        $filters = $filterForm->isSubmitted() && $filterForm->isValid() ? $filterForm->getData() : [];

        // Récupération des campus en BDD
        $campusLst = $campusRepository->findByFilters($filters);

        // Traitement du formulaire d'ajout (Dernière ligne de la table sur la vue)
        $campus = new Campus();
        $formCampus = $this->createForm(CampusType::class, $campus);
        $formCampus->handleRequest($request);
        if ($formCampus->isSubmitted() && $formCampus->isValid()) {
            $campus = $formCampus->getData();
            $em->persist($campus);
            $em->flush();

            $this->addFlash("success", "Le campus a bien été ajouté");
            return $this->redirectToRoute('campus_show');
        }
        // Autres traitements si nécessaire

        // Affichage de la vue avec les attributs nécessaires
        return $this->render('campus/show.html.twig', [
            "campusLst" => $campusLst,
            "formCampus" => $formCampus,
            "filterForm" => $filterForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'campus_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        int $id,
        CampusRepository $campusRepository
    ): Response
    {
        // Récupération des campus en BDD
        $campusLst = $campusRepository->findBy([], ['name' => 'ASC']);

        // Récupération du campus à modifier
        $campus = $campusRepository->find($id);
        if (!$campus) {
            throw $this->createNotFoundException('Campus introuvable');
        }

        // Traitement du formulaire de modification
        $formCampus = $this->createForm(CampusType::class, $campus);
        $formCampus->handleRequest($request);
        if ($formCampus->isSubmitted() && $formCampus->isValid()) {
            $campus = $formCampus->getData();
            $em->persist($campus);
            $em->flush();
            $this->addFlash("success", "Modification du campus reussie");

            return $this->redirectToRoute('campus_show');
        }

        return $this->render('campus/edit.html.twig', [
            'campusToEdit' => $campus,
            'campusLst' => $campusLst,
            'formCampus' => $formCampus,
        ]);
    }

    #[Route('/{id}/delete', name: 'campus_delete', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        int $id,
        CampusRepository $campusRepository
    ): Response
    {
        // Recupération du campus dans la BDD
        $campus = $campusRepository->find($id);
        // S'il n'est pas trouvé, on renvoie une exception
        if (!$campus) {
            throw $this->createNotFoundException('Campus introuvable');
        }

        // On vérifie le token CSRF avant de supprimer le campus
        if ($this->isCsrfTokenValid('delete'.$id, $request->query->get('token'))) {
            $em->remove($campus);
            $em->flush();
            $this->addFlash('success', 'Ce campus a bien été supprimé');
        }
        else {
            $this->addFlash('danger', 'Impossible de supprimer ce campus');
        }

        return $this->redirectToRoute('campus_show');
    }
}
