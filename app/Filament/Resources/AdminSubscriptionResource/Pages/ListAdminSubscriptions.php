<?php

namespace App\Filament\Resources\AdminSubscriptionResource\Pages;

use App\Filament\Resources\AdminSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminSubscriptions extends ListRecords
{
    protected static string $resource = AdminSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()->latest();
    }
}
