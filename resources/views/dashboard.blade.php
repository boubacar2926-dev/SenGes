<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de Bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Revenu du jour -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center mb-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">📅 Revenu du jour</h3>
                <p class="text-2xl font-bold text-blue-500 mt-2">{{ number_format($revenuAujourdhui, 0, ',', ' ') }} FCFA</p>
            </div>

            <!-- Graphique des revenus -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4">Revenus des 7 derniers jours</h3>
                <canvas id="revenuChart"></canvas>
            </div>

            <!-- Graphique des produits les plus vendus -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6">
                <h3 class="text-lg font-bold mb-4">Produits les plus vendus</h3>
                <canvas id="produitsChart"></canvas>
            </div>

        </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const ctx1 = document.getElementById('revenuChart').getContext('2d');
        const ctx2 = document.getElementById('produitsChart').getContext('2d');

        // Graphique des revenus
        const revenuChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenusParJour->pluck('date'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                datasets: [{
                    label: 'Revenu (FCFA)',
                    data: {!! json_encode($revenusParJour->pluck('total'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                    borderColor: '#1d4ed8', // Bleu 700 (mode clair)
                    fill: false
                }]
            }
        });

        // Graphique des produits les plus vendus
        const produitsChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! json_encode($produitsPopulaires->pluck('produit.nom'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                datasets: [{
                    label: 'Quantité Vendue',
                    data: {!! json_encode($produitsPopulaires->pluck('total_vendu'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                    backgroundColor: '#1d4ed8', // Bleu 700 (mode clair)
                    borderColor: '#1d4ed8', // Bleu 700 (mode clair)
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Fonction pour appliquer les couleurs en fonction du mode sombre/clair
        const applyThemeColors = () => {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const blueColor = isDarkMode ? '#60a5fa' : '#1d4ed8'; // Bleu 400 (mode sombre) ou Bleu 700 (mode clair)

            // Mettre à jour les couleurs du graphique des revenus
            revenuChart.data.datasets[0].borderColor = blueColor;
            revenuChart.update();

            // Mettre à jour les couleurs du graphique des produits
            produitsChart.data.datasets[0].backgroundColor = blueColor;
            produitsChart.data.datasets[0].borderColor = blueColor;
            produitsChart.update();
        };

        // Appliquer les couleurs au chargement de la page
        applyThemeColors();

        // Écouter les changements de mode sombre/clair
        const observer = new MutationObserver(applyThemeColors);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
      });
    </script>
</x-app-layout>
