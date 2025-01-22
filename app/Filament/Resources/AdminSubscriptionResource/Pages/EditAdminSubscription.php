<?php

namespace App\Filament\Resources\AdminSubscriptionResource\Pages;

use App\Filament\Resources\AdminSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdminSubscription extends EditRecord
{
    protected static string $resource = AdminSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
