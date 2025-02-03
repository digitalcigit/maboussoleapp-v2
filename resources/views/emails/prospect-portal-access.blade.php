@component('mail::message')
# Bienvenue sur votre espace candidat

Cher(e) {{ $user->name }},

Un compte a été créé pour vous sur le portail candidat de Ma Boussole.

Voici vos identifiants de connexion :
- Email : {{ $user->email }}
- Mot de passe : {{ $password }}

@component('mail::button', ['url' => $verificationUrl])
Vérifier mon email
@endcomponent

@component('mail::button', ['url' => config('app.url') . '/portail/login'])
Accéder au portail
@endcomponent

Pour votre sécurité, nous vous recommandons de :
1. Vérifier votre email en cliquant sur le bouton ci-dessus
2. Changer votre mot de passe lors de votre première connexion

Cordialement,<br>
L'équipe {{ config('app.name') }}
@endcomponent
