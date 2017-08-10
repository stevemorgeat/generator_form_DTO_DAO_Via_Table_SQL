<?php


require_once('TravailChaineCaractere.php');

//variable de test
$test="bonjour_le_test";
// nouveau objet
$camel = new TravailChaineCaractere;
//on camelize
$r1 = $camel->camelize($test);
$r2 = $camel->snakeToUpperTitre($test);
$test = "test";
$r3 = $camel->upper($test);
echo $r1."<br>";
echo $r2."<br>";
echo $r3."<br>";

/*
 * reusltat 1 bonjourLeTest
 * reusltat 2 BONJOUR LE TEST
 * reusltat 3 TEST
 */