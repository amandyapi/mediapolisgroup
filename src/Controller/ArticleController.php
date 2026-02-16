<?php
// src/AppBundle/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;

class ArticleController extends AbstractController
{
    protected $em;
    protected $mailer;
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function getAll()
    {

        $articles = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->findArticles();
                     
        return $this->json($articles);
    }

    public function getOne($id)
    {

        $article = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->findArticle($id);
                     
        return $this->json($article);
    }
    public function updateOne(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        
        $lang = $data['lang'];
        $title = $data['title'];
        $slug = $data['slug'];
        $content = $data['content'];

        $article = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->find($id);
                     
        try {
            $article->setLang(\strtolower($lang));
            $article->setTitle(\strtolower($title));
            $article->setSlug(\strtolower($slug));
            $article->setContent(\strtolower($content));

            $entityManager->flush();
            $responseText = "L'article a été mis à jour avec succes";

            return $this->json($responseText, 200);
        } catch (\Throwable $th) {
            $responseText = "Une erreur est survenue lors de la mise à jour de l'article";
            
            return $this->json($th->getMessage(), 500);
        }
    }

    public function getAllByLang($lang)
    {

        $articles = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->findArticleByLang($lang);
                     
        return $this->json($articles);
    }

    public function save(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        //var_dump($data);die();
        
        $entityManager = $this->getDoctrine()->getManager();
        $responseText;
        
        $lang = $data['lang'];
        $title = $data['title'];
        $slug = $data['slug'];
        $content = $data['content'];

        try {
            $article = new Article();
            $article->setLang(\strtolower($lang));
            $article->setTitle(\strtolower($title));
            $article->setSlug(\strtolower($slug));
            $article->setContent(\strtolower($content));

            $entityManager->persist($article);
            $entityManager->flush();
            $responseText = "L'article a été ajouté avec succes. Id: ";

            $newId = $article->getId();

            return $this->json($responseText.$newId, 200);
        } catch (\Throwable $th) {
            $responseText = "Une erreur est survenue lors de la création de l'article";
            
            return $this->json($th->getMessage(), 500);
        }
    }
	
}
