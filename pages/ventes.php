<?php
session_start();
include_once __DIR__ . '../../config/database.php';
include_once __DIR__ . '../../includes/fonctions.php';

$fonctionnalite = ($_GET['fonct'] ?? 'Vente');

if (!isset($_SESSION['ids_'])) {
     $_SESSION['ids_'] = [];
}


$id = (int) ($_GET['id'] ?? 0);


if ($id > 0) {
     if (!in_array($id, $_SESSION['ids_'])) {
          $_SESSION['ids_'][] = $id;
     }
}



if ($fonctionnalite == 'Panier') {

     $ids = $_SESSION['ids_'];
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
          <?php include_once '../includes/header.php'; ?>
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
                                                  <a href="?id=<?= $medoc['id_medoc'] ?>">+ panier</a>
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
                                                  <a href="?id=<?= $medoc['id_medoc'] ?>">+ panier</a>
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

          <section class="container">
               <h1>
                    Panier
               </h1>

               <?php if (count($ids) != 0): ?>
                    <?php foreach ($ids as $id): ?>
                         <?php
                         $medoc_choisi = Affiche_cibler($database, 'medicaments', 'id_medoc', $id);
                         ?>
                         <div class="col-10">

                              <img src="../images/image2.png" class="img" alt="">
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
                                        Quantité: <input type="number" min="1" value="1" name="quantite" class="from-control">
                                   </p>
                                   <p>
                                        <button type="button" class="btn btn-danger">
                                             Supprimer
                                        </button>
                                   </p>
                              </nav>

                         </div>
                    <?php endforeach; ?>
               <?php endif; ?>
          </section>

     <?php endif; ?>
</body>

</html>