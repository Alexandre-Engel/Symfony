<?php
namespace App\Service;
use aharen\OMDbAPI;

class ChercheFilm
{

public function api(String $film): string
    {
        /*
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'http://www.omdbapi.com/?i=tt3896198&apikey=ad635bc8');
        $content = $response->getContent();
        var_dump($content);
        return $this->render('film/api.html.twig');
        */

        $omdb = new OMDbAPI('ad635bc8', false, true);
        $content = $omdb->fetch("t", $film);
        return ($content["data"]["Plot"]);


    }
}