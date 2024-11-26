<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Service\PhotoService;

#[Route('/participant')]
final class UserController extends AbstractController
{
    // Création d'un nouvel utilisateur
    #[Route('/', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface  $userAuthenticator,
        AppAuthenticator            $authenticator,
        Security                    $security,
        EntityManagerInterface      $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode le mot de passe soumis
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Assigne un rôle utilisateur par défaut
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    // Affiche le profil d'un utilisateur
    #[Route('/show/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    // Suppression d'un utilisateur
    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    // Modification du profil d'un utilisateur connecté
    #[Route('/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editProfil(
        int                         $id,
        Request                     $request,
        UserRepository              $userRepository,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $userPasswordHasher,
        PhotoService                $photoService
    ): Response {
        // Récupération de l'utilisateur via son ID
        $user = $userRepository->find($id);

        // Vérification si l'utilisateur existe
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Création et gestion du formulaire
        $userForm = $this->createForm(UserForm::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            // Gestion de l'upload de la nouvelle photo
            $photo = $userForm->get('photo')->getData();
            if ($photo) {
                // Upload du fichier via le service
                $newPhoto = $photoService->upload($photo);
                $oldPhoto = $user->getPhoto();

                // Suppression sécurisée de l'ancienne photo si différente de la photo par défaut
                if ($oldPhoto && $oldPhoto !== User::DEFAULT_PHOTO) {
                    $photoService->delete($oldPhoto);
                }

                // Mise à jour de la nouvelle photo
                $user->setPhoto($newPhoto);
            }

            // Suppression de la photo si demandé via le champ deleteImage
            if ($userForm->has('deleteImage') && $userForm['deleteImage']->getData()) {
                $currentPhoto = $user->getPhoto();
                if ($currentPhoto && $currentPhoto !== User::DEFAULT_PHOTO) {
                    $photoService->delete($currentPhoto);
                }
                $user->setPhoto(User::DEFAULT_PHOTO);
            }

            // Mise à jour du mot de passe si un nouveau mot de passe est soumis
            $plainPassword = $userForm->get('plainPassword')->getData();
            if ($plainPassword) {
                // Encode et met à jour le mot de passe
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            // Sauvegarde des modifications dans la base de données
            $em->flush();

            // Ajout d'un message de succès
            $this->addFlash('success', 'Votre profil a été mis à jour.');

            // Redirection après modification
            return $this->redirectToRoute('user_edit', ['id' => $id]);
        }

        // Renvoie le formulaire pour l'afficher dans Twig
        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'form' => $userForm->createView(),
        ]);
    }
}
