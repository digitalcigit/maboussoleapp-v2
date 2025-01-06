<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public function mount(): void
    {
        parent::mount();
    }

    protected function afterCreate(): void
    {
        $client = $this->record;
        if ($client->prospect) {
            $client->prospect->update(['status' => 'converted']);
        }
    }
}
