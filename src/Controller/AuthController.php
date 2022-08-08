<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\Activite;
use App\Entity\Categorie;
use App\Entity\Config;
use App\Entity\Reservation;
use App\Entity\Role;
use App\Entity\Room;
use App\Entity\Ticket;
use App\Entity\Transaction;
use App\Entity\Typeroom;
use App\Entity\User;

Class AuthController extends AbstractController {

    public function home()
    {
        echo 'ok';
        die();
    }

    public function login(Request $request, SessionInterface $session): Response
    {
        
        if(!empty($request->request->get('connexion')))
        {
            $message = $session->get('message');
            $repository = $this->getDoctrine()->getRepository(User::class);
            //var_dump($request->request);

            $email = (string) $request->request->get('email');
            $password = (string) $request->request->get('password');
            $password = sha1($password);

            $user = $repository->findOneBy([
                'email' => $email,
                'password' => $password
            ]);

            if($user == NULL) {
                $message = 'Veuillez vérifier vos identifiants svp !!!';
                $session->set('message', $message);
                return $this->redirectToRoute('login');
            }
            else if($user->getRole()->getId() == 1)
            {
                return $this->redirectToRoute('login');
            }
            else 
            {
                $session->set('user', $user);
                return $this->redirectToRoute('home');
            }
        }   
        return $this->render('user/login.html.twig'
        ); 
    }

    public function users(SessionInterface $session) {
        $user = $session->get('user');
        if($session->get('user') == NULL || $session->get('user') == null) 
        {
            return $this->redirectToRoute('logout');
        }
        else
        {
            if($user->getRole()->getId() != 3)
            {
                return $this->redirectToRoute('logout');
            }
        }

        $users = $this->getDoctrine()
                            ->getRepository(User::class)
                            ->findusers();
        //var_dump($users);die();

        return $this->render('user/users.html.twig', [
            'users' => $users,
            'user' => $user,
        ]); 
    }

    public function logout(SessionInterface $session)
    {
        $session->clear();
        return $this->redirectToRoute('login');
    }

    public function register(Request $request, SessionInterface $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $role = $this->getDoctrine()
                            ->getRepository(Role::class)
                            ->find(2);

        if(!empty($request->request->get('register')))
        { 
            //var_dump($request->request);die();
            try 
            {
                $user = new User();
                $user->setPrenoms($request->request->get('prenoms'));
                $user->setNom($request->request->get('nom'));
                $user->setEmail($request->request->get('email'));
                $user->setContact($request->request->get('contact'));
                $password = sha1($request->request->get('password'));
                $user->setPassword($password);
                $user->setRole($role);
                $user->setPays("civ");
                $user->setGenre($request->request->get('genre'));
                $user->setActive(1);
                $now = new \DateTime('NOW');
                $user->setCreateTime($now->format('Y-m-d\TH:i:s.u'));
                //var_dump($user);die();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('users');
            } catch (\Throwable $th) {
                //var_dump($th->getMessage());die();
                return $this->redirectToRoute('register');
            }
            
        } 
        
        return $this->render('user/register.html.twig', []); 
    }
    public function registerSuccess(Request $request, SessionInterface $session): Response
    {
        return $this->render('user/register-success.html.twig', []); 
    }

    public function forgotPassword(Request $request, SessionInterface $session): Response
    {
        return $this->render('user/forgot-password.html.twig', []); 
    }


}