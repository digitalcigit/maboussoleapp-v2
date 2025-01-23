<x-filament-panels::page>
    <x-filament::grid>
        {{-- Section Statut du Dossier --}}
        <x-filament::grid.column span="4">
            <x-filament::section>
                <x-slot name="heading">
                    Statut de votre dossier
                </x-slot>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">État actuel :</span>
                        <span class="text-sm font-medium">En cours</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Documents manquants :</span>
                        <span class="text-sm font-medium">2</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Prochaine étape :</span>
                        <span class="text-sm font-medium">Validation des documents</span>
                    </div>
                </div>
            </x-filament::section>
        </x-filament::grid.column>

        {{-- Section Documents Récents --}}
        <x-filament::grid.column span="4">
            <x-filament::section>
                <x-slot name="heading">
                    Documents récents
                </x-slot>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Passeport</span>
                        <span class="text-sm text-success-600">Validé</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Relevé de notes</span>
                        <span class="text-sm text-warning-600">En attente</span>
                    </div>
                </div>
            </x-filament::section>
        </x-filament::grid.column>

        {{-- Section Notifications --}}
        <x-filament::grid.column span="4">
            <x-filament::section>
                <x-slot name="heading">
                    Notifications récentes
                </x-slot>

                <div class="space-y-2">
                    <div class="text-sm">
                        <p class="font-medium">Document validé</p>
                        <p class="text-gray-500">Votre passeport a été validé</p>
                        <p class="text-xs text-gray-400">Il y a 2 heures</p>
                    </div>
                    
                    <div class="text-sm">
                        <p class="font-medium">Rappel</p>
                        <p class="text-gray-500">N'oubliez pas de soumettre vos relevés de notes</p>
                        <p class="text-xs text-gray-400">Il y a 1 jour</p>
                    </div>
                </div>
            </x-filament::section>
        </x-filament::grid.column>
    </x-filament::grid>

    {{-- Section Actions Rapides --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Actions rapides
        </x-slot>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-filament::button
                wire:click="redirectToUpload"
                icon="heroicon-o-arrow-up-tray"
                class="justify-center">
                Déposer un document
            </x-filament::button>

            <x-filament::button
                wire:click="redirectToDocuments"
                icon="heroicon-o-document-text"
                class="justify-center">
                Voir mes documents
            </x-filament::button>

            <x-filament::button
                wire:click="redirectToProfile"
                icon="heroicon-o-user"
                class="justify-center">
                Mon profil
            </x-filament::button>

            <x-filament::button
                wire:click="redirectToSupport"
                icon="heroicon-o-chat-bubble-left-ellipsis"
                class="justify-center">
                Contacter le support
            </x-filament::button>
        </div>
    </x-filament::section>

    @if($this->hasHeaderWidgets)
        <x-filament::widgets
            :widgets="$this->getHeaderWidgets()"
            :columns="$this->getHeaderWidgetsColumns()"
            class="mt-6"
        />
    @endif
</x-filament-panels::page>
