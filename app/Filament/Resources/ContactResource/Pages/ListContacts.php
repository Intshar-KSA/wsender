<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('bulkCreate')
            ->icon('heroicon-o-users')
            ->url(ContactResource::getUrl('bulk-create')), // رابط صفحة الإنشاء الجماعي
        ];
    }
}
