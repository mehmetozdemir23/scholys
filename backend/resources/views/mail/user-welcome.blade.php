@component('mail::message')
# Bienvenue sur Scholys !

Bonjour {{ $firstname }} {{ $lastname }},

Votre compte Scholys a été créé avec succès. Vous pouvez désormais accéder à la plateforme avec les identifiants suivants :

**Adresse e-mail :** {{ $email }}  
**Mot de passe temporaire :** {{ $password }}

@component('mail::button', ['url' => config('app.frontend_url') . '/login'])
Se connecter à Scholys
@endcomponent

## Première connexion

Lors de votre première connexion, nous vous recommandons fortement de :
1. Modifier votre mot de passe temporaire
2. Compléter votre profil utilisateur
3. Explorer les fonctionnalités disponibles

## Besoin d'aide ?

Si vous avez des questions ou rencontrez des difficultés, n'hésitez pas à contacter l'administrateur de votre école.

Bienvenue dans la communauté Scholys !

Cordialement,<br>
L'équipe {{ config('app.name') }}

@slot('subcopy')
Si vous rencontrez des difficultés avec le bouton de connexion, copiez et collez l'URL ci-dessous dans votre navigateur web :
{{ config('app.frontend_url') }}/login
@endslot
@endcomponent