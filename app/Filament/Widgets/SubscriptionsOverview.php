<?php
namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $userId = auth()->id(); // الحصول على معرف المستخدم الحالي

        return [
            Stat::make('Total Subscriptions', Subscription::where('user_id', $userId)->count())->label(__('Total Subscriptions')),

            Stat::make('Active Subscriptions', Subscription::where('user_id', $userId)
                ->where('start_date', '<=', now()) // بدأ الاشتراك
                ->get()
                ->filter(fn ($subscription) => !$subscription->isExpired()) // التحقق من أن الاشتراك غير منتهي
                ->count())->label(__('Active Subscriptions')),

                Stat::make('Free Subscriptions', Subscription::where('user_id', $userId)
                ->whereHas('plan', fn ($query) => $query->where('is_free', true)) // الاشتراكات المجانية
                ->where('start_date', '<=', now()) // بدأ الاشتراك
                ->get()
                ->filter(fn ($subscription) => !$subscription->isExpired()) // التحقق من أن الاشتراك غير منتهي
                ->count())->label(__('Free Subscriptions')),
        ];
    }
}
