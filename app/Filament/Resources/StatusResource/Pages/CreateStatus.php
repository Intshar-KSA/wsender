<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use App\Models\Status;
use Filament\Resources\Pages\CreateRecord;

class CreateStatus extends CreateRecord
{
    protected static string $resource = StatusResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->record = Status::create(collect($data)->except('devices')->toArray());

        if (isset($data['devices'])) {
            $this->record->devices()->sync($data['devices']);
        }

        return $data;
    }
}
