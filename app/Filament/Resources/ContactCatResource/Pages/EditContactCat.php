<?php

namespace App\Filament\Resources\ContactCatResource\Pages;

use App\Filament\Resources\ContactCatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContactCat extends EditRecord
{
    protected static string $resource = ContactCatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}