<?php

namespace App\Filament\Resources\ContactCatResource\Pages;

use App\Filament\Resources\ContactCatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContactCat extends CreateRecord
{
    protected static string $resource = ContactCatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    
}