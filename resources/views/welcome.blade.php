<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="DOCTA - Logiciel de gestion pour cabinets médicaux en Tunisie. Rendez-vous, consultations, dossiers médicaux, ordonnances et facturation.">

        <title>{{ config('app.name', 'DOCTA') }} — ERP Médical pour Cabinets</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen flex flex-col">

            {{-- Header / Navigation --}}
            <header class="sticky top-0 z-40 w-full bg-white/90 backdrop-blur border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <a href="/" class="flex items-center gap-2.5">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-teal-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="font-extrabold text-xl tracking-tight text-gray-900">DOCTA</span>
                        </a>

                        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                            <a href="#fonctionnalites" class="hover:text-teal-600 transition">Fonctionnalités</a>
                            <a href="#services" class="hover:text-teal-600 transition">Services</a>
                            <a href="#pourquoi" class="hover:text-teal-600 transition">Pourquoi DOCTA</a>
                            <a href="#contact" class="hover:text-teal-600 transition">Contact</a>
                        </nav>

                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                   class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm text-white bg-teal-600 hover:bg-teal-700 transition">
                                    Mon espace
                                </a>
                            @else
                                @if (Route::has('login'))
                                    <a href="{{ url('patient/login') }}"
                                       class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm text-teal-600 border border-teal-600 hover:bg-teal-50 transition">
                                        Connexion
                                    </a>
                                @endif
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                       class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm text-white bg-teal-600 hover:bg-teal-700 transition">
                                        Créer un compte patient
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1">

                {{-- Hero Section --}}
                <section class="relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-50 via-white to-blue-50"></div>
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-teal-200/40 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-200/40 rounded-full blur-3xl"></div>

                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                        <div class="grid lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 text-xs font-semibold uppercase tracking-wide">
                                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                    ERP Médical Tunisien
                                </span>
                                <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
                                    La gestion de votre cabinet médical, simplifiée.
                                </h1>
                                <p class="mt-6 text-lg text-gray-600 leading-relaxed">
                                    DOCTA vous offre une solution complète : rendez-vous, consultations, dossiers
                                    médicaux, ordonnances électroniques et facturation — le tout dans une plateforme
                                    moderne et sécurisée.
                                </p>

                                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                                    @auth
                                        <a href="{{ url('/dashboard') }}"
                                           class="inline-flex justify-center items-center px-6 py-3.5 rounded-xl font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-lg shadow-teal-600/20 transition">
                                            Accéder à mon espace
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    @else
                                        @if (Route::has('login'))
                                            <a href="{{ url('patient/login') }}"
                                               class="inline-flex justify-center items-center px-6 py-3.5 rounded-xl font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-lg shadow-teal-600/20 transition">
                                                Se connecter
                                            </a>
                                        @endif
                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}"
                                               class="inline-flex justify-center items-center px-6 py-3.5 rounded-xl font-semibold text-teal-700 bg-white border border-teal-200 hover:bg-teal-50 transition">
                                                Créer un compte patient
                                            </a>
                                        @endif
                                    @endauth
                                </div>

                                <div class="mt-10 grid grid-cols-3 gap-6 max-w-md">
                                    <div>
                                        <p class="text-3xl font-extrabold text-teal-600">100%</p>
                                        <p class="text-sm text-gray-500">Digitalisation</p>
                                    </div>
                                    <div>
                                        <p class="text-3xl font-extrabold text-teal-600">24/7</p>
                                        <p class="text-sm text-gray-500">Disponibilité</p>
                                    </div>
                                    <div>
                                        <p class="text-3xl font-extrabold text-teal-600">SSL</p>
                                        <p class="text-sm text-gray-500">Sécurisé</p>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden lg:block">
                                <div class="relative rounded-2xl bg-white shadow-2xl shadow-teal-900/10 ring-1 ring-gray-200 overflow-hidden p-8">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">Bienvenue sur DOCTA</p>
                                            <p class="text-sm text-gray-500">Votre espace santé en ligne</p>
                                        </div>
                                        <span class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                            </svg>
                                        </span>
                                    </div>

                                    <div class="mt-6 space-y-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Prise de rendez-vous</p>
                                                <p class="text-xs text-gray-500">Réservez en quelques clics</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Dossier médical</p>
                                                <p class="text-xs text-gray-500">Antécédents et suivi patient</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Ordonnances</p>
                                                <p class="text-xs text-gray-500">Prescriptions électroniques PDF</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6 pt-6 border-t border-gray-100 flex items-center justify-between">
                                        <a href="{{ url('patient/login') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">Connectez-vous →</a>
                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" class="text-sm font-semibold text-gray-400 hover:text-gray-600">Nouveau ? Créer un compte</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Fonctionnalités --}}
                <section id="fonctionnalites" class="py-20 bg-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-2xl mx-auto">
                            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Fonctionnalités pensées pour la médecine</h2>
                            <p class="mt-4 text-lg text-gray-600">Tout ce dont un cabinet médical a besoin, réuni dans une seule plateforme.</p>
                        </div>

                        <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div class="p-6 rounded-2xl bg-gray-50 hover:bg-teal-50 transition ring-1 ring-gray-100">
                                <span class="inline-flex w-12 h-12 rounded-xl bg-teal-100 items-center justify-center text-teal-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-semibold text-gray-900">Gestion des rendez-vous</h3>
                                <p class="mt-2 text-sm text-gray-600">Agenda intelligent, réservation en ligne et rappels automatiques auprès du patient.</p>
                            </div>

                            <div class="p-6 rounded-2xl bg-gray-50 hover:bg-teal-50 transition ring-1 ring-gray-100">
                                <span class="inline-flex w-12 h-12 rounded-xl bg-blue-100 items-center justify-center text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-semibold text-gray-900">Dossiers médicaux</h3>
                                <p class="mt-2 text-sm text-gray-600">Antécédents, allergies, maladies chroniques et historique complet du patient.</p>
                            </div>

                            <div class="p-6 rounded-2xl bg-gray-50 hover:bg-teal-50 transition ring-1 ring-gray-100">
                                <span class="inline-flex w-12 h-12 rounded-xl bg-amber-100 items-center justify-center text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-semibold text-gray-900">Ordonnances électroniques</h3>
                                <p class="mt-2 text-sm text-gray-600">Prescriptions claires, impression PDF et vérification par QR Code.</p>
                            </div>

                            <div class="p-6 rounded-2xl bg-gray-50 hover:bg-teal-50 transition ring-1 ring-gray-100">
                                <span class="inline-flex w-12 h-12 rounded-xl bg-emerald-100 items-center justify-center text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-semibold text-gray-900">Facturation & paiements</h3>
                                <p class="mt-2 text-sm text-gray-600">Factures et reçus PDF, paiement par carte, chèque, CNAM ou assurance.</p>
                            </div>

                            <div class="p-6 rounded-2xl bg-gray-50 hover:bg-teal-50 transition ring-1 ring-gray-100">
                                <span class="inline-flex w-12 h-12 rounded-xl bg-violet-100 items-center justify-center text-violet-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-semibold text-gray-900">Statistiques & rapports</h3>
                                <p class="mt-2 text-sm text-gray-600">Tableaux de bord financiers et médicaux pour piloter votre activité.</p>
                            </div>

                            <div class="p-6 rounded-2xl bg-gray-50 hover:bg-teal-50 transition ring-1 ring-gray-100">
                                <span class="inline-flex w-12 h-12 rounded-xl bg-rose-100 items-center justify-center text-rose-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-semibold text-gray-900">Sécurité renforcée</h3>
                                <p class="mt-2 text-sm text-gray-600">Données chiffrées, contrôle d'accès par rôles et journal d'activité.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- CTA section --}}
                <section id="services" class="py-20 bg-gradient-to-br from-teal-600 to-teal-700">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <h2 class="text-3xl font-extrabold tracking-tight text-white">Prêt à moderniser votre cabinet ?</h2>
                        <p class="mt-4 text-lg text-teal-100">Rejoignez DOCTA et offrez à vos patients une expérience de soins digitale et sans friction.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            @if (Route::has('login'))
                                <a href="{{ url('patient/login') }}"
                                   class="inline-flex justify-center items-center px-6 py-3.5 rounded-xl font-semibold text-teal-700 bg-white hover:bg-teal-50 transition">
                                    Accéder à l'application
                                </a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex justify-center items-center px-6 py-3.5 rounded-xl font-semibold text-white border-2 border-white/70 hover:bg-white/10 transition">
                                    Devenir patient
                                </a>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Pourquoi DOCTA --}}
                <section id="pourquoi" class="py-20 bg-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Pourquoi choisir DOCTA ?</h2>
                                <p class="mt-4 text-lg text-gray-600">Une plateforme conçue spécifiquement pour les cabinets médicaux et cliniques tunisiens.</p>
                                <ul class="mt-8 space-y-5">
                                    <li class="flex gap-3">
                                        <span class="w-6 h-6 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 shrink-0 mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        </span>
                                        <p class="text-gray-700"><span class="font-semibold">Pensé pour la Tunisie :</span> compatible avec la CNAM et les assurances locales.</p>
                                    </li>
                                    <li class="flex gap-3">
                                        <span class="w-6 h-6 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 shrink-0 mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        </span>
                                        <p class="text-gray-700"><span class="font-semibold">Interface moderne :</span> une expérience claire et agréable pour le personnel et les patients.</p>
                                    </li>
                                    <li class="flex gap-3">
                                        <span class="w-6 h-6 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 shrink-0 mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        </span>
                                        <p class="text-gray-700"><span class="font-semibold">Accès patient :</span> vos patients consultent leurs rendez-vous, ordonnances et documents en ligne.</p>
                                    </li>
                                    <li class="flex gap-3">
                                        <span class="w-6 h-6 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 shrink-0 mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        </span>
                                        <p class="text-gray-700"><span class="font-semibold">Suivi financier :</span> facturation, encaissements et rapports précis en temps réel.</p>
                                    </li>
                                </ul>
                            </div>
                            <div class="relative">
                                <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-teal-50 ring-1 ring-gray-100 p-8">
                                    <p class="text-lg font-medium text-gray-800 italic">"DOCTA centralise toute la gestion de notre cabinet. Le gain de temps est considérable et nos patients apprécient l'espace en ligne."</p>
                                    <div class="mt-6 flex items-center gap-3">
                                        <span class="w-12 h-12 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold">Dr</span>
                                        <div>
                                            <p class="font-semibold text-gray-900">Dr. Témoignage</p>
                                            <p class="text-sm text-gray-500">Cabinet médical — Tunis</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Contact --}}
                <section id="contact" class="py-20 bg-gray-50">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-2xl mx-auto">
                            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Contactez-nous</h2>
                            <p class="mt-4 text-lg text-gray-600">Une question ? Notre équipe est là pour vous accompagner.</p>
                            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="mailto:contact@docta.test" class="inline-flex justify-center items-center px-6 py-3 rounded-xl font-semibold text-teal-700 bg-white border border-teal-200 hover:bg-teal-50 transition">
                                    contact@docta.tn
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            {{-- Footer --}}
            <footer class="bg-gray-900 text-gray-400">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="grid md:grid-cols-3 gap-8">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-teal-600 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </span>
                                <span class="font-extrabold text-lg text-white">DOCTA</span>
                            </div>
                            <p class="mt-4 text-sm leading-relaxed">ERP médical moderne pour cabinets et cliniques en Tunisie. Rendez-vous, dossiers, ordonnances et facturation.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">Navigation</h3>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li><a href="#fonctionnalites" class="hover:text-teal-400 transition">Fonctionnalités</a></li>
                                <li><a href="#services" class="hover:text-teal-400 transition">Services</a></li>
                                <li><a href="#pourquoi" class="hover:text-teal-400 transition">Pourquoi DOCTA</a></li>
                                <li><a href="#contact" class="hover:text-teal-400 transition">Contact</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">Accès</h3>
                            <ul class="mt-4 space-y-2 text-sm">
                                @if (Route::has('login'))
                                    <li><a href="{{ url('patient/login') }}" class="hover:text-teal-400 transition">Connexion</a></li>
                                @endif
                                @if (Route::has('register'))
                                    <li><a href="{{ route('register') }}" class="hover:text-teal-400 transition">Créer un compte patient</a></li>
                                @endif
                                @auth
                                    <li><a href="{{ url('/dashboard') }}" class="hover:text-teal-400 transition">Mon espace</a></li>
                                @endauth
                            </ul>
                        </div>
                    </div>
                    <div class="mt-10 pt-6 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between text-sm">
                        <p>&copy; {{ date('Y') }} DOCTA. Tous droits réservés.</p>
                        <p class="mt-2 sm:mt-0">ERP Médical Tunisien</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
