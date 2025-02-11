@component('mail::message')
# Rappel : Documents en attente

Cher(e) {{ $dossier->client->name }},

Nous espérons que vous allez bien. Nous vous contactons car votre dossier est en attente de documents depuis plus de 7 jours.

**Détails du dossier :**
- Référence : {{ $dossier->reference }}
- Statut : En attente de documents
- En attente depuis : {{ $dossier->status_updated_at->format('d/m/Y') }}

Pour éviter tout retard dans le traitement de votre dossier, nous vous invitons à :
1. Soumettre les documents manquants dès que possible
2. Contacter votre manager si vous rencontrez des difficultés

@component('mail::button', ['url' => config('app.url') . '/client/dossiers/' . $dossier->id])
Voir mon dossier
@endcomponent

Si vous avez des questions ou besoin d'assistance, n'hésitez pas à contacter votre manager.

Cordialement,<br>
L'équipe {{ config('app.name') }}
@endcomponent
