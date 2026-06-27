<?php
session_start();
include_once __DIR__ . '../../config/database.php';
include_once __DIR__ . '../../includes/fonctions.php';

$fonctionnalite = ($_GET['fonct'] ?? 'Ajouter');

$succes = false;

if (isset($_SESSION['succes'])) {
     $succes = $_SESSION['succes'];
     unset($_SESSION['succes']);
}

if (isset($_POST['envoyer'])) {
     $nom = htmlspecialchars($_POST['nom']);
     $prenom = htmlspecialchars($_POST['prenom']);
     $telephone = htmlspecialchars($_POST['telephone']);
     $type_cl = 'occasionnel';
     $identifiant = null;

     if (!empty($nom) && !empty($prenom) && !empty($telephone)) {

          $tab_tel = str_split($telephone);

          $identifiant = $tab_tel[2] . $tab_tel[3] . $tab_tel[4]
               . strtoupper($nom[0]) . strtoupper($prenom[0])
               . date('H') . date('s');

          ajout_client($database, 'client', $nom, $prenom, $telephone, $type_cl, $identifiant);

          $_SESSION['succes'] = true;
          header('location: clients.php?fonct=Ajouter');
          exit();
     }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Clients</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
     <link rel="stylesheet" href="../style/client.css?v=<?= time() ?>">
</head>

<body>
     <header>
          <?php include_once '../includes/header.php'; ?>
     </header>

     <?php if ($fonctionnalite == 'Ajouter'): ?>
          <section class="container-fluid px-lg-5 pt-5">
               <nav class="col text-center py-2 mb-5 bg-t-secondary">
                    <h1>
                         Enregistrement des Clients
                    </h1>
               </nav>
               <div class="row pt-1">
                    <form action="" method="post" class="col-5 bg-t-secondary px-5 mx-5">

                         <p class="mb3 my-5">
                              <label for="" class="form-label">Nom</label>
                              <input type="text" required placeholder="--- Entrer le nom du client ---" name="nom" class="form-control ">
                         </p>
                         <p class="mb-3 my-5">
                              <label for="" class="form-label">Prenom</label>
                              <input type="text" required placeholder="--- Entrer le prenom du client ---" name="prenom" class="form-control ">
                         </p>
                         <p class="mb3 my-5">
                              <label for="" class="form-label">Téléphone</label>
                              <input type="number" min="0" required placeholder="--- Entrer le numéro du client ---" name="telephone" class="form-control ">
                         </p>

                         <br>
                         <?php if ($succes == true): ?>
                              <p id="succes">
                                   Client enregistrer avec succès
                              </p>
                         <?php endif; ?>
                         <p class="mb3 my-4">
                              <button type="submit" name="envoyer" id="btn" class="form-control btn btn-primary">
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

</body>

</html>