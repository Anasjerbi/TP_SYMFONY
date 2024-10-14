<?php

namespace App\Controller;

use App\Form\ArticleType;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends AbstractController
{
    private EntityManagerInterface $entityManager; // Use typed property

    // Injecting EntityManagerInterface via constructor
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="article_list")
     */
    public function home(): Response
    {
        // Fetch all articles from the Article repository
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/article/save", name="article_save")
     */
    public function save(): Response
    {
        // Create a new Article and set its properties
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000.00); // Ensure to set as a decimal

        // Persist the article to the database
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // Return a response with the article's ID
        return new Response('Article enregistré avec id ' . $article->getId());
    }

    /**
     * @Route("/article/new", name="new_article", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new article
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            // Redirect to the article list after saving
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/article/{id}", name="article_show")
     */
    public function show(int $id): Response
    {
        // Fetch the article by ID from the repository
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        // Check if the article exists
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('articles/show.html.twig', ['article' => $article]);
    }

  /**
 * @Route("/article/edit/{id}", name="edit_article", methods={"GET", "POST"})
 */
public function edit(Request $request, int $id): Response
{
    // Fetch the article by ID
    $article = $this->entityManager->getRepository(Article::class)->find($id);

    // Check if the article exists
    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Create and handle the form
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Save the updated article to the database
        $this->entityManager->flush();

        // Redirect to the article list after updating
        return $this->redirectToRoute('article_list');
    }

    return $this->render('articles/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}


    /**
     * @Route("/article/delete/{id}", name="delete_article", methods={"DELETE"})
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        // Fetch the article by ID from the repository
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        // Check if the article exists
        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        // Remove the article
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        // Return a JSON response indicating success
        return new JsonResponse(['message' => 'Article deleted successfully'], Response::HTTP_OK);
    }
}
