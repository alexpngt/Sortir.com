<?php

namespace App\Controller;

use App\Form\SortieFilterType;
use App\Repository\SortieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(Request $request, SortieRepository $sortieRepository, PaginatorInterface $paginator): Response
    {
        //Créer le formulaire de filtres
        $form = $this->createForm(SortieFilterType::class);
        $form ->handleRequest($request);

        //Appliquer les filtres si le formulaire a été soumis
        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];
        //Récupérer l'utilisateur connecté
        $user = $this->getUser();

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
