function AjoutePanier(id) {

     fetch("ventes.php?fonct=Vente", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: id })
     })
          /*   .then(res => res.json())
             .then(data => {
                  if (data.status === "ok") {
                       alert("Produit ajouté au panier !");
                       // ou mettre à jour un compteur panier sans recharger
                  }
             })*/
          .catch(err => console.error(err));
}

function Supprimer(id) {
     fetch("ventes.php?fonct=Panier", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: id })
     })
          .then(res => res.json())
          .then(data => {
               if (data.status === "ok") {
                    document.getElementById("produit" + id).remove();
               }
          })

}