<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SubscriptionsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Subscriptions', Subscription::count()),
            Card::make('Active Subscriptions', Subscription::where('start_date', '<=', now())->count()),
            Card::make('Free Subscriptions', Subscription::whereHas('plan', fn ($query) => $query->where('is_free', true))->count()),
        ];
    }
}
