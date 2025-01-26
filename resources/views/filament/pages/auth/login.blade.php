<x-filament-panels::page.simple class="!p-0 !m-0">
    <div class="min-h-screen">
        <!-- Formulaire (droite) -->
        <div class="absolute right-0 top-0 bottom-0 w-[500px] bg-white">
            <div class="h-full flex flex-col">
                <div class="flex-1 p-8">
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900">
                            {{ __('Bienvenue') }}
                        </h2>
                        <p class="mt-2 text-gray-600">
                            {{ __('Connectez-vous à votre espace administrateur') }}
                        </p>
                    </div>

                    <x-filament-panels::form wire:submit="authenticate">
                        <div class="space-y-5">
                            {{ $this->form }}
                        </div>

                        <x-filament-panels::form.actions 
                            :actions="$this->getCachedFormActions()"
                            :full-width="$this->hasFullWidthFormActions()"
                            class="mt-6"
                        />
                    </x-filament-panels::form>
                </div>

                <div class="p-6 text-center border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        {{ date('Y') }} Ma Boussole. Tous droits réservés.
                    </p>
                </div>
            </div>
        </div>

        <!-- Image et message (gauche) -->
        <div class="hidden md:block absolute left-0 right-[500px] top-0 bottom-0">
            <!-- Image de fond -->
            <div class="absolute inset-0">
                <img src="/images/students/graduate-2.jpg" alt="Graduate" class="w-full h-full object-cover">
            </div>
            
            <!-- Overlay violet simple -->
            <div class="absolute inset-0 bg-primary-600/20"></div>
            
            <!-- Contenu -->
            <div class="relative h-full flex items-center justify-center p-12">
                <div class="text-center max-w-lg">
                    <img src="/images/students/logo_mabou.png" alt="Logo" class="h-20 mx-auto mb-8 drop-shadow-xl">
                    <h3 class="text-5xl font-bold text-white leading-tight drop-shadow-xl">
                        Votre parcours vers<br>l'excellence commence ici
                    </h3>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
