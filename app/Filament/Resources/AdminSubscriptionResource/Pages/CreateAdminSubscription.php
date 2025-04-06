<?php

namespace App\Filament\Resources\AdminSubscriptionResource\Pages;

use App\Filament\Resources\AdminSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminSubscription extends CreateRecord
{
    protected static string $resource = AdminSubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
