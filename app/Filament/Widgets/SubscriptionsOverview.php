<?php
namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SubscriptionsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $userId = auth()->id(); // الحصول على معرف المستخدم الحالي

        return [
            Card::make('Total Subscriptions', Subscription::where('user_id', $userId)->count()),

            Card::make('Active Subscriptions', Subscription::where('user_id', $userId)
                ->where('start_date', '<=', now()) // بدأ الاشتراك
                ->get()
                ->filter(fn ($subscription) => !$subscription->isExpired()) // التحقق من أن الاشتراك غير منتهي
                ->count()),

            Card::make('Free Subscriptions', Subscription::where('user_id', $userId)
                ->whereHas('plan', fn ($query) => $query->where('is_free', true)) // الاشتراكات المجانية
                ->where('start_date', '<=', now()) // بدأ الاشتراك
                ->get()
                ->filter(fn ($subscription) => !$subscription->isExpired()) // التحقق من أن الاشتراك غير منتهي
                ->count()),
        ];
    }
}
