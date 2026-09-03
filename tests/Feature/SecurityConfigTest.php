<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

// --- Seeder : pas d'identifiants en dur, comptes créés via l'environnement ---
//
// env() lit potentiellement $_ENV / $_SERVER avant putenv() (selon l'ordre des
// adaptateurs phpdotenv). On écrit donc directement dans les trois pour que la
// valeur de test soit bien celle vue par le seeder, quel que soit ce qui a été
// chargé depuis le .env local, puis on restaure l'état initial après chaque test.
function setSeedEnv(string $key, ?string $value): void
{
    if ($value === null) {
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);

        return;
    }

    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("{$key}={$value}");
}

beforeEach(function () {
    $this->seedEnvKeys = [
        'SEED_ADMIN_EMAIL',
        'SEED_ADMIN_PASSWORD',
        'SEED_DEMO_COMMERCANT_EMAIL',
        'SEED_DEMO_COMMERCANT_PASSWORD',
    ];
    $this->seedEnvBackup = [];
    foreach ($this->seedEnvKeys as $key) {
        $this->seedEnvBackup[$key] = $_ENV[$key] ?? null;
    }
});

afterEach(function () {
    foreach ($this->seedEnvBackup as $key => $value) {
        setSeedEnv($key, $value);
    }
});

test('le seeder ne crée aucun compte si les variables d’environnement sont absentes', function () {
    foreach ($this->seedEnvKeys as $key) {
        setSeedEnv($key, null);
    }

    (new DatabaseSeeder)->run();

    expect(User::count())->toBe(0);
});

test('le seeder admin lit l’identifiant et le mot de passe depuis l’environnement', function () {
    setSeedEnv('SEED_ADMIN_EMAIL', 'seed-admin@example.com');
    setSeedEnv('SEED_ADMIN_PASSWORD', 'un-mot-de-passe-non-devine');
    setSeedEnv('SEED_DEMO_COMMERCANT_EMAIL', null);
    setSeedEnv('SEED_DEMO_COMMERCANT_PASSWORD', null);

    (new DatabaseSeeder)->run();

    $this->assertDatabaseHas('users', [
        'email' => 'seed-admin@example.com',
        'role' => 'admin',
    ]);

    $admin = User::where('email', 'seed-admin@example.com')->firstOrFail();
    expect(Hash::check('un-mot-de-passe-non-devine', $admin->password))->toBeTrue();
});

test('relancer le seeder n’écrase pas un mot de passe admin déjà changé (firstOrCreate, pas updateOrCreate)', function () {
    setSeedEnv('SEED_ADMIN_EMAIL', 'seed-admin2@example.com');
    setSeedEnv('SEED_ADMIN_PASSWORD', 'mot-de-passe-initial');
    setSeedEnv('SEED_DEMO_COMMERCANT_EMAIL', null);
    setSeedEnv('SEED_DEMO_COMMERCANT_PASSWORD', null);

    (new DatabaseSeeder)->run();

    $admin = User::where('email', 'seed-admin2@example.com')->firstOrFail();
    $admin->update(['password' => Hash::make('mot-de-passe-change-manuellement')]);

    // Un redéploiement relance `php artisan migrate --force --seed` avec les
    // mêmes variables d'environnement : le mot de passe changé manuellement
    // ne doit pas être réécrasé.
    (new DatabaseSeeder)->run();

    $admin->refresh();
    expect(Hash::check('mot-de-passe-change-manuellement', $admin->password))->toBeTrue();
    expect(Hash::check('mot-de-passe-initial', $admin->password))->toBeFalse();
});

test('aucun seeder committé n’appelle Hash::make() avec un mot de passe littéral en dur', function () {
    $seederFiles = glob(database_path('seeders/*.php'));
    expect($seederFiles)->not->toBeEmpty();

    foreach ($seederFiles as $file) {
        $contents = file_get_contents($file);

        // Détecte Hash::make('...') / Hash::make("...") avec une chaîne littérale
        // (un vrai seeder sûr passe une variable issue de env(), jamais un littéral).
        expect($contents)->not->toMatch('/Hash::make\(\s*[\'"]/');
    }
});

// --- Configuration applicative ---

test('le débogage est désactivé par défaut si APP_DEBUG n’est pas défini (pas de fuite de stack trace)', function () {
    // config/app.php doit utiliser `env('APP_DEBUG', false)` : si la variable
    // d'environnement n'est pas définie sur l'hébergeur, l'app ne doit jamais
    // basculer en mode debug par défaut.
    $configSource = file_get_contents(config_path('app.php'));
    expect($configSource)->toContain("env('APP_DEBUG', false)");
});

test('CORS n’autorise pas les identifiants (cookies) cross-origin', function () {
    expect(config('cors.supports_credentials'))->toBeFalse();
});

test('CORS_ALLOWED_ORIGINS permet de restreindre les origines sans changer le défaut', function () {
    $configSource = file_get_contents(config_path('cors.php'));
    expect($configSource)->toContain('CORS_ALLOWED_ORIGINS');
    expect(config('cors.allowed_origins'))->toBeArray();
});

// --- .gitignore : les fichiers sensibles ne doivent jamais être trackables ---

test('.gitignore exclut les fichiers .env sensibles', function () {
    $gitignore = file_get_contents(base_path('.gitignore'));

    expect($gitignore)
        ->toContain('.env')
        ->toContain('.env.backup')
        ->toContain('.env.production');
});

test('.dockerignore existe et exclut .git, .env et les dépendances locales', function () {
    expect(file_exists(base_path('.dockerignore')))->toBeTrue();

    $dockerignore = file_get_contents(base_path('.dockerignore'));

    expect($dockerignore)
        ->toContain('.git')
        ->toContain('.env')
        ->toContain('/vendor')
        ->toContain('/node_modules');
});

test('aucun seeder mort avec des identifiants par défaut n’est présent dans le repo', function () {
    expect(file_exists(database_path('seeders/UserSeeder.php')))->toBeFalse();
});
