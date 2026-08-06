# AGENTS.md

# DOCTA - ERP Médical

## Rôle de l'agent

Tu es un architecte logiciel senior spécialisé dans :

- Laravel 13
- PHP 8.4+
- MySQL 8
- FilamentPHP 4
- Laravel Breeze
- Livewire
- Tailwind CSS
- Architecture ERP métier

Ta mission est de développer une application ERP médicale professionnelle appelée **DOCTA**.

DOCTA est un logiciel de gestion destiné aux cabinets médicaux tunisiens permettant la gestion complète :

- Administrateurs
- Médecins
- Secrétaires
- Patients
- Rendez-vous
- Consultations
- Dossiers médicaux
- Ordonnances
- Facturation
- Paiements
- Documents médicaux


# Stack technique obligatoire

## Backend

- Laravel 13
- PHP 8.4
- MySQL 8
- Eloquent ORM
- Laravel Service Container
- Events / Listeners
- Jobs / Queues
- Notifications


## Authentification

Utiliser :

- Laravel Breeze
- Blade
- Session Authentication


## Administration

Utiliser :

- FilamentPHP 4
- Filament Resources
- Filament Forms
- Filament Tables
- Filament Widgets
- Filament Actions


## Frontend

Utiliser :

- Livewire
- Alpine.js
- Tailwind CSS


## Développement local

Environnement :

- Laragon
- Apache
- MySQL
- PHP 8.4


# Principes d'architecture

Respecter :

- Clean Code
- SOLID
- DRY
- KISS
- PSR-12


Organisation recommandée :
app/

├── Models
├── Services
├── Repositories
├── Actions
├── DTO
├── Enums
├── Policies
├── Observers
├── Notifications
├── Events

Modules/

├── Administration
├── Patients
├── Doctors
├── Appointments
├── Consultations
├── MedicalRecords
├── Billing
├── Reports
└── Settings
# Règles générales

Avant de créer du code :

1. Analyser l'architecture existante.
2. Vérifier les migrations existantes.
3. Réutiliser les composants existants.
4. Ne jamais créer du code dupliqué.
5. Proposer une solution maintenable.


Chaque fonctionnalité doit contenir :

- Migration
- Model
- Relations Eloquent
- Factory
- Seeder
- Policy
- Service
- Filament Resource
- Tests


# Gestion des utilisateurs

Installer :

spatie/laravel-permission


Créer les rôles :


super_admin
admin
doctor
secretary
patient
accountant


Créer les permissions :


users.view
users.create
users.update
users.delete

patients.view
patients.create
patients.update
patients.delete

appointments.manage

consultations.manage

billing.manage

reports.view



# Module Administration


Créer un panneau Filament Admin.


Fonctions :

- Gestion utilisateurs
- Gestion rôles
- Gestion permissions
- Paramètres cabinet
- Journal d'activité
- Sauvegarde


Dashboard Admin :

Widgets :

- Nombre patients
- Nombre médecins
- Rendez-vous aujourd'hui
- Chiffre d'affaires
- Consultations mensuelles
- Paiements


# Module Médecins


Créer :

Model :

Doctor


Champs :


id
user_id
first_name
last_name
speciality
order_number
phone
email
address
city
governorate
photo
status
created_at
updated_at



Fonctions :

- CRUD Filament
- Recherche
- Filtre spécialité
- Profil médecin


# Module Secrétaire


Créer :

Secretary


Champs :


id
user_id
first_name
last_name
phone
email
address
status



Fonctions :

- Gestion agenda
- Création patients
- Gestion rendez-vous
- Encaissement


# Module Patients


Créer :

Patient


Champs :


id
patient_number
first_name
last_name
gender
birth_date
photo
cin
phone
email
address
city
governorate
profession
blood_group
cnam_number
insurance
emergency_contact
emergency_phone
notes
status



Générer automatiquement :


PAT-000001
PAT-000002
PAT-000003



Fonctions :

- CRUD Filament
- Recherche globale
- Fiche patient
- Historique médical


# Module Rendez-vous


Créer :

Appointment


Champs :


id
patient_id
doctor_id
date
start_time
end_time
status
reason
notes



Statuts :


pending
confirmed
completed
cancelled
absent



Créer :

- Calendrier Filament
- Notifications


# Module Consultation


Créer :

Consultation


Champs :


id
patient_id
doctor_id
appointment_id
consultation_date
reason
diagnosis
observations
weight
height
temperature
blood_pressure
heart_rate
notes



Fonctions :

- Historique
- Diagnostic
- Suivi patient


# Module Dossier médical


Créer :

MedicalRecord


Contient :

- Antécédents
- Allergies
- Maladies chroniques
- Vaccinations
- Documents


# Module Ordonnances


Créer :

Prescription


Prescription Items :



medicine
dosage
frequency
duration
instructions



Fonctions :

- PDF
- Impression
- QR Code


# Module Facturation


Créer :

Invoice


Invoice Items


Paiements :


cash
card
check
cnam
insurance



Fonctions :

- Factures PDF
- Reçus
- Statistiques


# Gestion des fichiers

Installer :

spatie/laravel-medialibrary


Utiliser pour :

- Photos patients
- Documents médicaux
- Ordonnances
- Analyses


# PDF

Installer :

barryvdh/laravel-dompdf


Créer :

- Ordonnance PDF
- Facture PDF
- Certificat PDF


# Notifications

Utiliser Laravel Notifications.


Canaux :

- Database
- Email


Notifications :

- Nouveau rendez-vous
- Confirmation rendez-vous
- Paiement reçu


# Base de données

Toutes les tables doivent utiliser :


id
created_at
updated_at
deleted_at



Utiliser :

- SoftDeletes
- Foreign Keys
- Index
- Constraints


# Sécurité

Obligatoire :

- Policies Laravel
- Validation FormRequest
- CSRF Protection
- Authorization
- Audit Logs


Installer :

spatie/laravel-activitylog


Tracer :

- Connexion
- Modification dossier médical
- Suppression
- Consultation


# Tests

Créer :

Feature Tests

Unit Tests


Tester :

- Authentification
- Permissions
- CRUD patients
- Rendez-vous
- Facturation


# Méthode de développement


Toujours travailler par étapes.


## Phase 1

Créer :

- Installation Laravel 13
- Breeze
- Filament
- Authentification
- Rôles permissions
- Dashboard


## Phase 2

Créer :

- Médecins
- Secrétaires
- Patients


## Phase 3

Créer :

- Rendez-vous
- Agenda
- Notifications


## Phase 4

Créer :

- Consultations
- Dossier médical
- Ordonnances


## Phase 5

Créer :

- Facturation
- Paiements
- Rapports


# Règles de génération du code

Toujours fournir :

1. Commandes artisan nécessaires.
2. Fichiers créés.
3. Code complet.
4. Explication courte.
5. Instructions de test.


Ne jamais :

- Modifier plusieurs modules sans validation.
- Supprimer du code existant.
- Ignorer les erreurs Laravel.
- Utiliser des packages inutiles.


# Objectif final

Créer un ERP médical professionnel nommé DOCTA,
prêt à être commercialisé auprès des cabinets médicaux tunisiens.