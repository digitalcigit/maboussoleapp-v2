# Session du 25 Janvier 2025 - Corrections Portail Candidat

## Corrections Apportées

### 1. Correction des Permissions
- Résolution de l'erreur 403 dans le portail candidat
- Modification de la vérification des permissions pour utiliser la relation inverse (via prospect_id du dossier)
- Ajout d'une colonne dossier_id dans la table prospects pour une meilleure cohérence des relations

### 2. Harmonisation des Formulaires
- Modification du champ "Niveau d'études" dans le portail candidat pour utiliser un Select au lieu d'un TextInput
- Alignement des options avec celles de l'interface admin pour garantir la cohérence des données :
  - Baccalauréat
  - Bac+2 (DUT, BTS)
  - Bac+3 (Licence)
  - Bac+4 (Master 1)
  - Bac+5 (Master 2)
  - Bac+8 (Doctorat)
