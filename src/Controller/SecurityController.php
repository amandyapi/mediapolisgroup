<?php
// src/AppBundle/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\DBAL\Connection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;

class SecurityController extends AbstractController
{
    protected $em;
    protected $mailer;
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function index(){
        return $this->redirectToRoute('admin_security_login');
    }

    public function login(Request $request, SessionInterface $session)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);

        if(!empty($request->request->get('connexion')))
        {
            $user = NULL;

            try 
            {
                $email = (string) $request->request->get('email');
                $password = (string) $request->request->get('password');
                $password = sha1($password);
                
                $user = $this->getDoctrine()
                             ->getRepository(User::class)
                             ->findUser($email, $password);
                var_dump($user[0]);die();  
            } catch (\Throwable $th) {
                $message = $th->getMessage();
                var_dump($message);die();
            }

            if($user == NULL) {
                return $this->redirectToRoute('admin_security_login');
            }
            else 
            {
                $session->set('user', $user);
                return $this->redirectToRoute('admin_articles');
                
            }
        }

        $template = 'admin/login/login.html.twig';            
        return $this->render($template, [
            
        ]); 
    }

    public function logout(SessionInterface $session){
        $session->clear();
        return $this->redirectToRoute('admin_security_login');
    }


	
}
