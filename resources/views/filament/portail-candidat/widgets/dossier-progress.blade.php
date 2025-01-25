<x-filament::widget>
    <x-filament::card>
        @if($this->getProgress())
            <div class="space-y-6">
                {{-- Barre de progression principale --}}
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $this->getProgress()['progress_percent'] }}%">
                    </div>
                </div>
                
                {{-- Étapes avec icônes --}}
                <div class="grid grid-cols-4 gap-4">
                    @foreach($this->getProgress()['etapes'] as $step => $info)
                        <div @class([
                            'flex flex-col items-center p-4 rounded-lg transition-all duration-200',
                            'bg-primary-50 border border-primary-200' => $step === $this->getProgress()['current_step'],
                            'bg-gray-50' => $step !== $this->getProgress()['current_step'],
                            'opacity-50' => $step > $this->getProgress()['current_step'],
                        ])>
                            {{-- Icône et label --}}
                            <x-dynamic-component 
                                :component="$info['icon']" 
                                @class([
                                    'w-8 h-8',
                                    'text-primary-600' => $step === $this->getProgress()['current_step'],
                                    'text-gray-400' => $step !== $this->getProgress()['current_step'],
                                ])
                            />
                            <span class="text-sm font-medium mt-2">{{ $info['label'] }}</span>
                            <span class="text-xs text-gray-500 mt-1">{{ $info['description'] }}</span>
                            
                            {{-- Informations de l'étape actuelle --}}
                            @if($step === $this->getProgress()['current_step'])
                                <div class="mt-4 w-full">
                                    <div class="text-xs font-medium text-primary-600">
                                        {{ $info['status_label'] }}
                                    </div>
                                    
                                    {{-- Documents requis --}}
                                    @if(!empty($info['documents_requis']))
                                        <div class="mt-3 space-y-2">
                                            <p class="text-xs font-medium text-gray-600">Documents requis :</p>
                                            <ul class="space-y-1">
                                                @foreach($info['documents_requis'] as $type => $doc)
                                                    <li class="flex items-center text-xs">
                                                        @if($doc['uploaded'])
                                                            <x-heroicon-s-check-circle class="w-4 h-4 text-success-500 mr-1"/>
                                                        @else
                                                            <x-heroicon-s-exclamation-circle class="w-4 h-4 text-danger-500 mr-1"/>
                                                        @endif
                                                        {{ $doc['label'] }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center text-gray-500 py-4">
                Aucun dossier trouvé
            </div>
        @endif
    </x-filament::card>
</x-filament::widget>
