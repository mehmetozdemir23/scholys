@component('mail::message')
# Bienvenue sur Scholys

Vous avez demandé à inscrire un établissement sur Scholys avec l'adresse : **{{ $email }}**.

Cliquez sur le bouton ci-dessous pour finaliser l'inscription de votre établissement.

@component('mail::button', ['url' => $url])
Finaliser l'inscription
@endcomponent

Ce lien expirera dans 24 heures.

Merci,<br>
L’équipe Scholys
@endcomponent
