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

            } catch (\Throwable $th) {
                
            }

            if($user == NULL) {
                return $this->redirectToRoute('admin_security_login');
            }
            else 
            {
                $session->set('user', $user);
                if($user['role'] == 3)
                {
                    return $this->redirectToRoute('admin_articles');
                }
                else
                {
                    return $this->redirectToRoute('admin_mails');
                }
                
            }
        }

        $template = 'login/login.html.twig';            
        return $this->render($template, [
            
        ]); 
    }

    public function logout(SessionInterface $session){
        $session->clear();
        return $this->redirectToRoute('admin_security_login');
    }


	
}
