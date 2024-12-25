<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (! empty($data['prospect_id'])) {
            $data['subject_type'] = \App\Models\Prospect::class;
            $data['subject_id'] = $data['prospect_id'];
        } elseif (! empty($data['client_id'])) {
            $data['subject_type'] = \App\Models\Client::class;
            $data['subject_id'] = $data['client_id'];
        }

        return $data;
    }
}
