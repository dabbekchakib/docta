# DOCTA - ERP Médical

Plateforme de gestion de cabinet médicale destinée aux cabinets médicaux tunisiens.

## Fonctionnalités

- Gestion des administrateurs
- Gestion des médecins
- Gestion des secrétaires
- Gestion des patients
- Gestion des rendez-vous
- Gestion des consultations
- Dossiers médicaux
- Ordonnances
- Facturation et paiements
- Documents médicaux

## Stack technique

- Laravel 13
- PHP 8.4
- MySQL 8
- FilamentPHP 4
- Laravel Breeze
- Livewire
- Tailwind CSS

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

## Tests

```bash
php artisan test
```
