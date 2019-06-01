<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager; /* use le manager */
use Symfony\Component\HttpFoundation\Request; /* Pour utiliser l'object Request */


class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog',
        ]);
    }

    /** 
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
    */
    public function form(Article $article = null, Request $request, ObjectManager $manager)
    {
        if(!$article)
        {
            $article = new Article();
        }

        /* Création du formulaire avec la fonction createFormBuilder et on demande d'ajouter les champs dont on a besoin et à la fin getForm pour qu'il nous donne le résultat */
        /* $form ne peut pas être affiché comme l'objet est complexe avec beaucoup de méthode etc */
        /* la fonction add peut prendre en second paramètre le type de champs de notre formulaire par exemple ->add('title', TextArea::class) (tout est sur la doc) */
        /* add prend un 3ème paramètre qui est les op^tions html de notre champs ex ci dessous (attr pour tout ce qui est param html) */
        /* On utlise pas cette méthode pour alléger le code, le tableau attr on préfère le mttre dans notre template */
        /*$form = $this->createFormBuilder($article)
                    ->add('title')
                    ->add('content')
                    ->add('image')
                    ->getForm();*/

        /* form via la console et createform prend en paramètre le nom du form ainsi que l'entité à lier à ce form */
        /* l'avantage c'est qu'avec cette méthode on peut appeler ce form où on veut l'appeller dans les méthode dde nos controllers */
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            /* Je set une date que si l'article n'existe pas sinon pas besoin comme ce sera un edit */
            if(!$article->getId())
            {
                $article->setCreatedAt(new \DateTime());
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        /* On envoie à twig 'form' qui contiendra une vue de notre formulaire grace à createView() 
        les autres paramètres sont pour savoir si l'article existe ou pas pour afficher différentes choses sur notre vue */
        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null,
            'titleMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show", requirements={"id" = "\d+"})
     */
    public function show(Article $article, Comment $comment, Request $request, ObjectManager $manager, $id)
    {      
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);   
        
        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setCreatedAt(new \Datetime())
                    ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/show.html.twig',[
            'article' => $article,
            'formComment' => $form->createView()
        ]);
    }
}