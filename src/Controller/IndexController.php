<?php
namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="article_list")
     */
    public function home(ManagerRegistry $doctrine): Response
    {
        // Fetch all articles from the database using the Article repository
        $articles = $doctrine->getRepository(Article::class)->findAll();

        // Render the articles in the view
        return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/article/save", name="article_save", methods={"GET"})
     */
    public function save(ManagerRegistry $doctrine): Response
    {
        // Access the entity manager
        $entityManager = $doctrine->getManager();

        // Create a new article object and set its properties
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000);

        // Persist the new article to the database
        $entityManager->persist($article);
        $entityManager->flush();

        // Return a response to confirm the article was saved
        return new Response('Article enregistré avec id ' . $article->getId());
    }

    /**
     * @Route("/article/new", name="new_article", methods={"GET", "POST"})
     */
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $article = new Article();

        // Create the form for the new article
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->getForm();

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the new article to the database
            $entityManager = $doctrine->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            // Redirect to the article list page
            return $this->redirectToRoute('article_list');
        }

        // Render the form view
        return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/article/{id}", name="article_show", requirements={"id"="\d+"})
     */
    public function show(ManagerRegistry $doctrine, $id): Response
    {
        // Fetch the article by its ID from the database
        $article = $doctrine->getRepository(Article::class)->find($id);

        // Check if the article exists
        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        // Render the article details in the view
        return $this->render('articles/show.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/article/edit/{id}", name="edit_article", methods={"GET", "POST"})
     */
    public function edit(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        // Fetch the article by its ID
        $article = $doctrine->getRepository(Article::class)->find($id);

        // Check if the article exists
        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        // Create the form for editing the article
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier'])
            ->getForm();

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated article to the database
            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            // Redirect to the article list page
            return $this->redirectToRoute('article_list');
        }

        // Render the form view for editing
        return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/article/delete/{id}", name="delete_article", methods={"POST"})
     */
    public function delete(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        // Check if the request is a POST request
        if ($request->isMethod('POST')) {
            $article = $doctrine->getRepository(Article::class)->find($id);

            // Check if the article exists
            if (!$article) {
                throw $this->createNotFoundException('The article does not exist');
            }

            // Access the entity manager and remove the article
            $entityManager = $doctrine->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            // Redirect to the article list page
            return $this->redirectToRoute('article_list');
        }

        // If the request is not POST, you might want to return a response indicating that
        return new Response('Method not allowed', Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
