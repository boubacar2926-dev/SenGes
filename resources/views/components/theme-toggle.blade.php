@props(['class' => ''])

<button
    type="button"
    onclick="var d = document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', d ? 'dark' : 'light');"
    title="Changer de thème"
    aria-label="Changer de thème clair/sombre"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out '.$class]) }}
>
    <span class="dark:hidden text-lg">🌙</span>
    <span class="hidden dark:inline text-lg">☀️</span>
</button>
