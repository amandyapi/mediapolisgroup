<?php
// src/AppBundle/Controller/UserController.php

namespace App\Controller;

use App\Entity\Picture;
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

class PictureController extends AbstractController
{
    protected $em;
    protected $mailer;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function getAllByEntity($entity, $ref)
    {
        $images = [];

        if($entity != "article" && $entity != "user" )
        {
            $message = "Wrong Entity";
            $error = ['error' => $message];
            return $this->json($error, 404);
        }
        elseif(empty($ref)){
            $error = ['error' => "Wrong entity ref"];
            return $this->json($error, 404);
        }

        try 
        {
            $images = $this->getDoctrine()
                           ->getRepository(Picture::class)
                           ->findPicturesByEntity($entity, $ref);

            return $this->json($images, 200);

        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), 500);
        }
    }

    public function getOne($id)
    {
        $image = null;

        try 
        {
            $image = $this->getDoctrine()
                           ->getRepository(Picture::class)
                           ->findPicture($id);

            return $this->json($image, 200);

        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), 500);
        }
    }

    public function save(Request $request)
    {
        $data = json_decode($request->getContent());
        
        $entityManager = $this->getDoctrine()->getManager();
        $responseText;
        
        $title = $data->title;
        $entity = $data->entity;
        $ref = $data->ref;
        $imageData = $data->imageData;

        try {
            $picture = new Picture();
            
            $picture->setTitle(\strtolower($title));
            $picture->setEntity(\strtolower($entity));
            $picture->setRef(\strtolower($ref));
            $picture->setImageData(\strtolower($imageData));
            
            $entityManager->persist($picture);
            $entityManager->flush();

            $responseText = "L'image a été ajouté avec succes. Id: ";

            $newId = $picture->getId();

            return $this->json($responseText.$newId, 200);
        } catch (\Throwable $th) {
            $responseText = "Une erreur est survenue lors de l'enregistrement de l'image";
            
            return $this->json($th->getMessage(), 500);
        }
    }
}
