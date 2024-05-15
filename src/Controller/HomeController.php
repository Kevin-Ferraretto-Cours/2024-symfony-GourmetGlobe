<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Form\CommentType;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request, RecipeRepository $recipeRepository, IngredientRepository $ingredientRepository): Response
    {
        $user = $this->getUser();
        $allRecipes = $recipeRepository->findAll();
        $selectedIngredientIds = [];

        // Récupérer les IDs des ingrédients sélectionnés
        foreach ($ingredientRepository->findAll() as $ingredient) {
            $inputName = 'filter-ingredient-' . $ingredient->getId();
            if ($request->request->get($inputName)) {
                $selectedIngredientIds[] = $ingredient->getId();
            }
        }

        $favoriteRecipes = [];
        $nonFavoriteRecipes = [];

        // Séparer les recettes favorites des non favorites
        foreach ($allRecipes as $recipe) {
            if ($user !== null && $user->getFavorite()->contains($recipe)) {
                $favoriteRecipes[] = $recipe;
            } else {
                $nonFavoriteRecipes[] = $recipe;
            }
        }

        // Si aucun ingrédient n'est sélectionné, afficher les recettes favorites en premier
        if (empty($selectedIngredientIds)) {
            return $this->render('home/index.html.twig', [
                'recipes' => array_merge($favoriteRecipes, $nonFavoriteRecipes),
                'ingredients' => $ingredientRepository->findAll(),
                'selectedIngredientIds' => $selectedIngredientIds
            ]);
        }

        // Filtrer et trier les recettes favorites en fonction des ingrédients sélectionnés
        $filteredFavoriteRecipes = [];
        foreach ($favoriteRecipes as $recipe) {
            $recipeIngredients = $recipe->getIngredient()->toArray();
            $recipeIngredientIds = array_map(function($ingredient) {
                return $ingredient->getId();
            }, $recipeIngredients);
            if (empty(array_diff($selectedIngredientIds, $recipeIngredientIds))) {
                $filteredFavoriteRecipes[] = $recipe;
            }
        }

        // Filtrer et trier les recettes non favorites en fonction des ingrédients sélectionnés
        $filteredNonFavoriteRecipes = [];
        foreach ($nonFavoriteRecipes as $recipe) {
            $recipeIngredients = $recipe->getIngredient()->toArray();
            $recipeIngredientIds = array_map(function($ingredient) {
                return $ingredient->getId();
            }, $recipeIngredients);
            $matchingIngredientCount = count(array_intersect($selectedIngredientIds, $recipeIngredientIds));
            if ($matchingIngredientCount > 0) {
                $filteredNonFavoriteRecipes[$recipe->getId()] = $matchingIngredientCount;
            }
        }

        // Trier les recettes non favorites par le nombre d'ingrédients correspondant à la recherche
        arsort($filteredNonFavoriteRecipes);

        // Réorganiser les recettes non favorites par ordre de tri
        $sortedNonFavoriteRecipes = [];
        foreach ($filteredNonFavoriteRecipes as $recipeId => $matchingIngredientCount) {
            $sortedNonFavoriteRecipes[] = $recipeRepository->find($recipeId);
        }

        // Fusionner les recettes favorites filtrées et les recettes non favorites triées
        $filteredRecipes = array_merge($filteredFavoriteRecipes, $sortedNonFavoriteRecipes);

        return $this->render('home/index.html.twig', [
            'recipes' => $filteredRecipes,
            'ingredients' => $ingredientRepository->findAll(),
            'selectedIngredientIds' => $selectedIngredientIds
        ]);
    }

    #[Route('/recipe/{id}', name: 'app_home_recipe', methods: ['GET', 'POST'])]
    public function recipe(Recipe $recipe, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $comment->setDate(new \DateTime());
            $recipe->addComment($comment);
            $entityManager->persist($comment);
            $entityManager->persist($recipe);
            $entityManager->flush();


            return $this->redirectToRoute('app_home_recipe', ['id' => $recipe->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('home/show.html.twig', [
            'recipe' => $recipe,
            'comments' => $recipe->getComment(),
            'form' => $form,
        ]);
    }

    #[Route('/favorite/{id}', name: 'app_home_recipe_favorite', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function favorite(Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getFavorite()->contains($recipe)) {
            $this->getUser()->removeFavorite($recipe);
        } else {
            $this->getUser()->addFavorite($recipe);
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_home_recipe', ['id' => $recipe->getId()], Response::HTTP_SEE_OTHER);
    }
}
