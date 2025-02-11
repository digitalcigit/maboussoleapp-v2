# Documentation Technique - Page de Connexion

## Structure

### Composants Principaux
```
resources/
└── css/
    └── filament/
        └── admin/
            └── theme.css       # Styles personnalisés
```

## Styles CSS

### Classes Principales
1. **Conteneur Principal**
```css
.fi-simple-page {
    @apply min-h-screen fixed inset-0 overflow-hidden;
}
```

2. **Conteneur du Formulaire**
```css
.fi-simple-main-ctn {
    @apply h-screen flex items-center justify-center;
}

.fi-simple-card {
    @apply w-full max-w-md mx-auto;
}
```

3. **Champs de Formulaire**
```css
.fi-input-wrp {
    @apply bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700;
}

.fi-input {
    @apply text-gray-900 dark:text-white;
}
```

## Mode Sombre
- Utilisation des préfixes `dark:`
- Couleurs adaptées pour le contraste
- Transitions fluides

## Compilation
```bash
# Commande de build
npm run build

# Fichiers générés
public/build/assets/theme-*.css
```

## Maintenance
1. Modifications de style :
   - Éditer `theme.css`
   - Recompiler avec `npm run build`
   - Vider le cache si nécessaire

2. Points d'attention :
   - Respect des classes Filament
   - Test des deux modes (clair/sombre)
   - Vérification responsive
