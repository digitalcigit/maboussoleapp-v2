# Guide de Débogage - Page de Connexion

## Problèmes Courants

### 1. Texte Invisible en Mode Sombre
**Symptômes :**
- Texte illisible
- Contraste insuffisant
- Éléments de formulaire mal visibles

**Solutions :**
1. Vérifier les classes dark mode :
```css
.fi-input {
    @apply text-gray-900 dark:text-white;
}
```

2. Inspecter les surcharges CSS :
- Utiliser les outils de développement du navigateur
- Vérifier la spécificité des sélecteurs
- Contrôler les styles Filament par défaut

### 2. Problèmes de Défilement
**Symptômes :**
- Page dupliquée en bas
- Défilement inutile
- Hauteur incorrecte

**Solutions :**
1. Vérifier les classes de hauteur :
```css
.fi-simple-page {
    @apply min-h-screen fixed inset-0 overflow-hidden;
}

body:has(.fi-simple-page) {
    @apply overflow-hidden;
}
```

2. Inspecter la structure HTML :
- Vérifier les conteneurs imbriqués
- Contrôler les marges et paddings
- S'assurer que le contenu est centré

### 3. Problèmes de Compilation
**Symptômes :**
- Styles non appliqués
- Erreurs dans la console
- Cache obsolète

**Solutions :**
1. Recompiler les assets :
```bash
npm run build
```

2. Vider le cache :
```bash
php artisan optimize:clear
```

3. Vérifier les fichiers générés dans `public/build/`

## Vérification
1. Tester en mode clair et sombre
2. Vérifier différentes résolutions
3. Valider sur plusieurs navigateurs

## Support
Pour tout autre problème :
1. Consulter les logs Laravel
2. Vérifier la documentation Filament
3. Inspecter le code source généré
