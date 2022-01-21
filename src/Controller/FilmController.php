<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\FilmType;
use App\Form\Type\SuppressionFilm;
use App\Form\Type\CSV;
use App\Service\ChercheFilm;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Film;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use aharen\OMDbAPI;
use GuzzleHttp\Psr7\UploadedFile;
use League\Csv\Reader;





class FilmController extends AbstractController
{


    public function createFilm(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $film = new Film();
        $film->setTitre('Keyboard');
        $film->setDescription('Ergonomic and stylish!');
        $film->setScore(8);
        $film->setNbVotant(69);
        
        $entityManager->persist($film);

        $entityManager->flush();

        return new Response('Nouveau film enregistré avec l\'id '.$film->getId());
    }

    public function listeFilm(ManagerRegistry $doctrine): Response
    {
        $liste_film = $doctrine->getRepository(Film::class)->findAll();
        return $this->render('film/listeFilm.html.twig', ['liste_film' => $liste_film]);

    }



    public function new(Request $request, ManagerRegistry $doctrine, ChercheFilm $ChercheFilm): Response
    {
        $film = new Film();
        

        $form = $this->createForm(filmType::class, $film);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $film = $form->getData();
            
            $film->setDescription($ChercheFilm->api($film->getTitre()));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($film);

            $entityManager->flush();


            return $this->redirectToRoute('film_show', ["id" => $film->getId()]);
        }

        return $this->renderForm('film/new.html.twig', [
            'form' => $form,
        ]);

    }


    public function show(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $film = $doctrine->getRepository(Film::class)->find($id);

        if (!$film) {
            throw $this->createNotFoundException(
                'Woaaaa le bo film qui existe pas '.$id
            );
        }

        $form = $this->createForm(SuppressionFilm::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->getData()["code"];
            if ( $code === $this->getParameter("app.admin_code")) {
              
            $entityManager = $doctrine->getManager();

            $entityManager->remove($film);
            $entityManager->flush();
            return $this->redirectToRoute('film_liste');  
            }
            
            
        }

        return $this->renderForm('film/film.html.twig', [
            'film'=> $film,
            'form'=> $form
        ]);
    }


    public function ajoutCSV(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(CSV::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get("file")->getData();
            $name = $file->getClientOriginalName();
            $file->move("../public",  $name);

            $csv = Reader::createFromPath("../public/".$name, 'r');
            $csv->setHeaderOffset(0);

            $header = $csv->getHeader();
            $records = $csv->getRecords();
    
            foreach ($records as $record){
                $film = new Film();
                $film->setTitre($record[$header[0]])
                    ->setDescription($record[$header[1]])
                    ->setScore($record[$header[2]]);

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($film);
            } 
            $entityManager->flush();

            
            return $this->redirectToRoute('film_liste');
        }
    
        return $this->renderForm('film/api.html.twig', [
            'form' => $form
        ]);

    }


    


}

