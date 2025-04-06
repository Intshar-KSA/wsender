<?php

namespace App\Filament\Resources\AdminSubscriptionResource\Pages;

use App\Filament\Resources\AdminSubscriptionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAdminSubscription extends ViewRecord
{
    protected static string $resource = AdminSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->action(fn () => $this->record->update(['payment_status' => 'approved'])),

            \Filament\Actions\Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->action(fn () => $this->record->update(['payment_status' => 'rejected'])),
        ];
    }
}
