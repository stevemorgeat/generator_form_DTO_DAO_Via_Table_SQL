<?php
/*
  MySQL2FormDTODAO.php
 */
session_start();

$listesBDs = "";
$listeTables = "";
$nomBD = "";
$nomTable = "";
$lsEntetes = "";
$lsContenu = "";
$lsContenu2 = "";
$lsAffichage = "";
$lsMessage = "";

require_once 'Connexion.php';
require_once 'Metabase.class.php';
require_once 'TravailChaineCaractere.php';

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
            //--------------------------------------------------------------------------------------------------------------------
            /*
              MODE FETCHALL
             */
            /*
              La première ligne
              http://php.net/manual/fr/function.each.php
              each() retourne la paire clé/valeur courante du tableau array et avance le pointeur de tableau.
             */
            $t = each($tData);
            foreach ($t[1] as $key => $value) {
                $lsEntetes .= "$key;";
            }
            $lsEntetes = substr($lsEntetes, 0, -1);

            /*
              Fermeture du curseur
             */
            $lrs->closeCursor();
            /*
             * je transforme ma chaine des entêtes en tableau des entêtes
             */
            $tEntetes = explode(";", $lsEntetes);


            //------------------------------------------------------------------------------------------------------------------------               
            /*
             * SI FORM
             */
            if ($rbSortie == "form") {

                /*
                 * on ecrit dans lsContenu le formulaire
                 */
                $lsContenu.="<form action='' method=''>\n";
                $lsCar = new TravailChaineCaractere(); //préparation à la méthode upper pour mettre en majuscule le nom de la table sélectionnée
                $lsContenu.="<fieldset> \n <legend> " . $lsCar->upper($nomTable) . " </legend>\n";

                /*
                 * boucle pour créer les labels et inputs
                 */
                for ($i = 0; $i < count($tEntetes); $i++) {
                    $lsSnake = new TravailChaineCaractere(); //préparation à la méthode camelize et snakeToUpperTitre
                    $lsContenu.= "<label>" . $lsSnake->snakeToUpperTitre($tEntetes[$i]) . " :</label>\n";
                    $lsContenu.= "<input type='text' name='" . $lsSnake->camelize($tEntetes[$i]) . "'>\n";
                }
                /*
                 * fin du formulaire avec le bouton valider
                 */
                $lsContenu.="<input type='submit' name='valider' value='GO'>\n";
                $lsContenu.="</fieldset> \n </form>\n";

                /*
                 * optionnel (affichage graphique)
                 */
                $lsAffichage = $lsContenu;

                /*
                 * Utilisation de &lt; &gt; pour remplacer les chevrons "<" ">"
                 * on utilise le code ascii car sinon sur la page html l'affichage de lsContenu 
                 * et les \n en <br> pour un affichage du code plus lisible.
                 */

                $lsContenu = str_replace("<", "&lt;", $lsContenu);
                $lsContenu = str_replace(">", "&gt;", $lsContenu);
                $lsContenu = nl2br($lsContenu);
            }

            //------------------------------------------------------------------------------------------------------------------------               


            /*
             * SI DTO
             */
            if ($rbSortie == "dto") {
                //--------------------------------------------------------------------------------------------------------------------

                /*
                 * on ecrit dans lsContenu la page php en mode text
                 */
                $lsContenu.="<?php \n\n";
                $lsCar = new TravailChaineCaractere(); //préparation à la méthode camelize 
                $lsContenu.="//--" .$lsCar->snakeToMajPremierelettreMot($nomTable) . ".php\n\n";// snakeToMajPremierelettreMot pour mettre en majuscule la première lettre de chaque mot
                $lsContenu.="//--propriétés\n\n";
                for ($i = 0; $i < count($tEntetes); $i++) {
                    $lsSnake = new TravailChaineCaractere(); //préparation à la méthode camelize et snakeToUpperTitre
                    $lsContenu.="private $" . $lsSnake->camelize($tEntetes[$i]) . ";\n";
                    /*
                     * dans lsContenu2 je stock les public function getters et setters
                     */
                    $lsContenu2.= "public function get" . $lsSnake->snakeToMajPremierelettreMot($tEntetes[$i]) . "(){\n";
                    $lsContenu2.= "return &#36;this->" . $lsSnake->camelize($tEntetes[$i]) . "\n}\n\n";
                    $lsContenu2.= "public function set" . $lsSnake->snakeToMajPremierelettreMot($tEntetes[$i]) . "($" . $lsSnake->camelize($tEntetes[$i]) . "){\n";
                    $lsContenu2.= "&#36;this->" . $lsSnake->camelize($tEntetes[$i]) . "= $" . $lsSnake->camelize($tEntetes[$i]) . "\n}\n\n";
                }
                $lsContenu.="\n//--méthode\n\n";
                $lsContenu.= $lsContenu2;

                $lsContenu.="\n\n}";

                /*
                 * Utilisation de &lt; &gt; pour remplacer les chevrons "<" ">"
                 * on utilise le code ascii car sinon sur la page html l'affichage de lsContenu 
                 * et les \n en <br> pour un affichage du code plus lisible.
                 */
                $lsContenu = str_replace("<", "&lt;", $lsContenu);
                $lsContenu = str_replace(">", "&gt;", $lsContenu);
                $lsContenu = nl2br($lsContenu);

                //--------------------------------------------------------------------------------------------------------------------               
            }
            /*
             *  SI DAO
             */
            if ($rbSortie == "dao") {
                //--------------------------------------------------------------------------------------------------------------------
                /*
                 *  Code ici
                 */
                //--------------------------------------------------------------------------------------------------------------------               
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
            <code>
                <?php echo $lsContenu; ?>
            </code>
            <hr>
            <p><?php echo $lsAffichage; ?></p>
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

