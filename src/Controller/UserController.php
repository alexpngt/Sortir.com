<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Form\UserType;
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

#[Route('/participant')]
final class UserController extends AbstractController
{

    #[Route('/', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator,
                        AppAuthenticator $authenticator, Security $security,EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ));

            // assigne un rôle user par défaut lors de la création d'un utilisateur
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

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: '', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_show', ['id' => $user->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editProfil(int $id,Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $userRepository->find($id); // Récup° du user via son id

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Créer et gérer le formulaire
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $em->flush(); // Enregistrer les modifications

            // Ajouter un message flash de succès
            $this->addFlash('success', 'Votre profil a été mis à jour.');

            // Rediriger vers la même page pour éviter la resoumission
            return $this->redirectToRoute('user_edit', ['id' => $id]);
        }

        // Renvoyer le formulaire à twig
        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);



}}
