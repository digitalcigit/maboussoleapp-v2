# Gestion des Rôles Utilisateurs

## Vue d'ensemble
Ce module gère les rôles et permissions des utilisateurs dans l'application MaBoussole. Il définit la structure des rôles disponibles et leurs permissions associées.

## Points clés
- Le rôle "prospect" existe dans le système mais n'est pas disponible lors de la création d'utilisateurs
- Le rôle "apporteur-affaire" permet aux utilisateurs de gérer leurs propres prospects
- Les permissions sont gérées via le package Spatie/laravel-permission
- L'interface d'administration utilise Filament pour la gestion des rôles

## Rôles disponibles pour les utilisateurs
- super-admin
- manager
- apporteur-affaire
- autres rôles selon les besoins métier

## Sécurité
- Validation stricte des permissions à chaque niveau
- Séparation claire des responsabilités par rôle
- Protection contre l'attribution non autorisée de rôles sensibles
