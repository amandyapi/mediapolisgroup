<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    
    public function index(): Response
    {
        /*$file = "data/partners.json";
        $data = file_get_contents($file);
        $partners = \json_decode($data);*/
        $partners = [];
        for ($i=1; $i < 35; $i++) { 
            $p = new \stdClass();
            $p->name="$i";
            $p->url="$i.png";
            $partners[] = $p;
        }

        return $this->render('page/index.html.twig', [
            'controller_name' => 'PageController',
            'partners' => $partners
        ]);
    }

    public function agence(): Response
    {
        return $this->render('page/agence.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function equipe(): Response
    {
        return $this->render('page/equipe.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function offreStrategique(): Response
    {
        return $this->render('page/offres-strategique.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    public function offresTechnologiques(): Response
    {
        return $this->render('page/offres-technologiques.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function offresOperationnelles(): Response
    {
        return $this->render('page/offres-operationnelles.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function offresMusicEvent(): Response
    {
        return $this->render('page/offres-music-event.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    public function galerie(): Response
    {
        return $this->render('page/galerie.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function blog(): Response
    {
        return $this->render('page/blog.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }

    public function recrutements(): Response
    {
        return $this->render('page/recrutements.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
}
