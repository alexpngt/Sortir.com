<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CancelType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
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
            $sortie->addParticipant($sortie->getOrganisateur());
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
        // Si l'utilisateur qui essaie d'acceder à cette sortie n'est pas l'organisateur
        // ou un admin, on le renvoie sur la page d'acceuil
        if ($sortie->getOrganisateur()->getId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }

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

    #[Route('/{id}/publish', name: 'sortie_publish', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function publish(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ): Response
    {
        // Si l'utilisateur qui essaie d'acceder à cette sortie n'est pas l'organisateur
        // ou un admin, on le renvoie sur la page d'acceuil
        if ($sortie->getOrganisateur()->getId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: 'sortie_cancel', methods: ['GET', 'POST'])]
    public function cancel(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository): Response
    {
        // Si l'utilisateur qui essaie d'acceder à cette sortie n'est pas l'organisateur
        // ou un admin, on le renvoie sur la page d'acceuil
        if ($sortie->getOrganisateur()->getId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }

        $cancelForm = $this->createForm(CancelType::class);
        $cancelForm->handleRequest($request);

        if ($cancelForm->isSubmitted() && $cancelForm->isValid()) {
            $sortie->setMotifAnnulation($cancelForm->get('motif')->getData());
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'cancelForm' => $cancelForm,
        ]);
    }

    #[Route('/{id}', name: 'sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->query->get('token'))) {
            $entityManager->flush();
            $this->addFlash('success', 'Cette sortie a bien été annulée');
        } else {
            $this->addFlash('danger', "Impossible d'annuler la sortie");
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/book', name: 'sortie_book', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function book(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) : Response
    {
        if ($this->getUser()->getId() !== $sortie->getOrganisateur()->getId()) {
            $sortie->addParticipant($userRepository->findOneBy(['id' => $this->getUser()->getId()]));
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous vous êtes bien inscrit à cette sortie');
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/desist', name: 'sortie_desist', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function desist(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) : Response
    {
        if ($this->getUser()->getId() !== $sortie->getOrganisateur()->getId()) {
            $sortie->removeParticipant($userRepository->findOneBy(['id' => $this->getUser()->getId()]));
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous vous êtes bien désisté de cette sortie');
        } else {
            $this->addFlash('warning', "Vous ne pouvez pas vous désister d'une sortie que vous organisez");
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }
}
