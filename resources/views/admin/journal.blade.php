<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('📋 Journal des actions admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                @if ($logs->isEmpty())
                    <p class="p-6 text-gray-500 dark:text-gray-400">Aucune action enregistrée pour le moment.</p>
                @else
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Admin</th>
                                <th class="px-6 py-3 text-left">Action</th>
                                <th class="px-6 py-3 text-left">Commerçant concerné</th>
                                <th class="px-6 py-3 text-left">Détails</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @php
                                $labels = [
                                    'creation' => '➕ Création',
                                    'modification' => '✏️ Modification',
                                    'suspension' => '🔒 Suspension',
                                    'reactivation' => '🔓 Réactivation',
                                    'suppression' => '🗑️ Suppression',
                                ];
                            @endphp
                            @foreach ($logs as $log)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200">
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-800 dark:text-gray-300">{{ $log->admin_name }}</td>
                                    <td class="px-6 py-4 text-gray-800 dark:text-gray-300">{{ $labels[$log->action] ?? $log->action }}</td>
                                    <td class="px-6 py-4 text-gray-800 dark:text-gray-300">
                                        {{ $log->target_name }}
                                        <span class="text-sm text-gray-500 dark:text-gray-400">({{ $log->target_email }})</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $log->details }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
