<x-filament-panels::page.simple>
    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :full-width="true"
            :actions="$this->getCachedFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page.simple>
