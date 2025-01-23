# ADR-006 : Synchronisation Prospect-Dossier

## Contexte
La modification des informations d'un prospect via le formulaire de dossier ne mettait pas à jour les données dans la table prospects, créant des incohérences dans l'application.

## Décision
Nous avons décidé d'implémenter un mécanisme de synchronisation automatique entre les dossiers et les prospects.

### Solution Technique
1. Utilisation du hook `afterSave` dans `EditDossier`
2. Propagation automatique des modifications
3. Notification de confirmation

## Implémentation

### Hook afterSave
```php
protected function afterSave(): void
{
    $prospectData = $this->data['prospect_info'] ?? null;
    
    if ($prospectData && $this->record->prospect_id) {
        $prospect = Prospect::find($this->record->prospect_id);
        
        if ($prospect) {
            $prospect->update([
                'first_name' => $prospectData['first_name'],
                'last_name' => $prospectData['last_name'],
                // ... autres champs
            ]);
        }
    }
}
```

## Avantages

1. **Cohérence des Données**
   - Synchronisation automatique
   - Réduction des erreurs humaines
   - Intégrité des données garantie

2. **Expérience Utilisateur**
   - Mise à jour transparente
   - Feedback immédiat via notifications
   - Interface cohérente

3. **Maintenance**
   - Code centralisé dans le hook
   - Facilité de débogage
   - Documentation claire

## Inconvénients

1. **Performance**
   - Double écriture en base de données
   - Légère augmentation du temps de traitement

2. **Complexité**
   - Logique supplémentaire à maintenir
   - Risque de conflits de données

## Impact sur le Système

1. **Base de Données**
   - Pas de modifications structurelles
   - Augmentation des opérations d'écriture

2. **Code**
   - Ajout du hook dans `EditDossier`
   - Documentation technique mise à jour
   - Guides de débogage créés

## Alternatives Considérées

1. **Mise à jour Manuelle**
   - Nécessite une action utilisateur
   - Risque d'oubli et d'incohérences
   - Rejetée pour la fiabilité

2. **Événements Laravel**
   - Plus complexe à implémenter
   - Overhead supplémentaire
   - Rejetée pour la simplicité

## Statut
Approuvé et implémenté le 19 janvier 2025
