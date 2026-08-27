<x-guest-layout>
    <div class="text-center">
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-teal-100 text-teal-600 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </span>
        <h2 class="text-2xl font-bold text-gray-900">Créer un compte patient</h2>
        <p class="mt-2 text-sm text-gray-500">Créez votre espace pour suivre vos rendez-vous, ordonnances et documents médicaux.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8">
        @csrf

        <!-- Civilité -->
        <div>
            <x-input-label for="title" :value="__('Civilité')" />
            <select id="title" name="title" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                <option value="mr" @selected(old('title') === 'mr')>Monsieur</option>
                <option value="mme" @selected(old('title') === 'mme')>Madame</option>
                <option value="dr" @selected(old('title') === 'dr')>Docteur</option>
            </select>
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <!-- Prénom / Nom -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('Prénom')" />
                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Nom')" />
                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <!-- Genre / Date de naissance -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="gender" :value="__('Genre')" />
                <select id="gender" name="gender" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                    <option value="male" @selected(old('gender') === 'male')>Homme</option>
                    <option value="female" @selected(old('gender') === 'female')>Femme</option>
                </select>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="birth_date" :value="__('Date de naissance')" />
                <x-text-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" :value="old('birth_date')" required autocomplete="bday" />
                <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
            </div>
        </div>

        <!-- CIN / Téléphone -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="cin" :value="__('Numéro CIN (optionnel)')" />
                <x-text-input id="cin" class="block mt-1 w-full" type="text" name="cin" :value="old('cin')" maxlength="20" autocomplete="off" />
                <x-input-error :messages="$errors->get('cin')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Téléphone')" />
                <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="+216 ..." />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Adresse -->
        <div class="mt-4">
            <x-input-label for="address" :value="__('Adresse')" />
            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" autocomplete="street-address" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- Ville / Gouvernorat -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="city" :value="__('Ville')" />
                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" autocomplete="address-level2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="governorate" :value="__('Gouvernorat')" />
                <select id="governorate" name="governorate" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Sélectionner</option>
                    @foreach (\App\Enums\Governorate::cases() as $gov)
                        <option value="{{ $gov->value }}" @selected(old('governorate') === $gov->value)>{{ $gov->value }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('governorate')" class="mt-2" />
            </div>
        </div>

        <!-- Mot de passe -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmation -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit"
                    class="w-full inline-flex justify-center items-center px-6 py-3 rounded-xl font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-lg shadow-teal-600/20 transition">
                Créer mon compte
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            Déjà inscrit ?
            <a class="font-semibold text-teal-600 hover:text-teal-700" href="{{ url('patient/login') }}">
                Connectez-vous
            </a>
        </p>
    </form>
</x-guest-layout>
