<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Form\CommentType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'recipes' => $recipeRepository->findAll(),
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
