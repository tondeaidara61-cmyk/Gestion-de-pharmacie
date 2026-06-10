<?php
include_once __DIR__ . '../../config/database.php';

function ajout_medicament($database, $table, $nom, $categorie, $prix, $qte, $date)
{
     $data = $database->prepare("insert into $table (nom,categorie,prix,quantite_stock,date_peremption) values(:nom,:categorie,:prix,:qte,:date)");
     $data->execute(
          [
               ':nom' => $nom,
               ':categorie' => $categorie,
               ':prix' => $prix,
               ':qte' => $qte,
               ':date' => $date
          ]
     );
}

function Afficher($database, $table)
{
     $data = $database->prepare("select * from $table");
     $data->execute();
     return $data->fetchAll(PDO::FETCH_ASSOC);
}

function pagination($database, $table, $N_element, $page_actuelle)
{
     $total_medoc = (int) $database->query("select count(*) from $table")->fetchColumn();

     if ($page_actuelle <= 0) {
          throw new Exception("Numéro de page invalide....");
     }
     $page_total = ceil($total_medoc / $N_element);

     if ($page_actuelle > $page_total) {
          throw new exception("Cette page n\'existe pas....");
     }

     $offset = $N_element * ($page_actuelle - 1);

     $data  = $database->query("select * from $table limit $N_element offset $offset");
     $resultats = $data->fetchALL(PDO::FETCH_ASSOC);
     return [
          'donnees' => $resultats,
          'pages_total' => $page_total
     ];
}

function ajout_client($database, $table, $nom, $prenom, $telephone, $type_cl)
{
     $data = $database->prepare("insert into $table (nom , prenom , telephone , type_client) values (:nom , :pre , :tel ,:type ) ");
     $data->execute(
          [
               ':nom' => $nom,
               ':pre' => $prenom,
               ':tel' => $telephone,
               ':type' => $type_cl
          ]
     );
}

function Affiche_cibler($database, $table, $colonne, $indice)
{
     $data = $database->prepare("select * from $table where $colonne=$indice");
     $data->execute();
     return $data->fetch(PDO::FETCH_ASSOC);
}

function modifie_donnee($database, $table, $colonne, $valeur, $index, $indice)
{
     $data = $database->query("update $table set $colonne = $valeur where $index=$indice");
     $data->execute();
}
