# Features Restantes - Scholys

## Backend Features

### 🔐 Authentification & Autorisation

- [x] Login/Logout avec sessions
- [x] Forgot Password avec tokens manuels
- [x] Reset Password
- [x] Change Password (utilisateur connecté)

### 👥 Gestion des Utilisateurs

- [ ] **UserController** - CRUD pour créer/modifier/supprimer les utilisateurs (professeurs, staff)
- [ ] User Profile Management - Modifier prénom, nom, email
- [ ] User Bulk Import - Import CSV/Excel d'utilisateurs
- [ ] Search & Filters pour utilisateurs

### 🎓 Gestion des Élèves

- [ ] **StudentController** - CRUD complet pour gérer les élèves
- [ ] Student Bulk Import - Import CSV/Excel d'élèves
- [ ] Search & Filters pour élèves

### 🏫 Gestion de l'École

- [x] School Registration avec invitations
- [ ] School Settings - Compléter la gestion des paramètres (nom, adresse, etc.)

### 🛡️ Gestion des Rôles

- [x] Models Role & User relationships
- [ ] Role Management Controller - Attribuer/retirer des rôles
- [ ] Permission system (optionnel)

### 📊 Dashboard & API

- [ ] Dashboard API - Endpoints pour statistiques
- [ ] School Stats - Nombre d'élèves, professeurs, etc.

### 🔧 Operations en Masse

- [ ] Bulk Actions - Supprimer/modifier plusieurs utilisateurs/élèves
- [ ] Export Data - Export CSV/Excel

## Frontend Features

### 🔐 Authentification

- [x] Login page
- [ ] Forgot Password page
- [ ] Reset Password page

### 📊 Dashboard

- [x] Dashboard layout basique
- [ ] Dashboard content - Stats, raccourcis, widgets
- [ ] Navigation sidebar

### 👥 Gestion Utilisateurs

- [ ] Liste des utilisateurs
- [ ] Création/édition utilisateur
- [ ] Gestion des rôles
- [ ] Import en masse

### 🎓 Gestion Élèves

- [ ] Liste des élèves
- [ ] Création/édition élève
- [ ] Import en masse
- [ ] Fiches élèves détaillées

### 🏫 Paramètres École

- [ ] Page paramètres école
- [ ] Profil utilisateur
- [ ] Changement mot de passe

### 🔍 Recherche & Filtres

- [ ] Barre de recherche globale
- [ ] Filtres avancés
- [ ] Tri et pagination

## Priorités Recommandées

### Phase 1 - Gestion de Base

1. **UserController** (Backend)
2. Liste/création utilisateurs (Frontend)
3. **StudentController** (Backend)
4. Liste/création élèves (Frontend)

### Phase 2 - Dashboard & UX

5. Dashboard API & content
6. Forgot/Reset Password frontend
7. Search & filters

### Phase 3 - Fonctionnalités Avancées

8. Bulk operations
9. Role management UI
10. Advanced reporting

## Notes Techniques

- Session-based authentication (pas de tokens)
- Tests Pest pour tous les contrôleurs
- Vue.js 3 + Composition API
- Tailwind CSS pour le design
- Messages en français
- Validation avec FormRequests
- Controllers invokables quand approprié
