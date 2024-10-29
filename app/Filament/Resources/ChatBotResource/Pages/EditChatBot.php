<?php

namespace App\Filament\Resources\ChatBotResource\Pages;

use App\Filament\Resources\ChatBotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChatBot extends EditRecord
{
    protected static string $resource = ChatBotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
