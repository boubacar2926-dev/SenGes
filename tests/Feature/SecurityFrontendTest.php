<?php

use App\Models\Produit;
use App\Models\User;

// --- Régression : injection dans les attributs HTML évalués par Alpine.js ---
//
// Deux bugs avaient été trouvés et corrigés plus tôt :
//   1. `x-data="xxx(@json($valeur))"` : @json() n'échappe que les guillemets
//      internes aux chaînes, pas les guillemets structurels du JSON produit,
//      donc l'attribut HTML se coupait au premier guillemet.
//   2. `@js(...)` : cette directive Blade n'existe pas ; utilisée par erreur
//      à la place de @json, elle reste telle quelle dans le HTML final.
// Le correctif correct est `{{ \Illuminate\Support\Js::from($valeur) }}`.

test('la recherche produits contenant des guillemets ne casse pas l’attribut x-data', function () {
    $user = User::factory()->commercant()->create();

    $recherche = 'Sac "spécial" <script>alert(1)</script>';

    $response = $this->actingAs($user)->get('/produits?search=' . urlencode($recherche));

    $response->assertOk();
    $html = $response->getContent();

    // La valeur doit être encodée via Illuminate\Support\Js (échappement JS complet,
    // apostrophes HTML-encodées en " / < etc.), jamais injectée telle quelle.
    expect($html)->not->toContain('x-data="searchSuggestions(\'' . route('produits.suggestions') . '\', "');
    expect($html)->not->toContain('<script>alert(1)</script>');

    // Aucune trace de la directive @js (qui n'existe pas et resterait littérale).
    expect($html)->not->toContain('@js(');
});

test('la recherche transactions contenant des guillemets ne casse pas l’attribut x-data', function () {
    $user = User::factory()->commercant()->create();

    $recherche = 'Test" onmouseover="alert(1)';

    $response = $this->actingAs($user)->get('/transactions?search=' . urlencode($recherche));

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->not->toContain('onmouseover="alert(1)"');
    expect($html)->not->toContain('@js(');
});

test('le formulaire de création de transaction sérialise la liste des produits sans casser x-data', function () {
    $user = User::factory()->commercant()->create();
    Produit::factory()->for($user)->create(['nom' => 'Produit "guillemets" <b>', 'prix' => 1000, 'quantite' => 5]);

    $response = $this->actingAs($user)->get('/transactions/create');

    $response->assertOk();
    $html = $response->getContent();

    // Le nom du produit ne doit jamais casser hors du x-data ni injecter du HTML brut.
    expect($html)->not->toContain('<b>');
    expect($html)->toContain('x-data="transactionForm(');
    expect($html)->not->toContain('@js(');
});

test('aucune vue Blade n’utilise la directive inexistante @js()', function () {
    $vues = glob(base_path('resources/views/**/*.blade.php'));
    $vuesRecursive = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views'), FilesystemIterator::SKIP_DOTS)
    );

    $fichiersAvecBug = [];

    foreach ($vuesRecursive as $fichier) {
        if ($fichier->getExtension() !== 'php') {
            continue;
        }

        $contenu = file_get_contents($fichier->getPathname());

        if (str_contains($contenu, '@js(')) {
            $fichiersAvecBug[] = $fichier->getPathname();
        }
    }

    expect($fichiersAvecBug)->toBe([]);
});

test('aucune vue Blade n’utilise @json() à l’intérieur d’un attribut HTML (x-data ou data-*)', function () {
    $vuesRecursive = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views'), FilesystemIterator::SKIP_DOTS)
    );

    $fichiersAvecBug = [];

    foreach ($vuesRecursive as $fichier) {
        if ($fichier->getExtension() !== 'php') {
            continue;
        }

        $contenu = file_get_contents($fichier->getPathname());

        // Repère @json( précédé, sur la même ligne, d'un attribut HTML ouvert par un guillemet
        // (x-data="...@json(...", data-xxx="...@json(...") — @json() est correct dans un bloc
        // <script> mais pas à l'intérieur d'un attribut HTML.
        foreach (preg_split('/\R/', $contenu) as $ligne) {
            if (preg_match('/\b[\w:-]+="[^"]*@json\(/', $ligne)) {
                $fichiersAvecBug[] = $fichier->getPathname() . ' :: ' . trim($ligne);
            }
        }
    }

    expect($fichiersAvecBug)->toBe([]);
});

test('la page d’accueil publique n’utilise pas de CDN Tailwind non versionné', function () {
    $response = $this->get('/');

    $response->assertOk();
    expect($response->getContent())->not->toContain('cdn.tailwindcss.com');
});

// --- En-têtes de sécurité HTTP (protection clickjacking, MIME-sniffing, CSP) ---

test('les en-têtes de sécurité HTTP sont présents sur toutes les réponses', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy');
    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'");
});

test('les en-têtes de sécurité sont aussi présents sur les pages authentifiées', function () {
    $commercant = User::factory()->commercant()->create();

    $response = $this->actingAs($commercant)->get('/dashboard');

    $response->assertOk();
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Content-Security-Policy');
});
