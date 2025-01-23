@php
    $state = $getState();
    $fileName = basename($state);
    
    // Déterminer l'icône en fonction de l'extension du fichier
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $icon = match ($extension) {
        'pdf' => 'heroicon-o-document-text',
        'doc', 'docx' => 'heroicon-o-document',
        'jpg', 'jpeg', 'png' => 'heroicon-o-photo',
        default => 'heroicon-o-paper-clip'
    };
@endphp

<div class="flex items-center space-x-2">
    @if($state)
        <a 
            href="{{ Storage::url($state) }}" 
            target="_blank" 
            class="inline-flex items-center justify-center gap-1 font-medium rounded-lg bg-gray-500/10 px-3 py-1 text-gray-700 hover:bg-gray-500/20 dark:hover:bg-gray-500/20 dark:text-gray-200 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
        >
            <x-dynamic-component 
                :component="$icon" 
                class="w-5 h-5"
            />
            <span>{{ $fileName }}</span>
        </a>
    @else
        <span class="text-gray-400 dark:text-gray-500">Aucun fichier</span>
    @endif
</div>
