# Personnalisation de la Page de Connexion Admin

## Vue d'ensemble

Ce module décrit la personnalisation de la page de connexion de l'interface d'administration de Ma Boussole. Il utilise une approche moderne avec un design split-screen combinant un formulaire de connexion élégant et un slider d'images dynamique.

## Points clés

### 1. Design Split-Screen
- Interface divisée en deux parties distinctes
- Partie gauche : formulaire de connexion épuré
- Partie droite : slider d'images avec messages inspirants

### 2. Composants Principaux
- Classe de connexion personnalisée étendant `Filament\Pages\Auth\Login`
- Vue Blade personnalisée avec Alpine.js pour les animations
- Thème CSS personnalisé pour une cohérence visuelle

### 3. Caractéristiques
- Design responsive (adaptation mobile)
- Animations fluides pour les transitions
- Intégration avec l'identité visuelle de Ma Boussole
- Messages inspirants pour les étudiants

### 4. Technologies Utilisées
- Laravel Filament pour le framework d'administration
- Tailwind CSS pour les styles
- Alpine.js pour les interactions côté client
- Système de thèmes natif de Filament

## Organisation du Module

- `concept.md` : Principes de design et architecture
- `implementation.md` : Guide technique détaillé
- `usage.md` : Guide d'utilisation et personnalisation
- `troubleshooting.md` : Solutions aux problèmes courants

## Prérequis

- Filament 3.x
- PHP 8.1+
- Node.js et NPM pour la compilation des assets
- Connaissance de base de Tailwind CSS et Alpine.js

## Ressources Associées

- Documentation Filament : [Authentication](https://filamentphp.com/docs/panels/authentication)
- [Guide des thèmes Filament](https://filamentphp.com/docs/panels/themes)
- [Documentation Tailwind CSS](https://tailwindcss.com/docs)
- [Documentation Alpine.js](https://alpinejs.dev/)
