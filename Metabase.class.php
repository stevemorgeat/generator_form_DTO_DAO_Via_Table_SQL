<?php

/**
 * Description of Metabase
 *
 * @author pascal
 */
class Metabase {
    /*
     * Metabase.class.php
     *
     * Mode : OO
     */

    /**
     *
     * Renvoie un tableau : la liste des BDs d'un serveur
     *
     * @param type $pcnx
     * @return type
     */
    public static function getBDsFromServeur($pcnx) {
        $lsSelect = "SELECT SCHEMA_NAME FROM information_schema.schemata";
        return self::getTableau1DFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * Renvoie un tableau : la liste des tables d'une BD
     *
     * @param PDO $pcnx
     * @param type $psBD
     * @return type
     */
    public static function getTablesFromBD(PDO $pcnx, $psBD) {

        $lsSelect = "";
        $lsSelect .= "SELECT TABLE_NAME FROM information_schema.tables ";
        $lsSelect .= " WHERE TABLE_SCHEMA='$psBD'";

        //echo "<br>$lsSelect<br>";
        return self::getTableau1DFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * Renvoie un tableau : la liste des colonnes d'une table
     *
     * @param PDO $pcnx
     * @param type $psBD
     * @param type $psTable
     * @return type
     */
    public static function getColumnsNamesFromTable(PDO $pcnx, $psBD, $psTable) {

        $lsSelect = "";
        $lsSelect .= "SELECT COLUMN_NAME FROM information_schema.columns ";
        $lsSelect .= " WHERE TABLE_SCHEMA='$psBD' AND TABLE_NAME='$psTable'";

        return self::getTableau1DFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * Renvoie un hashmap : la liste des colonnes et les types d'une table
     *
     * @param PDO $pcnx
     * @param type $psBD
     * @param type $psTable
     * @return type
     */
    public static function getColumnsNamesAndTypesFromTable(PDO $pcnx, $psBD, $psTable) {

        $lsSelect = "";
        $lsSelect .= "SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.columns ";
        $lsSelect .= " WHERE TABLE_SCHEMA='$psBD' AND TABLE_NAME='$psTable'";

        return self::getMapFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * Renvoie un tableau : la liste des colonnes formant la PK d'une table
     *
     * @param type $pcnx
     * @param type $psBD
     * @param type $psTable
     * @return type
     */
    public static function getColumnsNamesPKFromTable($pcnx, $psBD, $psTable) {
        $lsSelect = "";
        $lsSelect .= "SELECT COLUMN_NAME ";
        $lsSelect .= " FROM information_schema.columns ";
        $lsSelect .= " WHERE TABLE_SCHEMA='$psBD' AND TABLE_NAME='$psTable' AND COLUMN_KEY='PRI' ";

        return self::getTableau1DFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * Renvoie un tableau : la liste des colonnes formant la FK
     *
     * @param type $pcnx
     * @param type $psBD
     * @param type $psTable
     * @return type
     */
    public static function getColumnsNamesFKFromTable($pcnx, $psBD, $psTable) {

        /*
          SELECT COLUMN_NAME
          FROM KEY_COLUMN_USAGE
          WHERE TABLE_SCHEMA = 'cours'
          AND TABLE_NAME = 'contributeur'
          AND REFERENCED_TABLE_NAME IS NOT NULL;
         */

        $lsSelect = "SELECT COLUMN_NAME ";
        $lsSelect .= " FROM information_schema.KEY_COLUMN_USAGE ";
        $lsSelect .= " WHERE TABLE_SCHEMA = '$psBD' ";
        $lsSelect .= " AND TABLE_NAME = '$psTable' ";
        $lsSelect .= " AND REFERENCED_TABLE_NAME IS NOT NULL ";

        return self::getTableau1DFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * Renvoie un hashmap : la liste des colonnes formant la FK et les tables de references
     *
     * @param type $pcnx
     * @param type $psBD
     * @param type $psTable
     * @return type
     */
    public static function getColumnsNamesFKAndReferencesFromTable($pcnx, $psBD, $psTable) {

        /*
          SELECT COLUMN_NAME
          FROM KEY_COLUMN_USAGE
          WHERE TABLE_SCHEMA = 'cours'
          AND TABLE_NAME = 'contributeur'
          AND REFERENCED_TABLE_NAME IS NOT NULL;
         */

        $lsSelect = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME ";
        $lsSelect .= " FROM information_schema.KEY_COLUMN_USAGE ";
        $lsSelect .= " WHERE TABLE_SCHEMA = '$psBD' ";
        $lsSelect .= " AND TABLE_NAME = '$psTable' ";
        $lsSelect .= " AND REFERENCED_TABLE_NAME IS NOT NULL ";

        return self::getMapFromSelect($pcnx, $lsSelect);
    }

    /**
     *
     * @param type $pcnx
     * @param type $psSelect
     * @return array
     */
    private static function getTableau1DFromSelect($pcnx, $psSelect) {
        $t1D = array();
        $lrs = null;
        try {
            $lrs = $pcnx->prepare($psSelect);
            $lrs->execute();
            $lrs->setFetchMode(PDO::FETCH_NUM);
            foreach ($lrs as $enr) {
                array_push($t1D, $enr[0]);
            }
            $lrs->closeCursor();
        } catch (PDOException $e) {
            $lrs = null;
            array_push($t1D, $e->getMessage());
        }
        return $t1D;
    }

    /**
     *
     * @param type $pcnx
     * @param type $psSelect
     * @return array
     */
    private static function getMapFromSelect($pcnx, $psSelect) {
        $map = array();
        $lrs = null;
        try {
            $lrs = $pcnx->prepare($psSelect);
            $lrs->execute();
            $lrs->setFetchMode(PDO::FETCH_NUM);
            foreach ($lrs as $enr) {
                $map[$enr[0]] = $enr[1];
            }
            $lrs->closeCursor();
        } catch (PDOException $e) {
            $lrs = null;
            $map["erreur"] = $e->getMessage();
        }
        return $map;
    }

}

?>
