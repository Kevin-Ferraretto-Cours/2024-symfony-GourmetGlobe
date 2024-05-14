<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'recipes' => $recipeRepository->findAll(),
        ]);
    }

    #[Route('/recipe/{id}', name: 'app_home_recipe')]
    public function recipe(Recipe $recipe): Response
    {
        return $this->render('home/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
