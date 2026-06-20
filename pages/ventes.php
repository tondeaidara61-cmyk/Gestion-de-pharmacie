<?php
session_start();
include_once __DIR__ . '../../config/database.php';
include_once __DIR__ . '../../includes/fonctions.php';
$qte_insuffisant = false;
$qte_insuffisant_ids = '';

$fonctionnalite = ($_GET['fonct'] ?? 'Vente');

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
               }
          }
     }
}


if ($fonctionnalite == 'Panier') {

     $ids = $_SESSION['ids_panier'];
     $input_supprimer = json_decode(file_get_contents("php://input"), true);
     if (!empty($input_supprimer)) {
          $id_supprimer = $input_supprimer['id'];
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

     // Gestion d'erreur de stock insuffisant.
     if (isset($_SESSION['qte_insuffisant_id'])) {
          $qte_insuffisant_ids = $_SESSION['qte_insuffisant_id'];
          $qte_insuffisant = true;
          unset($_SESSION['qte_insuffisant_id']);
     }

     // validation de la vente.
     if (isset($_POST['valider'])) {
          $quantites = $_POST['quantite'];

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
               foreach ($ids as $key => $value_id) {
                    $qte = (int) $quantites[$key];
                    $verification_qte = Affiche_cibler($database, 'medicaments', 'id_medoc', $value_id);

                    // calcule du stock restant
                    $qte_stock = $verification_qte['quantite_stock'];
                    $qte_restant = $qte_stock - $qte;
                    // Reinitialisation du stock
                    modifie_donnee($database, 'medicaments', 'quantite_stock', $qte_restant, 'id_medoc', $value_id);
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
          <?= include_once '../includes/header.php'; ?>

     </header>

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
               </div>
          </section>
     <?php endif; ?>

     <?php if ($fonctionnalite == 'Panier'): ?>
          <form action="" method="post">
               <section class="container">
                    <h1>
                         Panier <em class="compteur"><?= count($ids) ?></em>
                    </h1>

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
                                             Prix: <em><?= $medoc_choisi['prix'] ?> FCFA</em>
                                        </p>
                                        <p>
                                             Stock: <em><?= $medoc_choisi['quantite_stock'] ?></em>
                                        </p>
                                        <p>
                                             <label for=""> Quantité:</label>
                                             <input type="number" min="1" value="1" name="quantite[]" class="from-control ">
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
               <?php if (count($ids) == 0): ?>
                    <p>
                         Panier vide......
                    </p>
                    <button type="button" class="btn btn-danger my-5" onclick="window.location='ventes.php?fonct=Vente'">
                         Retour
                    </button>
               <?php endif; ?>
          </form>
     <?php endif; ?>
</body>
<script src="../includes/fonctions.js"> </script>


</html>