<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('profile/recipe')]
#[IsGranted('ROLE_USER')]
class RecipeController extends AbstractController
{
    #[Route('/profile/recipe', name: 'app_recipe_index', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipeRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[Route('/profile/recipe/new', name: 'app_recipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('picture')->getData();
            if ($image) {
                $slugger = new AsciiSlugger();
                $slug = $slugger->slug($form->get('name')->getData());
                $newFilename = date('d-m-Y--h-i-s') . '.' . $slug . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('recipe_directory_assets'),
                    $newFilename
                );
                $filesystem = new Filesystem();
                $filesystem->copy(
                    $this->getParameter('recipe_directory_assets') . '/' . $newFilename,
                    $this->getParameter('recipe_directory_public') . '/' . $newFilename
                );
                $recipe->setPicture('build/img/recipe/' . $newFilename);
            }
            $recipe->setUser($this->getUser());
            $entityManager->persist($recipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recipe/new.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/profile/recipe/{id}', name: 'app_recipe_show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/profile/recipe/{id}/edit', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        if ($recipe->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
        ]);
    }

    #[Route('/profile/recipe/{id}', name: 'app_recipe_delete', methods: ['POST'])]
    public function delete(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        if ($recipe->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete'.$recipe->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($recipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
