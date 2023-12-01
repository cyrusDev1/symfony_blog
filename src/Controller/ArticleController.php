<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use DateTime;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, ArticleRepository $repo): Response
    {
        $articles = $repo->findAllWithUsers();
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/new", name="post")
     */
    public function post(Request $request, EntityManagerInterface $manager,
        AuthenticationUtils $authenticationUtils): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $article->setCreatedAt(new DateTime());
        $article->setUpdatedAt(new DateTime());
        $article->setUser($this->getUser());
        $errors = $authenticationUtils->getLastAuthenticationError();
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($article);
            $manager->flush();
        }
        return $this->render('article/post.html.twig', [
            'formArticle' => $form->createView(),
            'errors' => $errors
        ]);
    }


    /**
     * @Route("/article/{id}", name="show")
     */
    public function show(Article $article, Request $request, ArticleRepository $repo): Response
    {
        $articleWithUser = $repo->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->addSelect('u')
            ->andWhere('a.id = :articleId')
            ->setParameter('articleId', $article->getId())
            ->getQuery()
            ->getOneOrNullResult();
        dump($articleWithUser);
        return $this->render('article/show.html.twig', [
            'article' => $articleWithUser,
        ]);
    }


    /**
     * @Route("/my-articles", name="my_articles")
     */
    public function myArticles(UserInterface $user, Request $request, ArticleRepository $repo): Response
    {
        
        $articles = $repo->findBy(['user' => $user]);
        return $this->render('article/my_articles.html.twig', [
            'articles' => $articles,
        ]);
    }


}
