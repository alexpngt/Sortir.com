<?php

namespace App\Controller;

use App\Form\SortieFilterType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(Request $request, SortieRepository $sortieRepository): Response
    {
        //Créer le formulaire de filtres
        $form = $this->createForm(SortieFilterType::class);
        $form ->handleRequest($request);

        //Appliquer les filtres si le formulaire a été soumis
        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];
        //Récupérer l'utilisateur connecté
        $user = $this->getUser();
        //Récupérer les sorties en fonction des filtres
        $sorties = $sortieRepository->findByFilters($filters, $user);

        return $this->render('main/home.html.twig',[
            'form' => $form->createView(),
            'sorties' => $sorties,
        ]);
    }
}
