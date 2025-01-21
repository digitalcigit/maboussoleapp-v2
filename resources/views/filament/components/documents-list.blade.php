@php
    $documents = $getState() ?? [];
@endphp

<div class="space-y-2">
    @if(count($documents) > 0)
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Description</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Fichier</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($documents as $document)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 dark:text-gray-100">
                                @php
                                    $type = match($document['type'] ?? 'autre') {
                                        'cv' => 'CV',
                                        'diplome' => 'Diplôme',
                                        'releve' => 'Relevé de notes',
                                        'piece_identite' => 'Pièce d\'identité',
                                        'photo' => 'Photo',
                                        default => 'Autre'
                                    };
                                @endphp
                                {{ $type }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $document['description'] ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @php
                                    $fileName = $document['file'] ?? null;
                                    $extension = $fileName ? strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) : null;
                                    $icon = match ($extension) {
                                        'pdf' => 'heroicon-o-document-text',
                                        'doc', 'docx' => 'heroicon-o-document',
                                        'jpg', 'jpeg', 'png' => 'heroicon-o-photo',
                                        default => 'heroicon-o-paper-clip'
                                    };
                                @endphp
                                
                                @if($fileName)
                                    <a 
                                        href="{{ Storage::url($fileName) }}" 
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400"
                                    >
                                        <x-dynamic-component 
                                            :component="$icon"
                                            class="w-5 h-5"
                                        />
                                        <span>{{ basename($fileName) }}</span>
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">Aucun fichier</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Aucun document fourni
        </div>
    @endif
</div>
