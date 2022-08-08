<?php
// src/AppBundle/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Picture;
use App\Entity\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;

class AdminController extends AbstractController
{
    protected $em;
    protected $mailer;
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function mailList(SessionInterface $session)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        if($user['role'] == '3') // Role 3 equal to article manager
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Mail::class);

        $mails = $this->getDoctrine()
                      ->getRepository(Mail::class)
                      ->findMails();

        $template = 'admin/mail/mails.html.twig';            
        return $this->render($template, [
            'mails' => $mails,
            'user' => $user,
        ]); 
    }

    public function mailInfo($id, SessionInterface $session)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        
        if($user['role'] == '3') // Role 3 equal to article manager
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Mail::class);

        $mail = $this->getDoctrine()
                      ->getRepository(Mail::class)
                      ->findMail($id);

        //var_dump($mail);die();
        $bgUrl = 'https://images.unsplash.com/photo-1531512073830-ba890ca4eba2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80';

        $template = 'admin/mail/mail.html.twig';            
        return $this->render($template, [
            'mail' => $mail,
            'user' => $user,
            'bgUrl' => $bgUrl
        ]); 
    }


    public function articleList(SessionInterface $session)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Article::class);

        $articles = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->findAllArticles();

        //var_dump($articles[0]);die();

        $template = 'admin/articles/articles-list.html.twig';            
        return $this->render($template, [
            'articles' => $articles,
            'user' => $user
        ]); 
    }


    public function articleInfo($id, SessionInterface $session)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Article::class);

        $article = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->customFindArticle($id);
        
        $serverName = $_SERVER['SERVER_NAME'];
        $bgUrl = "http://".$serverName."/assets/uploads/articles/".$article["picture"];

        $template = 'admin/articles/article.html.twig';            
        return $this->render($template, [
            'article' => $article,
            'user' => $user,
            'bgUrl' => $bgUrl
        ]); 
    }

    public function articleEdit($id, SessionInterface $session, Request $request)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Article::class);
        
        $article = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->customFindArticle($id);

        $a = $this->getDoctrine()
                  ->getRepository(Article::class)
                  ->find($id);
        //var_dump($article);die();
        
        $serverName = $_SERVER['SERVER_NAME'];
        $bgUrl = "http://".$serverName."/assets/uploads/articles/".$article["picture"];

        $repository = $this->getDoctrine()->getRepository(Article::class);
        if(!empty($request->request->get('save')))
        {
            try 
            {
                $entityManager = $this->getDoctrine()->getManager();

                $title = $request->request->get('title');
                $content = $request->request->get('content');

                $a->setTitle($title);
                $a->setContent($content);
                $entityManager->flush(); 
                
                return $this->redirectToRoute('admin_article_info', [
                    'id' => $id
                ]);
            } catch (\Throwable $th) {
                return $this->redirectToRoute('admin_article_edit', [
                    'id' => $id
                ]);
            }
        }

        $template = 'admin/articles/article-edit.html.twig';            
        return $this->render($template, [
            'article' => $article,
            'user' => $user,
            'bgUrl' => $bgUrl
        ]); 
    }

    public function articleAdd(SessionInterface $session, Request $request)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Article::class);
        
        $bgUrl = "http://newsite.gfi-co.net/assets/images/page-titles/13.jpg";

        $repository = $this->getDoctrine()->getRepository(Article::class);
        if(!empty($request->request->get('save')))
        {
            try 
            {
                $entityManager = $this->getDoctrine()->getManager();

                $title = $request->request->get('title');
                $content = $request->request->get('content');
                $lang = $request->request->get('lang');
                $slug = $this->slugify($title);

                $article = new Article();
                $article->setUser($user['id']);
                $article->setTitle($title);
                $article->setCategory("GFI-CO");
                $article->setContent($content);
                $article->setSlug($slug);
                $article->setLang($lang);

                $target_dir = "assets/uploads/articles/";
                $files = $_FILES;
                $attribute = "article_img";

                if($files[$attribute]['name'] != "")
                {
                    $img = $this->upload($attribute, $target_dir, $files);
                    $article->setPicture($img);
                }

                $entityManager->persist($article);
                $entityManager->flush(); 
                
                return $this->redirectToRoute('admin_article_info', [
                    'id' => $article->getId()
                ]);
            } catch (\Throwable $th) {
                return $this->redirectToRoute('admin_article_new');
            }
        }

        $template = 'admin/articles/article-new.html.twig';            
        return $this->render($template, [
            'user' => $user,
            'bgUrl' => $bgUrl
        ]);
    }

    public function articleDelete($id, SessionInterface $session, Request $request)
    {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('login');
        }

        $repository = $this->getDoctrine()->getRepository(Article::class);
        $entityManager = $this->getDoctrine()->getManager();
        
        try 
        {
            $a = $this->getDoctrine()
                      ->getRepository(Article::class)
                      ->find($id);
            //var_dump($a->getTitle());die();
            $entityManager->remove($a);
            $entityManager->flush();
                
            return $this->redirectToRoute('admin_articles');
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            var_dump($message);die();
            //return $this->redirectToRoute('admin_articles');
        }
    }

    public function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function upload($attribute, $target_dir, $files)
    {
        $target_file = $target_dir . basename($files[$attribute]["name"]);$data = [];
        
        $check = getimagesize($files[$attribute]["tmp_name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $now = new \DateTime('NOW');
        //$timestamp = $now->getTimestamp();
        $code = $now->format('YmdHisu');
        $picture = $code.rand(1000,9000).".".$imageFileType;
        $new_file_name = $target_dir.$picture;
        
        $errorMsg = [];
        $result = null;
        $returnStatement = false;
        // Check if image file is a actual image or fake image
        if ($check !== false) {
            $errorMsg[] = "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $errorMsg[] = "File is not an image.";
            $uploadOk = 0;
        }
        // Check if file already exists
        
        // Check file size
        if ($files[$attribute]["size"] > 500000) {
            $errorMsg[] = "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        // Allow certain file formats
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            $errorMsg[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $errorMsg[] = "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($files[$attribute]["tmp_name"], $new_file_name)) {
                //"The file ". htmlspecialchars( basename( $files["urlImageVehicule"]["name"])). " has been uploaded.";
                $returnStatement = true;
            } else {
                $errorMsg[] = "Sorry, there was an error uploading your file.";
            }
        }
        
        return $picture;
    }

	
}
