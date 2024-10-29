<?php

namespace App\Filament\Resources\ChatBotResource\Pages;

use App\Filament\Resources\ChatBotResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChatBot extends CreateRecord
{
    protected static string $resource = ChatBotResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
