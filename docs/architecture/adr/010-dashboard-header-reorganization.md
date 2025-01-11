# ADR 010: Réorganisation de l'en-tête du Dashboard

## Date
2025-01-08

## État
Accepté

## Contexte
Le widget "Bonjour Admin" avec le bouton de déconnexion était positionné sous les graphiques du dashboard, ce qui n'était pas optimal pour l'expérience utilisateur. Les informations de compte et l'option de déconnexion devraient être facilement accessibles en haut de l'interface.

## Décision
Nous avons décidé de :
1. Déplacer l'`AccountWidget` en première position dans les widgets d'en-tête du dashboard
2. Retirer la configuration du widget du `AdminPanelProvider` pour éviter la duplication
3. Maintenir toutes les fonctionnalités existantes tout en améliorant l'organisation visuelle

### Modifications techniques
- Ajout de l'`AccountWidget` au début du tableau dans `getHeaderWidgets()` du Dashboard
- Retrait de l'`AccountWidget` de la configuration du panel dans `AdminPanelProvider`

### Code impacté
```php
// Dans Dashboard.php
protected function getHeaderWidgets(): array
{
    $widgets = [];
    $widgets[] = AccountWidget::class; // Ajouté en première position
    // Autres widgets...
}

// Dans AdminPanelProvider.php
->widgets([
    // AccountWidget retiré d'ici
])
```

## Conséquences
### Positives
- Meilleure organisation visuelle du dashboard
- Accès plus rapide aux fonctions de compte utilisateur
- Code plus maintainable avec une seule source de vérité pour la position du widget
- Pas de duplication de widget

### Négatives
- Aucune identifiée

## Alternatives considérées
1. Création d'un nouveau widget personnalisé : Rejetée car inutilement complexe
2. Désactivation du widget par défaut : Rejetée car non nécessaire
3. Modification du template : Rejetée car moins flexible que l'approche choisie

## Notes
Cette modification suit le processus qualité en :
- Minimisant les changements nécessaires
- Évitant la sur-ingénierie
- Maintenant une documentation claire
- Assurant la traçabilité des modifications
