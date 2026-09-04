<x-guest-layout>
    <!-- Logo / Titre -->
    <div class="flex flex-col items-center">
        <h1 class="text-2xl font-bold text-blue-700 dark:text-blue-400">SenGes</h1>
        <p class="mt-1 text-center text-sm text-gray-600 dark:text-gray-400">
            Créez votre compte pour gérer votre activité
        </p>
    </div>

    <!-- Formulaire d'inscription -->
    <form method="POST" action="{{ route('register') }}" class="mt-4 space-y-3">
        @csrf

        <!-- Nom -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
            <input type="text" name="name" id="name"
                class="mt-1 w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition duration-300"
                placeholder="Votre nom" required autofocus>
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-400" />
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" name="email" id="email"
                class="mt-1 w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition duration-300"
                placeholder="Votre email" required>
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-400" />
        </div>

        <!-- Mot de passe -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe</label>
            <div class="relative mt-1">
                <input type="password" name="password" id="password"
                    class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition duration-300"
                    placeholder="Votre mot de passe" required>
                <span class="absolute inset-y-0 right-3 flex items-center cursor-pointer" onclick="togglePassword('password', 'eyeIcon1')">
                    <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z" />
                    </svg>
                </span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-400" />
        </div>

        <!-- Confirmation du mot de passe -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmer le mot de passe</label>
            <div class="relative mt-1">
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition duration-300"
                    placeholder="Confirmez votre mot de passe" required>
                <span class="absolute inset-y-0 right-3 flex items-center cursor-pointer" onclick="togglePassword('password_confirmation', 'eyeIcon2')">
                    <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z" />
                    </svg>
                </span>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-red-400" />
        </div>

        <!-- Bouton d'inscription -->
        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded-md shadow-md transition duration-300">
            🚀 S'inscrire
        </button>
    </form>

    <!-- Lien vers la connexion -->
    <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
        Vous avez déjà un compte ?
        <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Se connecter</a>
    </p>

    <!-- Script pour afficher/masquer le mot de passe -->
    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);

            if (field.type === 'password') {
                field.type = 'text';
                icon.innerHTML = '<path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z"/>';
            } else {
                field.type = 'password';
                icon.innerHTML = '<path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z"/>';
            }
        }
    </script>
</x-guest-layout>
