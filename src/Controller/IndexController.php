<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Entity\Article;
use App\Form\CategoryType;
use App\Form\ArticleType;
use App\Entity\PropertySearch;
use App\Form\PropertySearchType;
use App\Entity\PriceSearch; // Ensure you import the PriceSearch class
use App\Form\PriceSearchType; // Ensure you import the PriceSearchType class
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="article_list")
     */
    public function home(Request $request): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $propertySearch);
        $form->handleRequest($request);
        
        // Initialize the articles array
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        
        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom();

            if (!empty($nom)) {
                $articles = $this->entityManager->getRepository(Article::class)->findBy(['nom' => $nom]);
            } 
        }

        return $this->render('articles/index.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/save", name="article_save")
     */
    public function save(): Response
    {
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000.00); // Ensure to set as a decimal

        $this->entityManager->persist($article);
        $this->entityManager->flush();

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
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_show")
     */
    public function show(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article/edit/{id}", name="edit_article", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article, // Added for context
        ]);
    }

    /**
     * @Route("/article/delete/{id}", name="delete_article", methods={"DELETE"})
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Article deleted successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/category/newCat", name="new_category", methods={"GET", "POST"})
     */
    public function newCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/art_cat/", name="article_par_cat", methods={"GET", "POST"})
     */
    public function articlesParCategorie(Request $request): Response
    {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);
        
        $articles = [];
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();

            if ($category) { // Check if category is not null
                $articles = $category->getArticles(); // Assuming getArticles() returns an iterable of articles
            } else {
                $articles = $this->entityManager->getRepository(Article::class)->findAll(); // Fetch all articles if no category is selected
            }
        }

        return $this->render('articles/articlesParCategorie.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/art_prix/", name="article_par_prix", methods={"GET", "POST"})
     */
    public function articlesParPrix(Request $request): Response
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $priceSearch);
        $form->handleRequest($request);

        $articles = []; // Initialize articles array
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();
            $articles = $this->entityManager
                             ->getRepository(Article::class)
                             ->findByPriceRange($minPrice, $maxPrice);
        }

        return $this->render('articles/articlesParPrix.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }
}
