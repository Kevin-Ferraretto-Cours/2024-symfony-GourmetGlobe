<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Form\CommentType;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
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

        // Récupére les ID des ingrédients sélectionnés
        foreach ($ingredientRepository->findAll() as $ingredient) {
            $inputName = 'filter-ingredient-' . $ingredient->getId();
            if ($request->request->get($inputName)) {
                $selectedIngredientIds[] = $ingredient->getId();
            }
        }

        $favoriteRecipes = [];
        $nonFavoriteRecipes = [];

        // Sépare les recettes favoris des non favoris
        foreach ($allRecipes as $recipe) {
            if ($user !== null && $user->getFavorite()->contains($recipe)) {
                $favoriteRecipes[] = $recipe;
            } else {
                $nonFavoriteRecipes[] = $recipe;
            }
        }

        // Si aucun ingrédient est sélectionné, aucun filtre renvoie de la vue
        if (empty($selectedIngredientIds)) {
            return $this->render('home/index.html.twig', [
                'recipes' => array_merge($favoriteRecipes, $nonFavoriteRecipes),
                'ingredients' => $ingredientRepository->findAll(),
                'selectedIngredientIds' => $selectedIngredientIds
            ]);
        }

        // Filtre les recettes favoris qui contien au moins un ingrédient sélectionné
        $filteredFavoriteRecipes = array_filter($favoriteRecipes, function($recipe) use ($selectedIngredientIds) {
            $recipeIngredients = $recipe->getIngredient()->toArray();
            $recipeIngredientIds = array_map(function($ingredient) {
                return $ingredient->getId();
            }, $recipeIngredients);
            return !empty(array_intersect($selectedIngredientIds, $recipeIngredientIds));
        });

        // Filtre les recettes non favoris qui contiennent au moins un ingrédient sélectionné
        $filteredNonFavoriteRecipes = array_filter($nonFavoriteRecipes, function($recipe) use ($selectedIngredientIds) {
            $recipeIngredients = $recipe->getIngredient()->toArray();
            $recipeIngredientIds = array_map(function($ingredient) {
                return $ingredient->getId();
            }, $recipeIngredients);
            return !empty(array_intersect($selectedIngredientIds, $recipeIngredientIds));
        });

        // Trie les recettes non favoris avec le nombre d'ingrédients correspondant à la recherche
        usort($filteredNonFavoriteRecipes, function($a, $b) use ($selectedIngredientIds) {
            $countA = count(array_intersect($selectedIngredientIds, $a->getIngredient()->map(function($ingredient) {
                return $ingredient->getId();
            })->toArray()));
            $countB = count(array_intersect($selectedIngredientIds, $b->getIngredient()->map(function($ingredient) {
                return $ingredient->getId();
            })->toArray()));
            return $countB <=> $countA;
        });

        // Fusion des recettes favoris filtrées avec les recettes non favoris filtrées
        $filteredRecipes = array_merge($filteredFavoriteRecipes, $filteredNonFavoriteRecipes);

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

        return $this->render('home/recipe.html.twig', [
            'recipe' => $recipe,
            'comments' => $recipe->getComment(),
            'form' => $form,
        ]);
    }

    #[Route('/ingredient/{id}', name: 'app_home_ingredient')]
    public function ingredient(Ingredient $ingredient): Response
    {
        return $this->render('home/ingredient.html.twig', [
            'ingredient' => $ingredient,
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

    #[Route('/profile/favorite', name: 'app_home_recipe_favorite_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function showFavorite(UserRepository $userRepository): Response
    {

        return $this->render('home/favorite.html.twig', [
            'recipes' => $this->getUser()->getFavorite(),
        ]);
    }
}
