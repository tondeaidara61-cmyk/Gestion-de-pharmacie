<?php
session_start();
include_once __DIR__ . '../../config/database.php';
include_once __DIR__ . '../../includes/fonctions.php';

$qte_insuffisant = false;
$qte_insuffisant_ids = '';


if (isset($_GET['fonct'])) {
     $fonctionnalite = $_GET['fonct'];
}elseif (!isset($_GET['fonct']) && isset($_SESSION['vente_valide'])) {
     $fonctionnalite = 'Facture';
    unset($_SESSION['vente_valide']);
}
else{
     $fonctionnalite= 'Vente';
}

if ($fonctionnalite == 'Vente') {

     if (!isset($_SESSION['ids_panier'])) {
          // unset($_SESSION['ids_panier']);
          $_SESSION['ids_panier'] = [];
     }
     $input = json_decode(file_get_contents("php://input"), true);
     if (!empty($input)) {
          $id = $input["id"];

          if ($id > 0) {
               if (!in_array($id, $_SESSION['ids_panier'])) {
                    $_SESSION['ids_panier'][] = $id;
                    echo json_encode(["status" => "ok"]);
               }
          }
     }
}



if ($fonctionnalite == 'Panier') {

if (!isset($_SESSION['id_cl']) || !isset($_SESSION['remise'])) {
     
 $_SESSION['id_cl'] = null;
     $_SESSION['remise']=false;

}
  

     $ids = $_SESSION['ids_panier'];
     $input_panier = json_decode(file_get_contents("php://input"), true);
     if (!empty($input_panier)) {
          if ($input_panier['action'] === "Suppression") {

               $id_supprimer = $input_panier['id'];
               if (in_array($id_supprimer, $_SESSION['ids_panier'])) {
                    $ids_panier = $_SESSION['ids_panier'];
                    unset($_SESSION['ids_panier']);
                    $_SESSION['ids_panier'] = [];

                    foreach ($ids_panier as $id_panier) {
                         if ($id_panier != $id_supprimer) {
                              $_SESSION['ids_panier'][] = $id_panier;
                         }
                    }
               }

               echo json_encode(["status" => "ok"]);
               exit();
          }

          if ($input_panier['action'] === "EnvoiIdentifiant") {
               $identifiant = $input_panier['identifiant'];

               // Chercher par identifiant d'abord
               $client = Affiche_cibler($database, 'client', 'identifiant', $identifiant);

               // Si pas trouvé, chercher par téléphone
               if (!$client) {
                    $client = Affiche_cibler($database, 'client', 'telephone', $identifiant);
               }

               // Toujours pas trouvé
               if (!$client) {
                    echo json_encode(["status" => "non", "recu" => "Identifiant incorrect"]);
                    exit();
               }

               $_SESSION['id_cl'] = $client['id_client'];
                 $_SESSION['remise']=true;
               // Vérifier type client
               if ($client['type_client'] == 'regulier') {

                    echo json_encode(["status" => "ok", "recu" => "Client régulier, réduction de 5%"]);
                    exit();
               }

               // Compter ses ventes passées
               $ventes = Affiche_tous_cibler($database, 'vente', 'id_client', $client['id_client']);

               if ($ventes && count($ventes) >= 5) {
                     $_SESSION['remise']=true;
                    echo json_encode(["status" => "ok", "recu" => "Client régulier, réduction de 5% accordée"]);
               } else {
                    echo json_encode(["status" => "non", "recu" => "Client occasionnel, réduction de 0%"]);
               }
               exit();
          }
     }


     // Gestion d'erreur de stock insuffisant.
     if (isset($_SESSION['qte_insuffisant_id'])) {
          $qte_insuffisant_ids = $_SESSION['qte_insuffisant_id'];
          $qte_insuffisant = true;
          unset($_SESSION['qte_insuffisant_id']);
     }

     // validation de la vente.
     if (isset($_POST['valider'])) {
          $quantites = $_POST['quantite'];
          $identifiant = $_POST['identifiant'];

          // vérification des stock.
          foreach ($ids as $key => $value_id) {
               $qte = (int) $quantites[$key];

               $verification_qte = Affiche_cibler($database, 'medicaments', 'id_medoc', $value_id);
               $qte_stock = $verification_qte['quantite_stock'];

               if (!isset($_SESSION['qte_insuffisant_id'])) {
                    $_SESSION['qte_insuffisant_id'] = [];
               }

               if ($qte_stock < $qte) {
                    $_SESSION['qte_insuffisant_id'][] = $value_id;
               }
          }

          if (count($_SESSION['qte_insuffisant_id']) == 0) {

                $id_client = $_SESSION['id_cl'];
               
               

               if ($_SESSION['remise'] == true) {
                    $remise = '5%';
               }else{
                     $remise = '0%';
               }

               $premier_vente =  true;
               $id_vente = null;

               foreach ($ids as $key => $value_id) {
                    $qte = (int) $quantites[$key];
                    $verification = Affiche_cibler($database, 'medicaments', 'id_medoc', $value_id);

                    
                    // calcule du stock restant
                    $qte_stock = $verification['quantite_stock'];
                    $qte_restant = $qte_stock - $qte;
                    $prix_unitaire = $verification['prix'];
                    if ($remise == '5%') {
                           $montant = ($prix_unitaire * $qte) - ($prix_unitaire * $qte) * 0.05;
                    }
                    else{
                          $montant = $prix_unitaire * $qte;
                    }
                   
                    $id_medoc = $verification['id_medoc'];
                   
                    $date = Date('Y-m-d');

                    if ($premier_vente == true) {
                        AjouteVente($database,'vente',$date,$montant,$remise,$id_client);
                        $id_vente =$database -> lastInsertId();
                         Ajout_Ligne_Vente($database,'ligne_vente',$id_vente,$id_medoc,$qte,$prix_unitaire);
                         $premier_vente = false;

                    } else {
                          AjouteVente($database,'vente',$date,$montant,$remise,$id_client);
                         Ajout_Ligne_Vente($database,'ligne_vente',$id_vente,$id_medoc,$qte,$prix_unitaire);
                    }
                    
                    // Reinitialisation du stock
                      modifie_donnee($database, 'medicaments', 'quantite_stock', $qte_restant, 'id_medoc', $value_id);
               }
                if ($premier_vente == false) {
               unset($_SESSION['ids_panier']);
               unset($_SESSION['id_cl']);

               $_SESSION['id_vente'] = $id_vente;
               $_SESSION['vente_valide'] = true;

               header('location: ventes.php');
               exit();
                }
          }
     }
}

$medicaments = Afficher($database, 'medicaments');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Ventes</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
     <link rel="stylesheet" href="../style/vente.css?v=<?= time() ?>">
</head>

<body>
       
     <header>
        <?php
       //  include_once  '../includes/header.php';
          ?>
     </header> 

      <!-- Début page de  vente -->

     <?php if ($fonctionnalite == 'Vente'): ?>

          <section class="container-fluid ">
               <a href="?fonct=Panier">Panier</a>
               <div class="container-fluid py-5">
                    <h1>Médicaments Disponible en Vente</h1>
               </div>
               <div class="container-fluid ">
                    <h3>
                         Comprimers
                    </h3>
                    <div class="row  gx-4">

                         <?php foreach ($medicaments as $medoc): ?>

                              <?php if ($medoc['categorie'] == 'comprimer' && $medoc['quantite_stock'] > 0): ?>
                                   <article class="col-3 px-2 mb-5 mt-2">
                                        <div class="card-content px-3 pt-3">
                                             <img src="../images/image2.png" class="img" alt="">
                                             <p class="nom">
                                                  <?= $medoc['nom'] ?>
                                                  <button type="button" onclick="AjoutePanier(<?= $medoc['id_medoc'] ?>)" class="ajoutepanier">
                                                       + Panier
                                                  </button>
                                             </p>
                                             <p class="prix">
                                                  Prix: <em><?= $medoc['prix'] ?> FCFA</em>
                                             </p>
                                        </div>
                                   </article>
                              <?php endif; ?>
                         <?php endforeach; ?>

                    </div>

                    <h3>
                         Produits Cosmétique
                    </h3>
                    <div class="row  gx-4">

                         <?php foreach ($medicaments as $medoc): ?>

                              <?php if ($medoc['categorie'] == 'produit cosmétique' && $medoc['quantite_stock'] > 0): ?>
                                   <article class="col-3 px-2 mb-5 mt-2 ">
                                        <div class="card-content px-3 pt-3 ">
                                             <img src="../images/image2.png" class="img" alt="">
                                             <p class="nom ">
                                                  <?= $medoc['nom'] ?>
                                                  <button type="button" onclick="AjoutePanier(<?= $medoc['id_medoc'] ?>)" class="ajoutepanier">
                                                       + Panier
                                                  </button>
                                             </p>
                                             <p class="prix">
                                                  Prix: <em> <?= $medoc['prix'] ?> FCFA </em>
                                             </p>
                                        </div>
                                   </article>
                              <?php endif; ?>
                         <?php endforeach; ?>

                    </div>

                    
                    <h3>
                         Injections
                    </h3>
                    <div class="row  gx-4">

                         <?php foreach ($medicaments as $medoc): ?>

                              <?php if ($medoc['categorie'] == 'injection' && $medoc['quantite_stock'] > 0): ?>
                                   <article class="col-3 px-2 mb-5 mt-2 ">
                                        <div class="card-content px-3 pt-3 ">
                                             <img src="../images/image2.png" class="img" alt="">
                                             <p class="nom ">
                                                  <?= $medoc['nom'] ?>
                                                  <button type="button" onclick="AjoutePanier(<?= $medoc['id_medoc'] ?>)" class="ajoutepanier">
                                                       + Panier
                                                  </button>
                                             </p>
                                             <p class="prix">
                                                  Prix: <em> <?= $medoc['prix'] ?> FCFA </em>
                                             </p>
                                        </div>
                                   </article>
                              <?php endif; ?>
                         <?php endforeach; ?>

                    </div>
               </div>
          </section>
     <?php endif; ?>

      <!-- Fin page de vente -->

      <!-- Début Panier de la vente -->

     <?php if ($fonctionnalite == 'Panier'): ?>
          <form action="" method="post">
               <section class="container">
                    <h1>
                         Panier
                         <?php if (!empty($ids)): ?>
                              <em class="compteur"><?= count($ids) ?></em>
                         <?php endif; ?>
                    </h1>
                    <?php if (!empty($ids)): ?>
                         <?php if (count($ids) != 0): ?>
                              <?php foreach ($ids as $id): ?>
                                   <?php
                                   $medoc_choisi = Affiche_cibler($database, 'medicaments', 'id_medoc', $id);
                                   ?>

                                   <div class="col-10 contenu-panier" id="produit<?= $medoc_choisi['id_medoc'] ?>">

                                        <img src="../images/image2.png" class="img-panier" alt="">
                                        <nav>
                                             <p class="nom">
                                                  <?= $medoc_choisi['nom'] ?>
                                             </p>
                                             <p class="prix">
                                                  Prix: <em id="prix-<?= $medoc_choisi['id_medoc'] ?>"><?= $medoc_choisi['prix'] ?> FCFA</em>
                                             </p>
                                             <p>
                                                  Stock: <em><?= $medoc_choisi['quantite_stock'] ?></em>
                                             </p>
                                             <p>
                                                  <label for=""> Quantité:</label>
                                                  <input type="number" min="1" value="1" name="quantite[]" class="from-control quantite" oninput="Calculator()" data-prix="<?= $medoc_choisi['prix'] ?>" data-id="<?= $medoc_choisi['id_medoc'] ?>">
                                             </p>
                                             <?php if ($qte_insuffisant == true): ?>
                                                  <?php foreach ($qte_insuffisant_ids as $id_qte): ?>
                                                       <?php if ($id_qte == $medoc_choisi['id_medoc']): ?>
                                                            <p class="erreur">
                                                                 Stock insuffisant !
                                                            </p>
                                                       <?php endif; ?>
                                                  <?php endforeach; ?>
                                             <?php endif; ?>
                                             <p>
                                                  <button type="button" class="btn btn-outline-danger" onclick="Supprimer(<?= $medoc_choisi['id_medoc'] ?>)">
                                                       Supprimer
                                                  </button>
                                             </p>
                                        </nav>
                                   </div>
                              <?php endforeach; ?>
                              <nav>
                                   <p>
                                        <label for="" class="form-label">Identifiant</label>
                                        <input type="text" placeholder="identifiant / numéro" name="identifiant" oninput="EnvoiIdentifiant(this.value)"> <br>
                                        <em id="reponse"> </em>
                                   </p>

                                   <p>
                                        TOTAL: <em id="total"> </em>
                                   </p>
                              </nav>
                         <?php endif; ?>
               </section>
               <div class="container">
                    <?php if (count($ids) != 0): ?>
                         <button type="button" name="annuler" class="form-control btn  btn-outline-danger my-4">
                              Annuler
                         </button>
                         <button type="submit" name="valider" class="form-control btn  btn-outline-primary mb-5">
                              Valider
                         </button>
                    <?php endif; ?>
               </div>
          <?php endif; ?>
          <?php if (empty($ids)): ?>
               <p>
                    Panier vide......
               </p>
               <button type="button" class="btn btn-danger my-5" onclick="window.location='ventes.php?fonct=Vente'">
                    Retour
               </button>
          <?php endif; ?>
          </form>
     <?php endif; ?>
 <!-- Fin Panier de la vente -->

     <!-- Début Facture de la vente -->

     <?php if($fonctionnalite == 'Facture'): ?>

        <?php
          $id_vente = $_SESSION['id_vente'];
          $vente = Affiche_cibler($database,'vente','id_vente',$id_vente);
          $id_client_facture = $vente['id_client'];
         
          $total_facture = 0;

          $info_vente = get_lignes_vente($database,$id_vente);

          if ($id_client_facture != null) {
               $client_facture= Affiche_cibler($database,'client','id_client',$id_client_facture);
               $info_client = $client_facture['nom'] .' '. $client_facture['prenom'];
          }
          else
               {
                    $info_client = 'Anonyme';
               }
           $numero = numero_facture($id_vente,$vente['date_vent']);
           ?>
          
     <section id="section_facture">

          <div class="infos">
               <p>Numéro facture : <strong><?= $numero ?></strong></p>
               <p>Date : <strong><?= $vente['date_vent'] ?></strong></p>
               <p>Heure : <strong><?= date('H:i:s') ?></strong></p>
               <p>Client : <strong><?= $info_client ?></strong></p>
          </div>

          <table>
               <thead>
                    <tr>
                         <th>Produit</th>
                         <th>Catégorie</th>
                         <th>Qté</th>
                         <th>Prix unitaire</th>
                          <th>Remise Appliquée</th>

                    </tr>
               </thead>
               <tbody>
                    <?php foreach ($info_vente as $ligne): ?>
                         <?php
                        // $info_produit = Affiche_cibler($database,'medicaments','id_medoc',$ligne['id_medicament']);
                               ?>
                         <tr>
                              <td><?= $ligne['medicament'] ?></td>
                              <td><?= $ligne['categorie'] ?></td>
                              <td><?= $ligne['quantite_vendue'] ?></td>
                              <td><?= number_format($ligne['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                                <td><?= $ligne['remise_appliquee'] ?></td>
                         </tr>
                         <?php $total_facture += $ligne['montant_total']; ?>
                    <?php endforeach; ?>
               </tbody>
          </table>
          <br>
          <div class="totaux">
               <div>
                    <span>Total</span>
                    <span><?= number_format($total_facture, 0, ',', ' ') ?> FCFA</span>
               </div>
          </div>
          <br><br><br>

          <div class="boutons">
               <button onclick="window.print()">🖨️ Imprimer</button>
          </div>
     </section>

     <?php endif; ?>

      <!-- Fin Facture de la vente -->

</body>
<script src="../includes/fonctions.js"> </script>
<script>
     Calculator()
</script>

</html>