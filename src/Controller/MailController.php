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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;

class MailController extends AbstractController
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

        $mails = $this->getDoctrine()
                      ->getRepository(Mail::class)
                      ->findMails();
                     
        return $this->json($mails);
    }

    public function getOne($id)
    {

        $mail = $this->getDoctrine()
                      ->getRepository(Mail::class)
                      ->findMail($id);
                     
        return $this->json($mail);
    }

    public function sendOne(Request $request)
    {

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

    public function sendOneMail(Request $request)
    {
        $response;
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        //var_dump($data);die();
        $gfiContactMail = 'contact@gfi-co.net';
        $senderFullName = $data['senderFullName'];
        $senderMail = $data['senderMail'];
        $senderContact = $data['senderContact'];
        $title = $data['title'];
        $content = $data['content'];
        
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($senderMail, $senderFullName))
                ->to(new Address($gfiContactMail))
                ->subject($title)
                ->htmlTemplate('contact-mail.html.twig')
                //->text('nous avons le plaisir de vous informer, que votre compte <b>CIKHAPAY</b>');

                ->context([
                    'senderFullName' => $senderFullName,
                    'senderMail' => $senderMail,
                    'senderContact' => $senderContact,
                    'title' => $title,
                    'content' => $content,
                ]);

            $this->mailer->send($email);

            $mail = new Mail();
            $mail->setSenderFullName(\strtolower($senderFullName));
            $mail->setSenderMail(\strtolower($senderMail));
            $mail->setSenderContact(\strtolower($senderContact));
            $mail->setTitle(\strtolower($title));
            $mail->setContent(\strtolower($content));

            $entityManager->persist($mail);
            $entityManager->flush();

            return $this->json(true, 200);
            //return $this->redirectToRoute('user_get_all');
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            
            return $this->json($th->getMessage(), 500);
        }
    }
	
}
