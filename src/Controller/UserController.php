<?php
// src/AppBundle/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
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

class UserController extends AbstractController
{
    protected $em;
    protected $mailer;
    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function login(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = sha1($data['password']);
        
        if($email == "NULL" || $email == "null") {
            $responseText = "L'email ne peut etre null";
            return $this->json($responseText, 400);
        }

        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->findUser($email, $password);
                     
        return $this->json($user);
    }

    public function getAll()
    {

        $users = $this->getDoctrine()
                      ->getRepository(User::class)
                      ->findUsers();
                     
        return $this->json($users);
    }

    public function getOne($id)
    {

        $user = $this->getDoctrine()
                      ->getRepository(User::class)
                      ->findUserById($id);
                     
        return $this->json($user);
    }

    public function register($data)
    {
        //var_dump($data);die();
        $entityManager = $this->getDoctrine()->getManager();

        $response;$responseText;
        $contact = $data->getContact();
        $email = $data->getEmail();
        $isUserUnique = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->checkEmailContactUnicity($email, $contact);
        //var_dump($user);die();
        if(!$isUserUnique) {
            $responseText = "L'E-mail et/ou le contact existent déjà";
            //return new Response($responseText, 500);
            
            return  $this->json($responseText, 500);
        }
        try {
            $user = new User();
            $user->setNom(\strtoupper($data->getNom()));
            $user->setPrenoms(\ucwords(\strtolower($data->getPrenoms())));
            $user->setEmail(\strtolower($data->getEmail()));
            $password = sha1($data->getPassword());
            $user->setPassword($password);
            $user->setContact($data->getContact());
            $user->setPays($data->getPays());
			$user->setGenre(\strtoupper($data->getGenre()));
            $otp = rand(1000, 9000);
            $user->setRegisterOtp($otp); 
            
            $time = $user->getCreateTime(); 
            $user->setRegisterOtpTime($time); 
            //var_dump($user);die();
            $role = $this->getDoctrine()
                        ->getRepository(Role::class)
                        ->find(1);
            $user->setRole($role);

            $entityManager->persist($user);
            $entityManager->flush();
            $responseText = "L'opération a été réalisée avec succes";

            $new_user = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->findOneBy(['email' => $user->getEmail()]);

            $response = $this->sendConfirmationMail([
                        'usermail' => $user->getEmail(),
                        'nom' => $user->getNom(),
                        'otp' => $user->getRegisterOtp(),
                        'prenoms' => $user->getPrenoms(),
                    ]);
            //Throw mail error

            //var_dump($response);die();

            return $this->json($new_user);
        } catch (\Throwable $th) {
            $responseText = "Une erreur est survenue lors de la création de l'utilisateur";
            
            return $this->json($th->getMessage(), 500);
        }


    }
	
	public function reactivation($data)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $email = $data->getEmail();
        $oldEmail = $data->getOldEmail();
        $password = sha1($data->getPassword());
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = null;
        $user = $repository->findOneBy([
            'email' => $oldEmail,
            'password' => $password,
        ]);
        
        if($user == NULL)
        {
            $responseText = "Utilisateur introuvable";
            return $this->json($responseText, 500);
        }
        else 
        {
            try 
            {
                $user->setEmail($email);
                $entityManager->flush();
                $params = [
                    'usermail' => $email,
                    'nom' => $user->getNom(),
                    'otp' => $user->getRegisterOtp(),
                    'prenoms' => $user->getPrenoms(),
                ];

                $response = $this->sendConfirmationMail($params);
                
                $responseText = "Votre code d'activation vous a été envoyé par mail";
                return $this->json($responseText, 200);
            } catch (\Throwable $th) {
                $responseText = "Une erreur est survenue lors de l'envoi du code";
                return $this->json($responseText, 500);
            }
        }
        
    }

    public function activation($data)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $response;$responseText;$canActivatedUser = null;

        $contact = $data->getContact();
        $pwd = sha1($data->getPassword());
        $otp = $data->getRegisterOtp();

        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->checkUserRegisterOtp($contact, $pwd, $otp);
        //var_dump($user);die();
        if(!$user) {
            $responseText = "Veuillez vérifier les informations fournies";
            
            return $this->json($responseText, 500);
        }
        else if($user['active'] == 1) {
            $responseText = "Utilisateur déjà actif";
            return $this->json($responseText, 500);
        }

        else if(!$this->checkOtpValidity($user))
        {
            $userObj = $entityManager->getRepository(User::class)->find($user["id"]);

            //var_dump($userObj);die();
            
            $otp = rand(1000, 9000);
            $now = new \DateTime('NOW');
            $time = $now->format('Y-m-d\TH:i:s.u');
            $userObj->setRegisterOtp($otp);
            $userObj->setCreateTime($time);
            $entityManager->flush();

            $data = [
                'usermail' => $userObj->getEmail(),
                'nom' => $userObj->getNom(),
                'otp' => $userObj->getRegisterOtp(),
                'prenoms' => $userObj->getPrenoms(),
            ];
            
            $response = $this->sendNewOtp($data);
            //var_dump($response);
            //die();
            $responseText = "Votre code de confirmation a expiré. Un nouveau code vous a été envoyé par mail.";
            return $this->json($responseText, 500);
        }

        try {
            $userId = $user['id'];
            $userObj = $entityManager->getRepository(User::class)->find($userId);
            $userObj->setActive(1);
            $entityManager->flush();

            $responseText = "success";
            return $this->json($responseText, 200);
        } catch (\Throwable $th) {
            $responseText = "Une erreur est survenue lors de l'activation de l'utilisateur";
            return $this->json($th->getMessage(), 500);
        }


    }

    public function sendConfirmationMail($params)
    {
        $response;
        $userMail = $params['usermail'];
        $nom = $params['nom'];
        $otp = $params['otp'];
        $prenoms = $params['prenoms'];

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@cikhapay.com', 'CIKHAPAY'))
                ->to(new Address($userMail))
                ->subject('Creation de compte cikhapay')
                ->htmlTemplate('inscription-mail.html.twig')
                //->text('nous avons le plaisir de vous informer, que votre compte <b>CIKHAPAY</b> vient d’être créé. Vous pouvez dès maintenant profiter des différents services proposés par CIKHAPAY');

                ->context([
                    'userMail' => $userMail,
                    'otp' => $otp,
                    'nom' => $nom,
                    'prenoms' => $prenoms,
                ]);
            $this->mailer->send($email);
            $response = true;
            //return $this->redirectToRoute('user_get_all');
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $response = false;
        }
        return $response;
    }

    public function checkOtpValidity($user)
    {
        //var_dump($user);die();
        $isValid = false;$limit = 30;

        $create_time = new \DateTime($user['register_otp_time']);
        $now = new \DateTime('NOW');
        $date = $create_time->format('Y-m-d\TH:i:s.u');

        $delay=$create_time->diff($now);

        //var_dump($date);var_dump($delay);die();

        if($delay->y > 0 || $delay->m > 0 || $delay->d > 0 || $delay->h > 0 || $delay->i > $limit) {
            $isValid = false;
        }
        else if($delay->m < $limit)
        {
            $isValid = true;
        }

        return $isValid;
    }

    public function checkResetOtpValidity($user)
    {
        //var_dump($user);die();
        $isValid = false;$limit = 30;

        $create_time = new \DateTime($user->getResetOtpTime());
        $now = new \DateTime('NOW');
        $date = $create_time->format('Y-m-d\TH:i:s.u');

        $delay=$create_time->diff($now);

        //var_dump($date);var_dump($delay);die();

        if($delay->y > 0 || $delay->m > 0 || $delay->d > 0 || $delay->h > 0 || $delay->i > $limit) {
            $isValid = false;
        }
        else if($delay->m < $limit)
        {
            $isValid = true;
        }

        return $isValid;
    }


    public function sendNewOtp($params)
    {
        $response;
        $userMail = $params['usermail'];
        $nom = $params['nom'];
        $otp = $params['otp'];
        $prenoms = $params['prenoms'];

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@cikhapay.com', 'CIKHAPAY'))
                ->to(new Address($userMail))
                ->subject("Code d'activation cikhapay")
                ->htmlTemplate('new-otp-mail.html.twig')
                //->text('nous avons le plaisir de vous informer, que votre compte <b>CIKHAPAY</b> vient d’être créé. Vous pouvez dès maintenant profiter des différents services proposés par CIKHAPAY');

                ->context([
                    'userMail' => $userMail,
                    'otp' => $otp,
                    'nom' => $nom,
                    'prenoms' => $prenoms,
                ]);
            $this->mailer->send($email);
            $response = true;
            //return $this->redirectToRoute('user_get_all');
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $response = false;
        }
        return $response;
    }

    public function sendResetPasswordOtp($params)
    {
        $response;
        $userMail = $params['usermail'];
        $nom = $params['nom'];
        $otp = $params['otp'];
        $prenoms = $params['prenoms'];

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@cikhapay.com', 'CIKHAPAY'))
                ->to(new Address($userMail))
                ->subject("Réinitialisation de mot de passe")
                ->htmlTemplate('reset-password-otp-mail.html.twig')

                ->context([
                    'userMail' => $userMail,
                    'otp' => $otp,
                    'nom' => $nom,
                    'prenoms' => $prenoms,
                ]);
            $this->mailer->send($email);
            $response = true;

        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $response = false;
        }
        return $response;
    }

    public function forgotpassword($data)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = null;
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data->getEmail()]);

        if($user == null) {
            $responseText = "Veuillez vérifier l'adresse email fournie";
            return $this->json($responseText, 500);
        }

        try {
            $otp = rand(1000, 9000);
            $now = new \DateTime('NOW');
            $time = $now->format('Y-m-d\TH:i:s.u');
            $user->setResetOtp($otp);
            $user->setResetOtpTime($time);

            $entityManager->flush();

            $params = [
                'usermail' => $user->getEmail(),
                'nom' => $user->getNom(),
                'otp' => $user->getResetOtp(),
                'prenoms' => $user->getPrenoms(),
            ];
            
            $response = $this->sendResetPasswordOtp($params);

            if(!$response) {
                $responseText = "Une erreur est survenue lors de l'envoi du mail";
                return new Response($th->getMessage(), 500);
            }
        } catch (\Throwable $th) {
            //throw $th;
            $responseText = "Une erreur est survenue";
            return new Response($th->getMessage(), 500);
        }

        $responseText = "Un code de réinitialisation de mot de passe a été envoyé par mail";
        return $this->json($responseText, 200);

        
    }


    public function resetpassword($data)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = null;
        $otp = $data->getResetOtp();
        $password = $data->getPassword();
        $user = $entityManager->getRepository(User::class)->findOneBy(['resetOtp' => $otp]);

        if($user == null) {
            $responseText = "Veuillez vérifier le code de réinitialisation";
            return $this->json($responseText, 500);
        }

        //var_dump($user->getId());var_dump($user->getNom());die();
        $user->getId();

        if(!$this->checkResetOtpValidity($user)) {
            try {
                $otp = rand(1000, 9000);
                $now = new \DateTime('NOW');
                $time = $now->format('Y-m-d\TH:i:s.u');
                $user->setResetOtp($otp);
                $user->setResetOtpTime($time);
    
                $entityManager->flush();
    
                $params = [
                    'usermail' => $user->getEmail(),
                    'nom' => $user->getNom(),
                    'otp' => $user->getResetOtp(),
                    'prenoms' => $user->getPrenoms(),
                ];
                
                $response = $this->sendResetPasswordOtp($params);
    
                if(!$response) {
                    $responseText = "Une erreur est survenue lors de l'envoi du mail";
                    return new Response($th->getMessage(), 500);
                }
            }
            catch (\Throwable $th) {
                //throw $th;
                $responseText = "Une erreur est survenue";
                return $this->json($th->getMessage(), 500);
            }
            $responseText = "Votre code a expiré. Un nouveau code de réinitialisation de mot de passe vous a été envoyé par mail";
            return $this->json($responseText, 500);
        } 
        $hashedPwd = sha1($password);
        $user->setPassword($hashedPwd);
        $entityManager->flush();

        //echo 'New password ';var_dump($user->getPassword());die();

        $responseText = "Votre mot de passe a été réinitialisé avec succès";
        return $this->json($responseText, 200);
    }

}
