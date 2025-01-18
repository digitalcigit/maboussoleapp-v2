# Workflow de Gestion des Prospects et Clients

## Vue d'ensemble du processus

```mermaid
stateDiagram-v2
    %% PARTIE 1 : PROSPECTS
    [*] --> NouveauProspect
    
    state "PARTIE 1: PROSPECTS" {
        NouveauProspect --> EnAnalyse: Assignation responsable
        
        state EnAnalyse {
            [*] --> AnalyseEnCours
            AnalyseEnCours --> DecisionAnalyse: 0-5 jours ouvrés
            DecisionAnalyse --> [*]: Notification
        }
        
        EnAnalyse --> Apte
        EnAnalyse --> NonApte
        
        Apte --> RelanceSemaine1: Pas de réponse
        RelanceSemaine1 --> RelanceSemaine2: Pas de réponse
        RelanceSemaine2 --> Archivage: Pas de réponse
        
        NonApte --> Archivage
        
        Apte --> OuvertureDossier: Réponse positive
    }
    
    %% PARTIE 2 : CLIENTS
    state "PARTIE 2: CLIENTS" {
        OuvertureDossier --> ConstitutionDossier
        
        state ConstitutionDossier {
            [*] --> AttenteDocuments
            AttenteDocuments --> DocumentsComplets: 2 semaines
            DocumentsComplets --> [*]
        }
        
        ConstitutionDossier --> PaiementAdmission
        PaiementAdmission --> EnvoiPhysique
        EnvoiPhysique --> ResultatAdmission
        
        ResultatAdmission --> PaiementEcole: Admis
        ResultatAdmission --> RetourEtape3: Non Admis (Cas 1)
        ResultatAdmission --> ArchivageClient: Non Admis (Cas 2)
        
        PaiementEcole --> PaiementAgence
        PaiementAgence --> OuvertureVisa
    }
    
    Archivage --> [*]
    ArchivageClient --> [*]
    OuvertureVisa --> [*]
```

## Points à Clarifier avec le MOE

### PARTIE 1 : PROSPECTS

1. **Dépôt et Enregistrement**
   - Qui peut créer un prospect ?
   - Les champs obligatoires vs optionnels
   - Validation des données (email, téléphone)

2. **Analyse de Dossier**
   - Critères d'analyse par destination
   - Processus de validation interne
   - Gestion des délais et notifications

3. **Système de Relance**
   - Automatisation des relances
   - Contenu des notifications
   - Critères de passage à l'archivage

### PARTIE 2 : CLIENTS

1. **Constitution Dossier**
   - Liste des documents par type de dossier
   - Système de validation des documents
   - Gestion des délais et relances

2. **Paiements**
   - Méthodes de paiement acceptées
   - Workflow de validation des paiements
   - Gestion des échéances

3. **Résultat Admission**
   - Critères de décision pour Cas 1 vs Cas 2
   - Processus de nouvelle soumission
   - Gestion des documents pour nouvelle tentative

## Suggestions d'Améliorations

1. **Statuts et Transitions**
   - Statuts plus granulaires pour meilleur suivi
   - Transitions automatiques basées sur événements
   - Historique complet des changements

2. **Notifications**
   - Système de notification multi-canal
   - Templates personnalisés par type d'événement
   - Rappels programmés

3. **Archivage**
   - Politique de rétention des données
   - Export des données archivées
   - Statistiques sur les motifs d'archivage

4. **Tableaux de Bord**
   - KPIs par étape du processus
   - Temps moyen par étape
   - Taux de conversion prospect -> client
