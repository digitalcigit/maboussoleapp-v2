# Guide du Tableau de Bord Super Admin

## Introduction
Ce guide explique en détail le fonctionnement du tableau de bord Super Admin, ses composants et comment les utiliser efficacement.

## 1. Structure des Widgets

### 1.1 Métriques Financières (`FinancialMetricsWidget`)
```php
// Localisation : app/Filament/Widgets/FinancialMetricsWidget.php
```
- **Chiffre d'Affaires** : Calculé à partir de `total_amount` des clients
- **Commissions** : 20% du CA automatiquement calculé
- **Pipeline** : Basé sur les prospects qualifiés × montant moyen des contrats

### 1.2 Entonnoir de Conversion (`ProspectFunnelWidget`)
```php
// Localisation : app/Filament/Widgets/ProspectFunnelWidget.php
```
Visualise le parcours des prospects :
- Nouveaux → Contactés → Qualifiés → Prêts → Proposition → Négociation → Signés

### 1.3 Performance Financière (`FinancialPerformanceChart`)
```php
// Localisation : app/Filament/Widgets/FinancialPerformanceChart.php
```
- Graphique linéaire sur 6 mois
- Mise à jour automatique toutes les 30 secondes
- Données agrégées par mois

### 1.4 Objectifs Mensuels (`MonthlyGoalsWidget`)
```php
// Localisation : app/Filament/Widgets/MonthlyGoalsWidget.php
```
Suit trois KPIs principaux :
- Objectif CA : 100 000 €
- Objectif Prospects : 50
- Taux de Conversion : 20%

### 1.5 Dernières Transactions (`LatestTransactionsWidget`)
```php
// Localisation : app/Filament/Widgets/LatestTransactionsWidget.php
```
Affiche un tableau avec :
- Nom du client
- Montant total et payé
- Statut du paiement (avec codes couleur)
- Date de création

## 2. Utilisation des Données

### 2.1 Statuts de Paiement
Les statuts possibles sont :
```php
const PAYMENT_STATUS_PENDING = 'en_attente';
const PAYMENT_STATUS_PARTIAL = 'partiel';
const PAYMENT_STATUS_COMPLETED = 'complete';
```

### 2.2 Calculs Importants
```php
// Calcul des commissions
$commissions = $chiffreAffaires * 0.20;

// Calcul du pipeline prévisionnel
$pipeline = $prospectsQualifies * $montantMoyenContrats;

// Taux de conversion
$tauxConversion = ($clientsConvertis / $totalProspects) * 100;
```

## 3. Maintenance et Dépannage

### 3.1 Rafraîchissement des Données
- Les widgets se rafraîchissent automatiquement toutes les 30 secondes
- Pour forcer un rafraîchissement : utilisez le bouton en haut à droite

### 3.2 Problèmes Courants
1. **Données manquantes**
   - Vérifier les migrations : `php artisan migrate:status`
   - Vérifier les seeders : `php artisan db:seed`

2. **Erreurs d'affichage**
   - Vider le cache : `php artisan cache:clear`
   - Recompiler les vues : `php artisan view:clear`

## 4. Bonnes Pratiques

### 4.1 Performance
- Éviter les requêtes N+1 dans les widgets
- Utiliser les scopes Eloquent pour filtrer les données
- Mettre en cache les calculs lourds

### 4.2 Sécurité
- Accès limité au rôle 'super-admin'
- Validation des données entrantes
- Protection CSRF active

## 5. Personnalisation

### 5.1 Ajouter un Widget
1. Créer une nouvelle classe dans `app/Filament/Widgets`
2. Étendre la classe appropriée de Filament
3. Enregistrer le widget dans `SuperAdminDashboard`

### 5.2 Modifier les Objectifs
Les objectifs sont définis dans `MonthlyGoalsWidget` :
```php
protected function getStats(): array
{
    $revenueGoal = 100000;
    $prospectsGoal = 50;
    $conversionGoal = 20;
    // ...
}
```

## 6. Tests

### 6.1 Tests Unitaires
```bash
php artisan test --filter=SuperAdminDashboardTest
```

### 6.2 Tests Visuels
```bash
php artisan dusk
```

## Conclusion
Le tableau de bord Super Admin est un outil puissant pour suivre les KPIs et la performance globale. Il est conçu pour être facilement maintenable et extensible selon les besoins futurs.
