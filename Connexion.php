<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Connexion
 *
 * @author allth
 */
class Connexion {

    /**
     * 
     * @param type $psCheminParametresConnexion
     * @return null
     */
    public static function seConnecter($psCheminParametresConnexion) {
        $tProprietes = parse_ini_file($psCheminParametresConnexion);

        $lsProtocole = $tProprietes["protocole"];
        $lsServeur = $tProprietes["serveur"];
        $lsPort = $tProprietes["port"];
        $lsUT = $tProprietes["ut"];
        $lsMDP = $tProprietes["mdp"];
        $lsBD = $tProprietes["bd"];

        /*
         * Connexion
         */
        $lcnx = null;
        try {
            $lcnx = new PDO("$lsProtocole:host=$lsServeur;port=$lsPort;dbname=$lsBD;", $lsUT, $lsMDP);
            $lcnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $lcnx->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
            $lcnx->exec("SET NAMES 'UTF8'");
        } catch (Exception $ex) {
            $lcnx = null;
//        echo "<br>" . $ex->getMessage();
        }

        return $lcnx;
    }///fin seConnecter()

    /**
     * 
     * @param PDO $pcnx
     */
    public static function seDeconnecter(PDO& $pcnx) {
        /*
         * PDO...Typage possible d'un paramètre
         * & parce que passage par référence
         */

        $pcnx = null;
    }///fin seDeconnecter()

    /**
     *
     * @param PDO $pcnx
     */
    public static function initialiserTransaction(PDO &$pcnx) {
        $lbOK = true;
        try {
            $pcnx->beginTransaction();
        } catch (Exception $ex) {
            $lbOK = false;
        }
        return $lbOK;
        
    }/// fin initialiserTransaction()

    /**
     *
     * @param PDO $pcnx
     */
    public static function validerTransaction(PDO &$pcnx) {
        $lbOK = true;
        try {
            $pcnx->commit();
        } catch (Exception $ex) {
            $lbOK = false;
        }
        return $lbOK;
       
    } /// fin validerTransaction()

    /**
     *
     * @param PDO $pcnx
     */
    public static function annulerTransaction(PDO &$pcnx) {
        $lbOK = true;
        try {
            $pcnx->rollBack();
        } catch (Exception $ex) {
            $lbOK = false;
        }
        return $lbOK;
        /// annulerTransaction
    }/// fin  annulerTransaction()

}///fin de class
