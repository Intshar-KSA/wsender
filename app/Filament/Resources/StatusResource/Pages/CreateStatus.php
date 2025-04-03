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

        // ✅ ضبط التاريخ الحالي إذا لم يُحدد من قبل المستخدم
        $now = now();

        $data['start_date'] = $data['start_date'] ?? $now->copy()->toDateString();
        $data['end_date'] = $data['end_date'] ?? $now->copy()->toDateString();

        $this->record = Status::create(collect($data)->except('devices')->toArray());

        if (isset($data['devices'])) {
            $this->record->devices()->sync($data['devices']);
        }

        return $data;
    }
}
