@component('mail::message')
# Bienvenue sur votre espace candidat

Cher(e) {{ $user->name }},

Un compte a été créé pour vous sur le portail candidat de Ma Boussole.

Voici vos identifiants de connexion :
- Email : {{ $user->email }}
- Mot de passe : {{ $password }}

@component('mail::button', ['url' => config('app.url') . '/portail'])
Accéder au portail
@endcomponent

Pour votre sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.

Cordialement,<br>
L'équipe {{ config('app.name') }}
@endcomponent
