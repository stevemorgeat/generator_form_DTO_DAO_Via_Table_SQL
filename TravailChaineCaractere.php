<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TravailChaineCaractere
 *
 * @author allth
 */
class TravailChaineCaractere {

    /**
     * 
     * @param type $str
     * @return type
     */
    public function camelize($str) {// méthode pour cameliser une chaine de caractère écrite en snake
        return lcfirst(strtr(ucwords(strtr($str, ['_' => ' '])), [' ' => '']));
    }

    
    public function snakeToUpperTitre($str){// méthode pour passer de snake à titre majuscule séparer par des espace
        return strtoupper(str_replace("_", " ", $str));
    }
    
    public function upper($str){// méthode mettre en majuscule une chaine de caractère (un peu inutile)
        return strtoupper($str);
    }
}
