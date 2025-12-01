---

## Principe

---

Lister et administrer les inscriptions au club.

## Gestion des inscriptions

Afficher la liste des inscriptions en fonction du filtre.

### Filtres

* **3 séances en cours** : membre inscrit pour la première fois au club, ayant participé à **moins de 3 séances**.
* **3 séances terminées** : membre inscrit pour la première fois au club, ayant participé à **3 séances ou plus**.
* **Nouvelles inscriptions à valider** : membre ayant terminé la période d’essai et s’étant inscrit pour la saison.
* **Renouvellement à valider** : membre inscrit la saison précédente et qui s’est inscrit pour la nouvelle saison.
* **Licences non renouvelées** : membre inscrit la saison précédente et n’ayant pas renouvelé sa licence.
* **En cours de création** : membre ayant commencé à remplir le formulaire d’inscription mais n’ayant pas validé le récapitulatif.
* **Inscrire à la FFVélo** : membre dont l’inscription annuelle a été réceptionnée et qui doit être inscrit à la FFVélo.

![image](/wiki/img/inscriptions/licences_to_receive.png)

🛈 **NOTE :** La liste filtrée est exportable via le menu **Exporter la sélection**, et les adresses mail peuvent être copiées dans le presse-papier via le menu **Copier les emails de la sélection**.

🛈 **NOTE :** Dans l’affichage des listes pour la période d’essai, le nombre indiqué correspond à :
**nombre de participations / nombre d’inscriptions à une sortie**.

---

### Réception d’un dossier d’inscription

Pour les membres inscrits aux 3 séances d’essai ou à la licence annuelle, un bouton permet d’indiquer que le dossier d’inscription a été reçu par le club. Une pop-up permet de confirmer l’action.

![image](/wiki/img/inscriptions/recepted.png)

### Inscription à la FFVélo

Pour les membres dont le dossier d’inscription annuel a été réceptionné, un bouton permet d’indiquer que l’inscription a été enregistrée sur le site de la FFVélo. Une pop-up permet de confirmer l’action.

![image](/wiki/img/inscriptions/registered.png)

### Inscription incomplète

En cas de dossier incomplet, il est possible d’envoyer un message à l’adhérent pour lui indiquer les informations manquantes ou erronées.
À la validation du formulaire, l’état de l’inscription repasse en **inscription en cours de création**.
L’action est accessible via le menu contextuel.

![image](/wiki/img/inscriptions/reject.png)

### Suppression d’une inscription

L’action est accessible via le menu contextuel.

![image](/wiki/img/inscriptions/cancel.png)

---

### Cycle de vie d’une inscription

* **Pour un nouveau membre** :
  *inscription de test en cours de création → inscription de test enregistrée → inscription de test reçue par le club → inscription annuelle en cours de création → inscription annuelle enregistrée → inscription annuelle reçue par le club → inscription annuelle enregistrée auprès de la fédération → expirée*

* **Pour une réinscription** :
  *inscription annuelle en cours de création → inscription annuelle enregistrée → inscription annuelle reçue par le club → inscription annuelle enregistrée auprès de la fédération → expirée*

![image](/wiki/img/inscriptions/graph.svg)

🛈 **NOTE :** Pour s’inscrire au club après une période d’essai, il faut :

* que l’inscription aux 3 séances d’essai soit **reçue par le club** ;
* pour un membre de l’école VTT : avoir **participé** à au moins une rando ;
* pour un membre adulte : **être inscrit** à au moins une rando.

---

### Paramètres

Certains paramètres permettent de gérer les inscriptions :

* **Autoriser les inscriptions aux 3 séances d’essai pour l’école VTT** : permet d’autoriser ou non les nouvelles inscriptions à l’école VTT.
* **Autoriser les réinscriptions pour la nouvelle saison** : permet de bloquer la réinscription des adhérents de l’année précédente en attendant la nouvelle licence de la fédération.
  Au changement de saison, ce paramètre se règle automatiquement pour **interdire** la réinscription.

---

## Synthèse par saison

Permet de lister les adhérents par année, catégorisés en 3 groupes :

* les nouveaux inscrits pour l’année sélectionnée ;
* les adhérents ayant renouvelé leur inscription ;
* les adhérents n’ayant pas renouvelé leur inscription.

![image](/wiki/img/inscriptions/synthese.png)

---