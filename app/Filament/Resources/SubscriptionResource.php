<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 8;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User')
                    ->default(auth()->id())
                    ->disabled()
                    ->required(),
                Forms\Components\Select::make('device_id')
                    ->relationship('device', 'nickname')
                    ->label('Device')
                    ->required(),
                Forms\Components\Select::make('plan_id')
                    ->relationship('plan', 'title')
                    ->label('Plan')
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('device.nickname')
                    ->label('Device')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable()
                    ->color('success'), // إضافة لون

                Tables\Columns\BadgeColumn::make('plan.title')
                    ->label('Plan')
                    ->badge()
                    ->colors([
                        'primary' => 'Basic Plan',
                        'success' => 'Premium Plan',
                        'danger' => 'Trial Plan',
                    ]),

                    Tables\Columns\TextColumn::make('remaining_time')
                    ->label('Time Remaining')
                    ->badge()
                    ->getStateUsing(function (Subscription $record) {
                        // حساب تاريخ انتهاء الاشتراك بناءً على الخطة
                        $expirationDate = $record->getExpirationDate(); // تأكد من وجود دالة لحساب تاريخ الانتهاء

                        if ($expirationDate && now()->lessThan($expirationDate)) {
                            // حساب الفرق بين الآن وتاريخ الانتهاء
                            $remainingDays = now()->diffInDays($expirationDate);

                            return $remainingDays > 0
                                ? (round($remainingDays)) . ' days remaining'
                                : 'Less than a day remaining';
                        }

                        return 'Expired'; // الاشتراك منتهي
                    })
                    ->sortable()
                    ->colors([
                        'danger' => fn ($state) => $state === 'Expired',
                        'warning' => fn ($state) => is_numeric($state) && $state <= 5, // أقل من 5 أيام
                        'success' => fn ($state) => is_numeric($state) && $state > 5,  // أكثر من 5 أيام
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('start_date')
    ->form([
        Forms\Components\DatePicker::make('start_date_from')
            ->label('From'),
        Forms\Components\DatePicker::make('start_date_to')
            ->label('To'),
    ])
    ->query(function ($query, $data) {
        return $query
            ->when($data['start_date_from'], fn ($q) => $q->where('start_date', '>=', $data['start_date_from']))
            ->when($data['start_date_to'], fn ($q) => $q->where('start_date', '<=', $data['start_date_to']));
    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->where('user_id', auth()->id()); // عرض الاشتراكات الخاصة بالمستخدم الحالي فقط
}

}
