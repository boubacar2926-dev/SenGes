<x-guest-layout>
    <!-- Logo / Titre -->
    <div class="flex flex-col items-center">
        <h1 class="text-2xl font-bold text-blue-700 dark:text-blue-400">SenGes</h1>
        <p class="mt-1 text-center text-sm text-gray-600 dark:text-gray-400">
            Connectez-vous à votre espace de gestion
        </p>
    </div>

    <!-- Message de statut -->
    <x-auth-session-status class="mt-3" :status="session('status')" />

    <!-- Formulaire de connexion -->
    <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-3">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" name="email" id="email"
                class="mt-1 w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition duration-300"
                placeholder="Votre email" required autofocus>
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-400" />
        </div>

        <!-- Mot de passe -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe</label>
            <div class="relative mt-1">
                <input type="password" name="password" id="password"
                    class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition duration-300"
                    placeholder="Votre mot de passe" required>
                <span class="absolute inset-y-0 right-3 flex items-center cursor-pointer" onclick="togglePassword()">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z" />
                    </svg>
                </span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-400" />
        </div>

        <!-- Se souvenir de moi -->
        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="text-blue-600 dark:text-blue-400 rounded border-gray-300 dark:border-gray-600 focus:ring focus:ring-blue-300">
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Mot de passe oublié ?</a>
            @endif
        </div>

        <!-- Bouton de connexion -->
        <button type="submit"
            class="w-full bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold py-2 rounded-md shadow-md transition duration-300">
            🚀 Se connecter
        </button>
    </form>

    <!-- Lien vers l'inscription -->
    <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
        Vous n'avez pas encore de compte ?
        <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Créer un compte</a>
    </p>

    <!-- Script pour afficher/masquer le mot de passe -->
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.innerHTML = '<path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z"/>';
            } else {
                passwordField.type = 'password';
                eyeIcon.innerHTML = '<path d="M10 3C5 3 1 8 1 10s4 7 9 7 9-5 9-7-4-7-9-7zm0 12c-3 0-6-3.5-6-5s3-5 6-5 6 3.5 6 5-3 5-6 5zm0-8a3 3 0 110 6 3 3 0 010-6z"/>';
            }
        }
    </script>
</x-guest-layout>
