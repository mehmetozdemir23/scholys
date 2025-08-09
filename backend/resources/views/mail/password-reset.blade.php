@component('mail::message')
# Réinitialisation de votre mot de passe

Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Scholys.

@component('mail::button', ['url' => config('app.frontend_url') . '/reset-password?token=' . $token])
Réinitialiser mon mot de passe
@endcomponent

Ce lien de réinitialisation expirera dans {{ config('auth.passwords.users.expire', 60) }} minutes.

Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action supplémentaire n'est requise.

Cordialement,<br>
L'équipe {{ config('app.name') }}

@slot('subcopy')
Si vous rencontrez des difficultés avec le bouton "Réinitialiser mon mot de passe", copiez et collez l'URL ci-dessous dans votre navigateur web :
{{ config('app.frontend_url') }}/reset-password?token={{ $token }}
@endslot
@endcomponent