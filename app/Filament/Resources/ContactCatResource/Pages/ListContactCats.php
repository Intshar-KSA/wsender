<?php

namespace App\Filament\Resources\ContactCatResource\Pages;

use App\Filament\Resources\ContactCatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContactCats extends ListRecords
{
    protected static string $resource = ContactCatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
