<?php
/*
  MySQL2FormDTODAO.php
 */

/*
 * Session start pour plutard garder le noms de la BD dans une variable de session
 */
session_start();

/*
 * initialisation de mes variables
 */
$listesBDs = "";
$listeTables = "";
$nomBD = "";
$nomTable = "";
$lsEntetes = "";
$lsContenu = "";
$lsContenu2 = "";
$lsContenu3 = "";
$lsContenu4 = "";
$lsContenu5 = "";
$lsContenu6 = "";
$lsAffichage = "";
$lsMessage = "";

/*
 * fusion avec mes Class bibliothèques
 */
require_once 'Connexion.php';
require_once 'Metabase.class.php';
require_once 'TravailChaineCaractere.php';

$lcnx = Connexion::seConnecter("cours.ini"); //méthode se connecter via des propriétés. Pour vous connecter à votre base de donnée, changé le nom de la base de donnée dans le fichier INI par la votre.
Connexion::initialiserTransaction($lcnx); //beginTransaction() (on commence la transaction)

/*
 *  méthode pour récupérer les bases de données et les afficher dans un select avec des options
 */
$tBDs = Metabase::getBDsFromServeur($lcnx);
foreach ($tBDs as $bd) {
    if ($bd !== "information_schema" &&$bd !== "afg_paysagiste" &&$bd !== "mysql" && $bd !== "performance_schema" && $bd !== "phpmyadmin" && $bd !== "test") {// je garde que la base de donnée "cours"
        $listesBDs .= "<option>$bd</option>";
    }
}
Connexion::validerTransaction($lcnx); //commit() (validation de la transaction)
/*
 * affichage des tables de la BD
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
 * affichage final ...
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
              mode fetchAll
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
                $lsContenu.= "<form action='' method=''>\n";
                $lsCar = new TravailChaineCaractere(); //préparation à la méthode upper pour mettre en majuscule le nom de la table sélectionnée
                $lsContenu.= "<fieldset> \n <legend> " . $lsCar->upper($nomTable) . " </legend>\n";

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
                $lsContenu.= "<input type='submit' name='valider' value='GO'>\n";
                $lsContenu.= "</fieldset> \n </form>\n";

                /*
                 * optionnel (affichage graphique)
                 */
                $lsAffichage = $lsContenu;

                /*
                 * Utilisation de &lt; &gt; pour remplacer les chevrons "<" ">"
                 * on utilise le code ascii car sinon sur la page le text html ne s'affichage pas, les balises oui
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
                $lsContenu.= "<?php \n\n";
                $lsCar = new TravailChaineCaractere(); //préparation à la méthode camelize 
                $lsContenu.= "//--" . $lsCar->snakeToMajPremierelettreMot($nomTable) . ".php\n\n"; // snakeToMajPremierelettreMot pour mettre en majuscule la première lettre de chaque mot
                $lsContenu.= "Class " . $lsCar->snakeToMajPremierelettreMot($nomTable) . " {\n\n";
                $lsContenu.= "//--propriétés\n\n";
                for ($i = 0; $i < count($tEntetes); $i++) {
                    $lsSnake = new TravailChaineCaractere(); //préparation à la méthode camelize et snakeToUpperTitre
                    $lsContenu.= "private $" . $lsSnake->camelize($tEntetes[$i]) . ";\n";
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

                $lsContenu.="\n\n}\n\n?>";

                /*
                 * Utilisation de &lt; &gt; pour remplacer les chevrons "<" ">"
                 * on utilise le code ascii car sinon sur la page le text html ne s'affichage pas, les balises oui
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

                $lsCar = new TravailChaineCaractere(); //préparation à la méthode camelize 
                /*
                 * je recupère les colonnes de la table courante et la PK
                 */
                $lsPrimaryKey = Metabase::getColumnsNamesPKFromTable($lcnx, $nomBD, $nomTable);
                $lscolonnes = Metabase::getColumnsNamesFromTable($lcnx, $nomBD, $nomTable);
                for ($i = 0; $i < count($lscolonnes); $i++) {
                    /*
                     * boucle qui va travailler pour obtenir mes contenus pour les 3 ordres SQL INSERT, UPDATE et DELETE
                     */
                    $lsContenu2.= "&#36;" . $lsCar->snakeToMajPremierelettreMot($nomTable) . "->get" . $lsCar->snakeToMajPremierelettreMot($lscolonnes[$i]) . "(),"; // pour le tValeurs de INSERT
                    $lsContenu3.= $lscolonnes[$i] . ","; // pour l'ordre sql de INSERT
                    $lsContenu4.= "?,"; // pour l'ordre sql de INSERT

                    if ($lscolonnes !== $lsPrimaryKey[0]) {//pour le tValeurs de l'UPDATE et son ordre sql sans la PK qui ira dans le WHERE. la PK sera également utilisée pour le DELETE
                        $lsContenu5.= "&#36;" . $lsCar->snakeToMajPremierelettreMot($nomTable) . "->get" . $lsCar->snakeToMajPremierelettreMot($lscolonnes[$i]) . "(),";
                        $lsContenu6.= $lscolonnes[$i] . "= ?,";
                    }//fin if
                }//fin boucle
                /*
                 * nettoyage des concatenations en retirant les "," ou "?" en trop à la fin de mes chaines de caractères
                 */
                $lsContenu2 = substr($lsContenu2, 0, -1);
                $lsContenu3 = substr($lsContenu3, 0, -1);
                $lsContenu4 = substr($lsContenu4, 0, -1);
                $lsContenu6 = substr($lsContenu6, 0, -1);
                /*
                 * j'ajoute à tValeurs de UPTADE la PK à la fin
                 */
                $lsContenu5.= "&#36;" . $lsCar->snakeToMajPremierelettreMot($nomTable) . "->get" . $lsCar->snakeToMajPremierelettreMot($lsPrimaryKey[0]) . "()";

                /*
                 * on ecrit dans lsContenu la page php en mode text
                 */
                $lsContenu.= "<?php \n\n";
                $lsContenu.= "/**\n*Description of " . $lsCar->snakeToMajPremierelettreMot($nomTable) . "DAO.php\n*\n*@author Steve MORGEAT\n*\n*/\n\n"; // snakeToMajPremierelettreMot pour mettre en majuscule la première lettre de chaque mot
                $lsContenu.= "require_once '" . $lsCar->snakeToMajPremierelettreMot($nomTable) . ".php';\n\n";
                $lsContenu.= "Class " . $lsCar->snakeToMajPremierelettreMot($nomTable) . "DAO {\n\n";
                /*
                 * partie INSERT
                 */
                $lsContenu.= "//=================================================================================\n/**\n*INSERT\n* @param PDO &#36;pcnx\n* @param type $nomTable\n* @return string\n*/\n";
                $lsContenu.= "public static function insert(PDO &#36;pcnx, &#36;$nomTable) {\n\n\n";

                $lsContenu.= "&#36;tValeurs = array($lsContenu2);\n\n";
                $lsContenu.= "&#36;lsSQL = 'INSERT INTO $nomTable($lsContenu3) VALUES($lsContenu4)'\n\n";
                $lsContenu.= "try {\n\n";
                $lsContenu.= "&#36;lcmd = &#36;pcnx->prepare(&#36;lsSQL);\n";
                $lsContenu.= "&#36;lcmd->execute(&#36;tValeurs);\n";
                $lsContenu.= "&#36;lsMessage = &#36;lcmd->rowcount();\n";
                $lsContenu.= "} catch (PDOException &#36;e) {\n";
                $lsContenu.= "&#36;lsMessage = 'Echec de l'exécution : ' . htmlentities(&#36;e->getMessage());\n";
                $lsContenu.= "}\n";
                $lsContenu.= "return &#36;lsMessage;\n";
                $lsContenu.= "}\n";

                /*
                 * partie UPDATE
                 */

                $lsContenu.= "\n\n//=================================================================================\n/**\n*UPDATE\n* @param PDO &#36;pcnx\n* @param type $nomTable\n* @return string\n*/\n";
                $lsContenu.= "public static function insert(PDO &#36;pcnx, &#36;$nomTable) {\n\n\n";


                $lsContenu.= "&#36;tValeurs = array($lsContenu5);\n\n";
                $lsContenu.= "&#36;lsSQL = 'UPDATE $nomTable SET $lsContenu6 WHERE $lsPrimaryKey[0]= ?'\n\n";
                $lsContenu.= "try {\n\n";
                $lsContenu.= "&#36;lcmd = &#36;pcnx->prepare(&#36;lsSQL);\n";
                $lsContenu.= "&#36;lcmd->execute(&#36;tValeurs);\n";
                $lsContenu.= "&#36;lsMessage = &#36;lcmd->rowcount();\n";
                $lsContenu.= "} catch (PDOException &#36;e) {\n";
                $lsContenu.= "&#36;lsMessage = 'Echec de l'exécution : ' . htmlentities(&#36;e->getMessage());\n";
                $lsContenu.= "}\n";
                $lsContenu.= "return &#36;lsMessage;\n";
                $lsContenu.= "}\n";

                /*
                 * partie DELETE
                 */

                $lsContenu.= "\n\n//=================================================================================\n/**\n*DELETE\n* @param PDO &#36;pcnx\n* @param type $nomTable\n* @return string\n*/\n";
                $lsContenu.= "public static function insert(PDO &#36;pcnx, &#36;$nomTable) {\n\n\n";


                $lsContenu.= "&#36;tValeurs = array(" . $lsCar->snakeToMajPremierelettreMot($nomTable) . "->get" . $lsCar->snakeToMajPremierelettreMot($lsPrimaryKey[0]) . "());\n\n";
                $lsContenu.= "&#36;lsSQL = 'DELETE FROM $nomTable WHERE $lsPrimaryKey[0]= ?'\n\n";
                $lsContenu.= "try {\n\n";
                $lsContenu.= "&#36;lcmd = &#36;pcnx->prepare(&#36;lsSQL);\n";
                $lsContenu.= "&#36;lcmd->execute(&#36;tValeurs);\n";
                $lsContenu.= "&#36;lsMessage = &#36;lcmd->rowcount();\n";
                $lsContenu.= "} catch (PDOException &#36;e) {\n";
                $lsContenu.= "&#36;lsMessage = 'Echec de l'exécution : ' . htmlentities(&#36;e->getMessage());\n";
                $lsContenu.= "}\n";
                $lsContenu.= "return &#36;lsMessage;\n";
                $lsContenu.= "}\n\n";


                $lsContenu.= "}///Fin Class DTO\n"; // fin de la Class DTO sans les "SELECT"

                /*
                 * Utilisation de &lt; &gt; pour remplacer les chevrons "<" ">"
                 * on utilise le code ascii car sinon sur la page le text html ne s'affichage pas, les balises oui
                 * et les \n en <br> pour un affichage du code plus lisible.
                 */
                $lsContenu = str_replace("<", "&lt;", $lsContenu);
                $lsContenu = str_replace(">", "&gt;", $lsContenu);
                $lsContenu = nl2br($lsContenu);

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

