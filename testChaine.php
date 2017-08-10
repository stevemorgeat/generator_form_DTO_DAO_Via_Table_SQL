<?php


require_once('TravailChaineCaractere.php');

//variable de test
$test="bonjour_le_test";
// nouveau objet
$camel = new TravailChaineCaractere;
//on camelize
$r1 = $camel->camelize("$test");
$r2 = $camel->snakeToUpperTitre("$test");
echo $r1."<br>";
echo $r2."<br>";

//reusltat bonjourLeTest