Etape 1 : Analyse de dossier
Dans cette étape on crée le dossier, le formulaire de création de dossier est le même que celui de la création d'un nouveau prospect avec les 3 status que nous venons d'implémenter.

Si le status Analyse de dossier est terminé il y'a un bonton continuer qui est déclenché manuellement si le propriétaire du dossier donne son OK)

Etape 2 : Ouverture de dossier & Admission

Dans cette étape on n'a toutes les informations du formulaire de l'etape d'analyse de dossier avec de nouveaux status : 
	=> Réception physique des documents 
	=> Paiement Frais demande d'admission
	=> Soumission des dossiers
	=> Reponse de soumission Positif
	=> Reponse de soumission Negatif


Si le status est Reponse de soumission Negatif alors on reste à l'etape 2, le prospect peut changer de destination et reprendre un autre processus
Si le status est Reponse de soumission Positif alors on passe à l'etape 3

Etape 3 : Paiement
Dans cette étape on n'a toutes les informations du formulaire de l'etape d'analyse de dossier + un nouveau champ pour uploader le dossier d'admission après le paiement total avec de nouveaux status :
 	=> Statut Paiement Frais d'agence 
       		¦ Paiement Scolarité Partielle 
       		¦ Paiement Frais de scolarité Totale
       	=> Abandon (Après 2 mois sans reponse le dossier est archiver )

Si le paiement est effectif alors on va à l'etape 4 

Etape 4 :  Accompagnement Visa
Dans cette étape on n'a toutes les informations du formulaire de l'etape d'analyse de dossier + un nouveau champ pour uploader le dossier d'admission avec de nouveaux status :
	=> Constitution du dossier visa 
	=> Paiement des frais de demande de visa
	=> Reponse de la soumission
	=> Visa Obtenu ou Pas Obtenue
	=> Si Visa obtenue paiement des frais

Prévoir les champs pour ajouter Dossier du garant financier, Attestation de travail...)

Dis moi ce que tu as compris des modifications demandées. 

D'accord, j'ai bien compris qu'il s'agit d'un workflow de traitement de dossiers d'admission avec 4 grandes étapes. Voici ma compréhension :

1. Étape Analyse de Dossier

Utilise le formulaire actuel de création de prospect
Garde les 3 statuts existants :
En attente de documents
Analyse en cours
Analyse terminée
Ajout d'un bouton "Continuer" qui apparaît uniquement quand l'analyse est terminée
Ce bouton permet de passer à l'étape 2 après validation manuelle
2. Étape Ouverture & Admission

Conserve toutes les informations de l'étape 1
Nouveaux statuts séquentiels :
Réception physique des documents
Paiement Frais demande d'admission
Soumission des dossiers
Réponse de soumission (Positive/Négative)
Si réponse négative : possibilité de changer de destination et recommencer
Si réponse positive : passage à l'étape 3
3. Étape Paiement

Conserve les informations précédentes
Nouveau champ pour le dossier d'admission
Statuts de paiement :
Paiement Frais d'agence
Paiement Scolarité Partielle
Paiement Frais de scolarité Totale
Statut spécial "Abandon" après 2 mois d'inactivité
Passage à l'étape 4 si paiement complet

4. Étape Accompagnement Visa

Conserve les informations précédentes
Nouveaux champs pour documents spécifiques (garant financier, attestation travail...)
Statuts :
Constitution du dossier visa
Paiement frais de demande visa
Réponse soumission
Visa (Obtenu/Non obtenu)
Paiement des frais si visa obtenu
Points techniques importants :

Chaque étape est un "état" du dossier
Les informations s'accumulent d'étape en étape
Certaines étapes peuvent boucler (retour possible)
Besoin de gérer les documents spécifiques à chaque étape
Système de notification pour les changements d'état