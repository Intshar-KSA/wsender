<?php

namespace App\Filament\Resources\QuickSendResource\Pages;

use App\Filament\Resources\QuickSendResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuickSends extends ListRecords
{
    protected static string $resource = QuickSendResource::class;

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
