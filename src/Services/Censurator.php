<?php

namespace App\Services;

class Censurator
{

    private $motsInterdits;

    public function __construct()
    {
        $content = file_get_contents('../data/gros_mots.txt');
        $this->motsInterdits = preg_split('/\r\n/', $content);
    }


    public function purify($text){

        foreach ($this->motsInterdits as $grosMots){
            $texteDeRemplacement = str_repeat('*', mb_strlen($grosMots));
            $text = str_ireplace($grosMots, $texteDeRemplacement, $text);
        }
        return $text;
    }

}