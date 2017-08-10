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

    
    public function snakeToUpperTitre($str){
        return strtoupper(str_replace("_", " ", $str));
    }
}
