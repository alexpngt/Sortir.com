<?php

namespace App\Controller;

use App\Form\SortieFilterType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Service\EtatService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
class MainController extends AbstractController
{

    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(Request $request, SortieRepository $sortieRepository, PaginatorInterface $paginator, EtatRepository $etatRepository, EtatService $etatService, EntityManagerInterface $entityManager): Response
    {
        //Créer le formulaire de filtres
        $form = $this->createForm(SortieFilterType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
        $form ->handleRequest($request);

        //Appliquer les filtres si le formulaire a été soumis
        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];
        //Récupérer l'utilisateur connecté
        $user = $this->getUser();

        //Mettre à jour les Etats
        $sortiesMAJ = $sortieRepository->findAllSorties();
        foreach ($sortiesMAJ as $sortie) {
            $etatService->updateEtat($sortie, $etatRepository, $entityManager);
        }

        //Obtenir la requête sans exécuter les résultats
        $query = $sortieRepository->findByFilters($filters, $user);

        //Paginer les résultats
        $sorties = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('main/home.html.twig',[
            'form' => $form->createView(),
            'sorties' => $sorties,
        ]);
    }


}
