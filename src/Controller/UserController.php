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

    // affiche la page d'un participant depuis un autre participant ..
    #[Route('/show/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
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

    // Mon profil
    #[Route('/edit/{id}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editProfil(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $userRepository->find($id); // Récup° du user via son id

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Créer et gérer le formulaire
        $userForm = $this->createForm(UserForm::class, $user);
        $userForm->handleRequest($request);


        if ($userForm->isSubmitted()) {
            if ($userForm->isValid()) {
                // TODO : ajouter : && $this->getUser() pour sécuriser et soumettre que si l'utilisateur est co
                // TODO : Dans twig, faire en sorte d'afficher le formulaire que si l'utilisateur est connecté

                /** @var string $plainPassword */
                $plainPassword = $userForm->get('plainPassword')->getData();
                if ($plainPassword) {
                    // Encode le mot de passe uniquement si un nouveau mot de passe est fourni
                    $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                }
                $em->flush(); // Enregistrer les modifications

                // Ajouter un message flash de succès
                $this->addFlash('success', 'Votre profil a été mis à jour.');

                // Rediriger vers la même page pour éviter la resoumission
                return $this->redirectToRoute('user_edit', ['id' => $id]);
            } else {
                // Si le formulaire contient des erreurs
                // test à suppr
                $this->addFlash('danger', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        }

        // Renvoyer le formulaire à twig
        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'form' => $userForm->createView(),
        ]);


    }
}
