<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="initialize">
            {{ $this->form }}
            
            <div class="mt-4">
                <x-filament::button type="submit" size="lg">
                    Initialiser le système
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
