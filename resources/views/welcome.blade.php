<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion PME Locales</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="#" class="text-2xl font-bold text-blue-600 dark:text-blue-400">🛒SenGes</a>
                <div class="flex items-center">
                    <x-theme-toggle class="mr-2" />
                    <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Connexion</a>
                    <a href="{{ route('register') }}" class="ml-4 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Inscription</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Section principale -->
    <section
        class="relative flex flex-col items-center text-center py-32 px-6 bg-cover bg-center"
        style="background-image: url('{{ asset('images/hero-marche.jpg') }}');"
    >
        <div class="absolute inset-0 bg-gray-900/70"></div>
        <div class="relative">
            <h1 class="text-4xl font-extrabold text-white sm:text-5xl">
                Simplifiez la Gestion de votre PME
            </h1>
            <p class="mt-4 text-lg text-gray-200">
                Gagnez du temps et améliorez la gestion de vos produits et transactions avec notre solution digitale.
            </p>
            <a href="{{ route('register') }}" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-lg font-semibold shadow-md transition duration-300">
                Commencer Maintenant 🚀
            </a>
        </div>
    </section>

    <!-- Fonctionnalités -->
    <section class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-bold text-center">Pourquoi utiliser notre solution ?</h2>
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <h3 class="text-xl font-semibold text-blue-500">📦 Gestion des Produits</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Ajoutez, modifiez et suivez facilement vos produits.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <h3 class="text-xl font-semibold text-green-500">💳 Transactions Faciles</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Enregistrez et suivez toutes vos transactions en un clic.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <h3 class="text-xl font-semibold text-yellow-500">📊 Statistiques en Temps Réel</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Visualisez vos ventes et performances avec des graphiques.</p>
            </div>
        </div>
    </section>

    <!-- Section Témoignages -->
    <section class="bg-gray-100 dark:bg-gray-800 py-20">
        <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white">Ils nous font confiance 💙</h2>
        <div class="mt-10 max-w-5xl mx-auto px-6">
            <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg text-center">
                <p class="text-lg italic text-gray-700 dark:text-gray-300">
                    "Grâce à Gestion PME, j'ai optimisé mon activité et augmenté mes revenus de 30% !"
                </p>
                <h4 class="mt-4 font-semibold text-gray-900 dark:text-white">— Fatou Ndiaye, Commerçante</h4>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-200 dark:bg-gray-800 py-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">© 2025 Gestion PME Locales - Tous droits réservés.</p>
    </footer>

</body>
</html>
