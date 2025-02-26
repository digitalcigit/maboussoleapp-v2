# Concept : Activités dans les Dossiers

## Principes fondamentaux

1. **Relation polymorphique**
   - Les activités sont liées aux dossiers via une relation morphMany
   - Permet la réutilisation du système d'activités pour d'autres entités
   - Facilite l'extension future du système

2. **Types d'activités**
   - Chaque activité a un type spécifique
   - Les types sont standardisés via des constantes
   - Chaque type a sa propre représentation visuelle (couleurs, icônes)

3. **Traçabilité**
   - Toutes les activités sont horodatées
   - L'auteur de chaque activité est enregistré
   - Distinction entre date de création, date planifiée et date de réalisation

4. **Organisation**
   - Les activités sont triées par défaut par date de création (plus récent en premier)
   - Possibilité de filtrer par type
   - Interface intuitive pour la gestion des activités

## Avantages

1. **Efficacité opérationnelle**
   - Accès direct aux activités depuis le dossier
   - Réduction du temps de navigation
   - Vue consolidée des interactions

2. **Qualité des données**
   - Validation des données à la source
   - Cohérence des types d'activités
   - Historique complet des interactions

3. **Flexibilité**
   - Système extensible pour nouveaux types d'activités
   - Adaptable à différents besoins métier
   - Interface personnalisable
