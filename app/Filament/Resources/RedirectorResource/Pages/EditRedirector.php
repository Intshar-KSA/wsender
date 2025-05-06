<?php

namespace App\Filament\Resources\RedirectorResource\Pages;

use App\Filament\Resources\RedirectorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRedirector extends EditRecord
{
    protected static string $resource = RedirectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
