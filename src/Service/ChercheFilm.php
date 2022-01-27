<?php
namespace App\Service;
use aharen\OMDbAPI;

class ChercheFilm
{

public function api(String $film): string
    {

        $omdb = new OMDbAPI('ad635bc8', false, true);
        $content = $omdb->fetch("t", $film);
        return ($content["data"]["Plot"]);


    }
}