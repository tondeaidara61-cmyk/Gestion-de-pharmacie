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

function ajout_client($database, $table, $nom, $prenom, $telephone, $type_cl, $identifiant)
{
     $data = $database->prepare("insert into $table (nom , prenom , telephone , type_client,identifiant) values (:nom , :pre , :tel ,:type,:idet ) ");
     $data->execute(
          [
               ':nom' => $nom,
               ':pre' => $prenom,
               ':tel' => $telephone,
               ':type' => $type_cl,
               ':idet' => $identifiant
          ]
     );
}
function Affiche_cibler($database, $table, $colonne, $indice)
{
     $data = $database->prepare("SELECT * FROM $table WHERE $colonne = :indice");
     $data->execute([':indice' => $indice]);  // PDO gère les guillemets automatiquement 
     return $data->fetch(PDO::FETCH_ASSOC);
}


function Affiche_tous_cibler($database, $table, $colonne, $indice)
{
     $data = $database->prepare("SELECT * FROM $table WHERE $colonne = :indice");
     $data->execute([':indice' => $indice]);  // PDO gère les guillemets automatiquement 
     return $data->fetchALL(PDO::FETCH_ASSOC);
}

function modifie_donnee($database, $table, $colonne, $valeur, $index, $indice)
{
     $data = $database->query("update $table set $colonne = $valeur where $index=$indice");
     $data->execute();
}

function AjouteVente($database, $table, $date, $montant, $remise, $id_client)
{
     $data = $database->prepare("insert into $table(date_vent,montant_total,remise_appliquee,id_client) values (:dte,:mtt,:rse,:idC)");
     $data->execute(
          [
               ':dte' => $date,
               ':mtt' => $montant,
               ':rse' => $remise,
               ':idC' => $id_client
          ]
     );
}


function Ajout_Ligne_Vente($database, $table, $id_vente, $id_medoc, $quantite, $prix_u)
{
     $data = $database->prepare("insert into $table (id_vente,id_medicament,quantite_vendue,prix_unitaire) values (:idV,:idM,:qte,:PU)");
     $data->execute(
          [
               ':idV' => $id_vente,
               ':idM' => $id_medoc,
               ':qte' => $quantite,
               ':PU' => $prix_u
          ]
     );
}

// Générer le numéro de facture
function numero_facture($id_vente, $date)
{
     return "PhC-" . date('Ymd', strtotime($date)) . "-" . str_pad($id_vente, 3, '0', STR_PAD_LEFT);
}
function get_lignes_vente($database, $id_vente)
{
    $data = $database->prepare("
        SELECT 
            v.id_vente,
            v.date_vent,
            v.montant_total,
            v.remise_appliquee,
            c.nom AS client_nom,
            c.prenom AS client_prenom,
            c.telephone,
            m.nom AS medicament,
            m.categorie,
            lv.quantite_vendue,
            lv.prix_unitaire,
            (lv.quantite_vendue * lv.prix_unitaire) AS sous_total
        FROM vente v
        JOIN ligne_vente lv ON v.id_vente = lv.id_vente
        JOIN medicaments m ON lv.id_medicament = m.id_medoc
        LEFT JOIN client c ON v.id_client = c.id_client
        WHERE v.id_vente = :id_vente
    ");
    
    $data->execute([':id_vente' => $id_vente]);
    return $data->fetchAll(PDO::FETCH_ASSOC);
}