## Principe
***
Lister et administrer les inscription au club

## Gestion des inscriptions

Afficher la liste des inscriptions en fonction du filtre.

### Filtres
- 3 séances en cours: membre qui s'est inscrit pour la première fois au club, et qui a participé à moins de 3 séances.
- 3 séances terminées: membre qui s'est inscrit pour la première fois au club, et qui a été présent 3 séances ou plus.
- Nouvelles inscriptions à valider: membre qui a fini la période d'essai et qui s'est incrit pour la saison.
- Renouvellement à valiser : membre incrit à la saison précédente et qui s'est incrit pour la nouvelle saison.
- Licence non renouvelées: membre incrit à la saison précédente et qui n'a pas renouvellé sa licence.
- En cour de création: membre qui a commencé de remplir le formulaire d'incription mais qui n'a pas validé le récaptulatif
- Inscrire à la ffvélo: membre dont l'inscription annuelle a été réceptionnée et qu'il faut inscrire à la ffvélo. 

![image](/wiki/img/inscriptions/licences_to_receive.png)

🛈 **NOTE :** la liste filtrée est exportable via le menu Exporter la sélection, et les adresses mail peuvent être copier dans le presse papier via le menu *Copier les emails de la séléction*


🛈 **NOTE :** l'affichage des liste pour la periode d'essai, indique le nombre de participations / le nombre d'inscription à une sortie.

### Réception d'un dossier d'inscription
Pour les membres qui se sont incrits aux 3 séances d'essai ou à la licence annuelle, un bouton permet d'indiqué que le dossier d'incription a été reçu par le club. Une pop'up permet de confirmer l'action.

![image](/wiki/img/inscriptions/recepted.png)

### Inscription à la ffvélo
Pour les membres don le dossier d'incription annuelle, un bouton permet d'indiqué que l'incription a été saisie sur le site de la ffvélo. Une pop'up permet de confirmer l'action.

![image](/wiki/img/inscriptions/registered.png)

### Inscription incomplète
Dans la cas d'un dossier incomplet, il est possible d'envoyer un message à l'adhérent en lui indiquant les informations manquantes ou erronées. A la validation du formulaire, l'état de l'incription repasse en "inscription en cours de création". Action est accèssible par le menu contextuel.

![image](/wiki/img/inscriptions/reject.png)


### Suppression d'une inscription
Action est accèssible par le menu contextuel.

![image](/wiki/img/inscriptions/cancel.png)

### Cycle de vie d'une inscription
- Pour un nouveau membre : inscription de test en cours de création => inscription de test enregistrée => inscription de test reçue par le club => Inscription annuelle en cours de création => inscription annuelle enregistrée => inscription annuelle reçue par le club => inscription annuelle enregistrée auprès de la fédération => expirée

- Pour une réinscription : Inscription annuelle en cours de création => inscription annuelle enregistrée => inscription annuelle reçue par le club => inscription annuelle enregistrée auprès de la fédération => expirée

![image](/wiki/img/inscriptions/graph.svg)

🛈 **NOTE :** Pour s'inscrire au club après une période d'essai, il faut :
- que l'inscription des 3 séances d'essai soit reçue par le club
- pour un membre de l'école VTT: avoir ***participé*** à au moins une rando
- pour un membre adulte: ***être inscrit*** à au moins une rando

### Paramètres
Certain paramètres perment de gérer les inscription
- Autoriser les inscriptions au 3 séances d'essai pour l'école vtt: permet d'autoriser ou non les nouvelles inscriptions à l'école vtt
- Autoriser les réinscriptions pour la nouvelle saison : permet de bloquer la réinscription des adhérent de l'année précédente en attandant la nouvelle licence de la fédération. Au changement de saison, ce paramètre se rèqle automatiquement pour interdire la réinscription.

## Synthèse par saison
Permet de lister les adhérents par année, catégorisé en 3 parties
- les nouveaux inscrits pour l'année sélectionnée
- les adhérents qui ont renouvellé leur inscription
- les adhérents qui n'ont pas renouvellé leur inscription

![image](/wiki/img/inscriptions/synthese.png)



