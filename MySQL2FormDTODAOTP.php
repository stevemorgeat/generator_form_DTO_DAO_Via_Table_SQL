<?php
/*
  MySQL2JsonCsvXmlExo.php
 */
session_start();

$listesBDs = "";
$listeTables = "";
$nomBD = "";
$nomTable = "";
$lsContenu = "";
$lsMessage = "";

require_once 'Connexion.php';
require_once 'Metabase.class.php';

$lcnx = Connexion::seConnecter("cours.ini"); //méthode se connecter
Connexion::initialiserTransaction($lcnx); //beginTransaction() (on commence la transaction)

$tBDs = Metabase::getBDsFromServeur($lcnx);
foreach ($tBDs as $bd) {
    $listesBDs .= "<option>$bd</option>";
}
Connexion::validerTransaction($lcnx); //commit() (validation de la transaction)
/*
 * AFFICHAGE LISTE DES TABLES D'UNE BD
 */
$btValiderBD = filter_input(INPUT_GET, "btValiderBD");
if ($btValiderBD != null) {
    $nomBD = filter_input(INPUT_GET, "listesBDs");
    $_SESSION["bd"] = $nomBD;
}

if (isSet($_SESSION["bd"])) {
    $nomBD = $_SESSION["bd"];
    $tTables = Metabase::getTablesFromBD($lcnx, $nomBD);
    foreach ($tTables as $table) {
        $listeTables .= "<option>$table</option>";
    }
}

/*
 * AFFICHAGE FINAL ...
 */
$btValiderTout = filter_input(INPUT_GET, "btValiderTout");

if ($btValiderTout != null) {
    $nomTable = filter_input(INPUT_GET, "listeTables");
    $lsSQL = "SELECT * FROM " . $nomBD . "." . $nomTable;
    echo "<br>$lsSQL<br>";

    $rbSortie = filter_input(INPUT_GET, "rbSortie");

    if ($rbSortie != null) {
        try {
            $lrs = $lcnx->query($lsSQL);
            $lrs->setFetchMode(PDO::FETCH_ASSOC);
            $tData = $lrs->fetchAll();

            /*
             * SI CSV
             */
            if ($rbSortie == "form") {
                // Initialisations
                $lsContenu = "";
                $lsEntetes = "";

//                /*
//                  MODE FETCHALL
//                 */
//                /*
//                  La première ligne
//                 */
//                list($cle, $valeur) = each($tData);
//                //echo "<hr>$cle : $valeur";
//                echo "<br><pre>";
//                //var_dump($lsEntetes);
//                echo "</pre><br>";
//                /*
//                  Les autres lignes
//                 */
//                foreach ($tData as $ligne) {
//                    foreach ($ligne as $valeur) {
//                        $lsContenu .= $valeur . ";";
//                    }
//                    $lsContenu = substr($lsContenu, 0, -1);
//                    $lsContenu.= "\n";
//                }
//
//                /*
//                  MODE WHILE-FETCH
//                 */
////                /*
////                  Le premier enregistrement de la table
////                  pour principalement récupérer les noms des colonnes
////                  et le contenu de la 1e ligne
////                 */
////                $ligne = $lrs->fetch();
////                foreach ($ligne as $colonne => $valeur) {
////                    $lsEntetes .= $colonne . ";";
////                    $lsContenu .= $valeur . ";";
////                }
////                $lsEntetes = substr($lsEntetes, 0, -1);
////                $lsContenu = substr($lsContenu, 0, -1);
////                $lsEntetes .= "\n";
////                $lsContenu .= "\n";
////
////                // On boucle sur les lignes à partir de la 2ème
////                while ($ligne = $lrs->fetch()) {
////                    // On boucle sur les colonnes
////                    foreach ($ligne as $valeur) {
////                        // On recupere les valeurs des colonnes
////                        $lsContenu .= $valeur . ";";
////                    }
////                    // On enleve le dernier ;
////                    $lsContenu = substr($lsContenu, 0, -1);
////                    // On ajoute le separateur d'enr
////                    $lsContenu .= "\n";
////                }
//                // Suppression du dernier saut de ligne
//                $lsContenu = substr($lsContenu, 0, -1);
//
//                // On place les entetes avant le contenu
//                $lsContenu = $lsEntetes . $lsContenu;
//                file_put_contents("$nomTable.txt", $lsContenu);
//                // Pour l'affichage
//                $lsContenu = nl2br($lsContenu);
            }

            /*
             * SI JSON
             */
            if ($rbSortie == "dto") {
//                //$tData = $lrs->fetchAll();
//                $lsContenu = json_encode($tData);
//                file_put_contents("$nomTable.json", $lsContenu);
            }
            
            if ($rbSortie == "dao") {
//                //$tData = $lrs->fetchAll();
//                $lsContenu = json_encode($tData);
//                file_put_contents("$nomTable.json", $lsContenu);
            }
            /*
              Fermeture du curseur
             */
            $lrs->closeCursor();

            $lsMessage = "Ok, c'est fini ! Tu peux aller faire la sieste !!!";
            /*
             * REINITIALISATION
             */
            unset($_SESSION["bd"]);
            $nomBD = "";
        } catch (PDOException $e) {
            $lsMessage = "Echec de l'exécution : " . $e->getMessage();
        }
    } else {
        $lsMessage = "Il faut sélectionner un type de sortie !!!";
    }
}
Connexion::seDeconnecter($lcnx);
?>

<!DOCTYPE html>
<!--
-->
<html>
    <head>
        <meta charset="UTF-8">
        <style>
            *{margin: 0; padding: 0;}
            html{
                width: 100%;
            }
            article{
                margin: 0.5em; 
                border: 1px red solid; 
                float: left; 
                width: 30%; 
                height: 500px;
            }
            aside{
                margin: 0.5em; 
                border: 1px black solid; 
                float: left; 
                width: 60%; 
                height: 500px;
                overflow: auto;
            }
            input, select, fieldset{
                margin: 0.5em;
                padding: 0.5em;
            }
            footer{
                margin: 0.5em;
                padding: 0.5em;
                clear: both;
            }
        </style>
        <title>MySQL2JsonCsvXml</title>
    </head>

    <body>
        <article>
            <form action="" method="GET">
                <select name="listesBDs" size="5">
                    <?php echo $listesBDs; ?>
                </select>
                <br>
                <input type="submit" value="Valider BD" name="btValiderBD" />
            </form>

            <form action="" method="GET">
                <select name="listeTables" size="5">
                    <?php echo $listeTables; ?>
                </select>

                <fieldset>
                    <legend>Sorties</legend>
                    <input type="radio" name="rbSortie" id="rbForm" value="form" />
                    <label for="rbForm">FORMULAIRE - form</label><br>
                    <input type="radio" name="rbSortie" id="rbDTO" value="dto" />
                    <label for="rbDTO">DTO</label><br>
                    <input type="radio" name="rbSortie" id="rbDAO" value="dao" />
                    <label for="rbDAO">DAO</label>
                </fieldset>

                <input type="submit" value="Valider Tout" name="btValiderTout" />
            </form>
        </article>

        <aside>
            <p>
                <?php echo $lsContenu; ?>
            </p>
        </aside>

        <footer>
            <label>
                <?php
                echo $lsMessage;
                ?>
            </label>
        </footer>

    </body>
</html>

