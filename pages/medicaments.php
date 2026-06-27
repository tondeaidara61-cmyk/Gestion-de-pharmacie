<?php
session_start();
include_once __DIR__ . '../../config/database.php';
include_once __DIR__ . '../../includes/fonctions.php';

$fonctionnalite = ($_GET['fonct'] ?? 'Afficher');


$page_actuelle = (int) ($_GET['page'] ?? 1);
$resultats = pagination($database, 'medicaments', '3', $page_actuelle);
$medicaments = $resultats['donnees'];
$page_total = $resultats['pages_total'];


$succes = false;
if (isset($_SESSION['succes'])) {
     $succes = $_SESSION['succes'];
     unset($_SESSION['succes']);
}

if (isset($_POST['envoyer'])) {

     $nom = htmlspecialchars($_POST['nom']);
     $categorie = htmlspecialchars($_POST['categorie']);
     $quantite = htmlspecialchars($_POST['quantite']);
     $prix_unitaire = htmlspecialchars($_POST['prix']);
     $date_peremption = htmlspecialchars($_POST['date']);

     if (!empty($nom) && !empty($categorie) && !empty($quantite) && !empty($prix_unitaire) && !empty($date_peremption)) {

          ajout_medicament($database, 'medicaments', $nom, $categorie, $prix_unitaire, $quantite, $date_peremption);

          $_SESSION['succes'] = true;
          header('location: medicaments.php?fonct=Ajouter');
          exit();
     } else {
          $_SESSION['erreur'] = true;
          header('location: medicaments.php?fonct=Ajouter');
          exit();
     }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Medicaments</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
     <link rel="stylesheet" href="../style/medoc.css?v=<?= time() ?>">
</head>

<body>
     <header>
          <?php include_once  '../includes/header.php';
          ?>
     </header>
     <?php if ($fonctionnalite == 'Ajouter'): ?>
          <section class="container-fluid px-lg-5 pt-5">
               <nav class="col text-center py-2 mb-5 bg-t-secondary">
                    <h1>
                         Enregistrement des médicaments
                    </h1>
               </nav>
               <div class="row pt-1">
                    <form action="" method="post" class="col-5 bg-t-secondary px-5 mx-5">

                         <p class="mb3 my-4">
                              <label for="" class="form-label">Nom</label>
                              <input type="text" required placeholder="--- Entrer le nom du medicament ---" name="nom" class="form-control ">
                         </p>
                         <p class="mb-3 my-4">
                              <label for="" class="form-label">Catégorie</label>
                              <select name="categorie" id="" class="form-control ">
                                   <option value="">----- Choisir -----</option>
                                   <option value="comprimer">Comprimer</option>
                                   <option value="produit cosmétique"> Produit Cosmétique</option>
                                   <option value="injection">Injection</option>
                                   <option value="siro">Siro</option>
                                   <option value="autre">Autre</option>
                              </select>
                         </p>
                         <p class="mb3 my-4">
                              <label for="" class="form-label">Prix Unitaire</label>
                              <input type="number" min="0" required placeholder="--- Entrer le prix ---" name="prix" class="form-control " step="any">
                         </p>
                         <p class="mb3 my-4">
                              <label for="" class="form-label">Quantité</label>
                              <input type="number" min="1" required placeholder="--- Entrer la quantité ---" name="quantite" class="form-control ">
                         </p>
                         <p class="mb3 my-4">
                              <label for="" class="form-label">Date de peremption</label>
                              <input type="text" name="date" required placeholder="--- Entrer la date de peremption ---" class="form-control ">
                         </p>
                         <br>
                         <?php if ($succes == true): ?>
                              <p id="succes">
                                   Médicaments enregistrer avec succès
                              </p>
                         <?php endif; ?>
                         <p class="mb3 my-4">
                              <button type="submit" id="btn" name="envoyer" class="form-control btn btn-primary">
                                   Ajouter
                              </button>
                         </p>
                    </form>

                    <article class="col-6  text-center px-5 art1 mx-3">
                         <nav class="pt-5">
                              <h2>
                                   PharmaCare
                              </h2>
                              <p>
                                   "Votre santé, notre priorité"
                              </p>
                         </nav>
                    </article>
               </div>
          </section>
     <?php endif; ?>

     <?php if ($fonctionnalite == 'Afficher'): ?>
          <section class="container-fluide  px-lg-5 pt-5" id="section2">
               <div class="col text-center py-2 mb-5 bg-t-secondary">
                    <h1>
                         Liste des médicaments
                    </h1>
               </div>
               <div class="container col-12 pt-5 bg-t-secondary mb-5 pb-5" id="s-div2">
                    <table class="table">
                         <thead>
                              <tr>
                                   <th>
                                        N°
                                   </th>
                                   <th>
                                        Nom
                                   </th>
                                   <th>
                                        Catégorie
                                   </th>
                                   <th>
                                        Prix Unitaire
                                   </th>
                                   <th>
                                        Quantité
                                   </th>
                                   <th>
                                        Date de peremption
                                   </th>
                              </tr>
                         </thead>
                         <tbody>
                              <?php foreach ($medicaments as $medoc):  ?>
                                   <tr>
                                        <td>
                                             <?= $medoc['id_medoc'] ?>
                                        </td>
                                        <td>
                                             <?= $medoc['nom'] ?>
                                        </td>
                                        <td>
                                             <?= $medoc['categorie'] ?>
                                        </td>
                                        <td>
                                             <?= $medoc['prix'] ?>
                                        </td>
                                        <td>
                                             <?= $medoc['quantite_stock'] ?>
                                        </td>
                                        <td>
                                             <?= $medoc['date_peremption'] ?>
                                        </td>
                                   </tr>
                              <?php endforeach; ?>
                         </tbody>
                    </table>
                    <nav aria-label="Page navigation example " class="position-relative">
                         <ul class="pagination position-absolute top-0 end-0">
                              <?php if ($page_actuelle > 1): ?>
                                   <li class="page-item"><a class="page-link" href="medicaments.php?page=<?= $page_actuelle - 1 ?>">Précédent</a></li>
                              <?php endif;  ?>

                              <?php if ($page_actuelle < $page_total): ?>
                                   <li class="page-item"><a class="page-link" href="medicaments.php?page=<?= $page_actuelle + 1 ?>">Next</a></li>
                              <?php endif;  ?>
                         </ul>
                    </nav>

               </div>
               <p class="container">
                    <button class="btn btn-outline-secondary" type="button" onclick="window.location=''" id="retour">
                         Retour
                    </button>
                    <button class="btn btn-outline-primary" type="button" onclick="window.location='medicaments.php?fonct=Ajouter'" id="ajouter">
                         Ajouter des médicaments
                    </button>
               </p>
          </section>
     <?php endif; ?>

     <?php if ($fonctionnalite == 'Alerte'): ?>
     <?php endif; ?>
</body>

</html>