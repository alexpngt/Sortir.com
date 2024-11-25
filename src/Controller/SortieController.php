<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{

    #[Route('/new', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setOrganisateur($this->getUser());
            $sortie->setCampusOrganisateur($sortieForm->get('campusOrganisateur')->getData());
            $sortie->setLieu($sortieForm->get('lieu')->getData());
            // Condition pour publier ou non la sortie
            if ($sortieForm->get('publish')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            } else {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'sortieForm' => $sortieForm,
        ]);
    }

    #[Route('/{id}', name: 'sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'sortie_edit', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository): Response
    {
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            if ($sortieForm->get('publish')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            } else {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'sortieForm' => $sortieForm,
        ]);
    }

    #[Route('/{id}', name: 'sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->query->get('token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Cette sortie a bien été annulée');
        } else {
            $this->addFlash('danger', "Impossible d'annuler la sortie");
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }
}
