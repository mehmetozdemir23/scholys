# Scholys - État des Features

## 📈 État MVP
**Backend API : ✅ 90% Complet** (manque stats + exports)  
**Interface Web : ❌ 0% (Critique pour MVP)**

> Le backend a toutes les fonctionnalités métier essentielles. Il manque quelques endpoints de statistiques et d'export. L'interface web reste la priorité absolue pour le MVP.

## Backend Features

### 🔐 Authentification & Autorisation
- [x] JWT Authentication avec Laravel Sanctum
- [x] Password reset par email avec tokens
- [x] Système de rôles (super_admin, teacher, student)
- [x] Policies granulaires pour autorisation

### 👥 Gestion des Utilisateurs  
- [x] CRUD utilisateurs (professeurs, staff)
- [x] Profile management (nom, prénom, email, password)
- [x] Import CSV bulk avec validation et notifications
- [x] Recherche et filtres utilisateurs

### 🏫 Gestion de l'École
- [x] School registration avec invitations par email
- [x] Account setup avec mots de passe temporaires
- [x] School settings (nom, contact email)

### 📚 Gestion Académique
- [x] ClassGroups - CRUD et gestion par année académique
- [x] Subjects - Gestion par école
- [x] Assignations élèves/professeurs aux classes
- [x] Assignations professeurs aux matières
- [x] **Grades - Système complet** :
  - [x] Création avec validation (value ≤ max_value)
  - [x] Modification avec autorisation
  - [x] Désactivation avec audit trail
  - [x] Support coefficients, commentaires, titres

### 📊 Dashboard & API
- [ ] **Dashboard API** - `/api/dashboard`
  - [ ] Stats générales école (total users, classes, grades)
  - [ ] Répartition par rôle (nb teachers/students)

### 🔧 Operations en Masse  
- [x] Import utilisateurs CSV avec email notifications
- [ ] Bulk delete/update utilisateurs
- [ ] Export CSV/Excel (utilisateurs, notes, classes)

## 🎯 MVP - Minimum Viable Product

### 📱 Interface Web (Critique pour MVP)
- [ ] **Pages d'authentification**
  - [ ] Login page avec validation
  - [ ] Forgot password avec envoi email
  - [ ] Reset password avec token validation
- [ ] **Dashboard principal**
  - [ ] Vue d'ensemble école (stats de base)
  - [ ] Navigation sidebar entre sections
  - [ ] Header avec profil utilisateur
- [ ] **Gestion des classes**
  - [ ] Liste des classes avec filtres
  - [ ] Création/édition classe avec validation
  - [ ] Assignation élèves/professeurs
- [ ] **Gestion des notes**
  - [ ] Interface saisie notes par classe/matière
  - [ ] Liste notes avec filtres (actives/désactivées)
  - [ ] Modification et désactivation notes
  - [ ] Validation côté client (value ≤ max_value)

### 📊 API Extensions (Nice to Have)
- [ ] **Consultation notes**
  - [ ] API notes par élève
  - [ ] API notes par classe/matière
  - [ ] Calcul moyennes (simple, coefficients)
- [ ] **APIs de lecture**
  - [ ] Liste utilisateurs paginée
  - [ ] Détails classe avec élèves/professeurs

### 🔧 Fonctionnalités Futures
- [ ] Export bulletins PDF
- [ ] Notifications email automatiques
- [ ] Réactivation des notes
- [ ] Gestion avancée des périodes/trimestres
- [ ] Reporting avancé et analytics

## Notes Techniques

### Backend
- **JWT authentication** avec Laravel Sanctum
- **Tests Pest complets** - Feature + Unit tests
- **Action Pattern** pour la logique métier
- **Policies** pour l'autorisation fine
- **FormRequests** pour validation
- **Audit Logging** structuré
- **Database** optimisée avec :
  - Clés primaires composites pour tables pivot
  - Index optimisés (suppression des index inutiles)
  - Contraintes d'intégrité référentielle
  - Champs datetime pour audit trail

### Code Quality
- **Laravel Pint** avec règles strictes
- **PHPStan** analyse statique
- **Parenthèses obligatoires** pour instantiations
- **Migrations** typées avec declare(strict_types=1)

### Frontend (Basique)
- Vue.js 3 + Composition API
- Tailwind CSS pour le design  
- Messages en français
