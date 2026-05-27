<?php
try {
     $database = new PDO("mysql:host=localhost;dbname=gestion_pharmacie;charset=utf8", "root", "");
} catch (PDOException $th) {
     echo "Erreur de connexion a la base de donnée....";
}
