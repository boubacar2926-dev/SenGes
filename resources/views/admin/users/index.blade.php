<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('👨‍💼 Gestion des Commerçants') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Bouton Ajouter un Commerçant -->
            <div class="flex justify-between items-center mb-6">
                <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition duration-300">
                    + Ajouter un Commerçant
                </a>
            </div>

            <!-- Message de succès -->
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded-lg shadow-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Version Desktop : Tableau -->
            <div class="hidden sm:block bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Nom</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach ($commercants as $commercant)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200">
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-300">{{ $commercant->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $commercant->email }}</td>
                                <td class="px-6 py-4 flex justify-center space-x-2">
                                    <!-- Modifier -->
                                    <a href="{{ route('admin.users.edit', $commercant) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                        ✏️ Modifier
                                    </a>

                                    <!-- Suspendre / Réactiver -->
                                    <form action="{{ route('admin.users.suspend', $commercant) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="{{ $commercant->suspended ? 'bg-green-500 hover:bg-green-600' : 'bg-yellow-500 hover:bg-yellow-600' }} text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                            {{ $commercant->suspended ? '🔓 Réactiver' : '🔒 Suspendre' }}
                                        </button>
                                    </form>

                                    <!-- Supprimer -->
                                    <form action="{{ route('admin.users.destroy', $commercant) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Version Mobile : Affichage sous forme de cartes -->
            <div class="sm:hidden space-y-4">
                @foreach ($commercants as $commercant)
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $commercant->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $commercant->email }}
                        </p>
                        <div class="flex flex-col mt-4 space-y-2">
                            <!-- Modifier -->
                            <a href="{{ route('admin.users.edit', $commercant) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-md text-center">
                                ✏️ Modifier
                            </a>

                            <!-- Suspendre / Réactiver -->
                            <form action="{{ route('admin.users.suspend', $commercant) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="{{ $commercant->suspended ? 'bg-green-500 hover:bg-green-600' : 'bg-yellow-500 hover:bg-yellow-600' }} text-white px-4 py-2 rounded-md shadow-md w-full">
                                    {{ $commercant->suspended ? '🔓 Réactiver' : '🔒 Suspendre' }}
                                </button>
                            </form>

                            <!-- Supprimer -->
                            <form action="{{ route('admin.users.destroy', $commercant) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md w-full">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $commercants->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
