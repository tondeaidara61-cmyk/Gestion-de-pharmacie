let ids = []
function AjoutePanier(id) {

     fetch("ventes.php?fonct=Vente", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: id })
     })

          .then(res => res.json())
          .then(data => {
               if (data.status === "ok") {
                    ids.push(id)
                    console.log(ids)
               }
          })
          .catch(err => console.error(err));
}

function Supprimer(id) {
     fetch("ventes.php?fonct=Panier", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "Suppression", id: id })
     })
          .then(res => res.json())
          .then(data => {
               if (data.status === "ok") {
                    document.getElementById("produit" + id).remove();
                    window.location.reload();
               }
          })
}

function Calculator(reducteur = reduction) {
     let total = 0
     let prix = 0
     const inputs = document.querySelectorAll('.quantite')

     inputs.forEach(input => {
          const quantite = parseInt(input.value) || 0
          const prix_u = parseFloat(input.dataset.prix) || 0
          const id = input.dataset.id

         if (reducteur === null) {
                prix = quantite * prix_u
          }else
          {
                 prix =( quantite * prix_u) - ( quantite * prix_u) * 0.05
          }
          document.getElementById('prix-' + id).innerHTML = prix + ' FCFA'

          total += prix
     })
     document.getElementById('total').innerHTML = total + ' FCFA'
}

let time;
let reduction =null
function EnvoiIdentifiant(identifiant) {

     if (identifiant.length === 0) {
          document.getElementById('reponse').innerHTML = '';
          return;
     }

     clearTimeout(time);
     time = setTimeout(() => {

          fetch("ventes.php?fonct=Panier", {
               method: "POST",
               headers: { "Content-Type": "application/json" },
               body: JSON.stringify({ action: "EnvoiIdentifiant", identifiant: identifiant })

          })


               .then(res => res.json())
               .then(data => {
                    if (data.status === "ok") {
                         console.log(data)
                         if (data['status'] === 'ok') {
                               reduction = 'ok'
                              Calculator(reduction)
                         }
                         document.getElementById('reponse').innerHTML = data['recu']
                    }
               })

     }, 500)
}
