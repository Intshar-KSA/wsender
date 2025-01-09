<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
use App\Models\Subscription;
use Filament\Resources\Resource;
use App\Filament\Resources\SubscriptionResource\Pages;

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
                    ->hidden()
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

                    Forms\Components\Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'receipt' => 'Receipt',
                        'online' => 'Online Payment',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\FileUpload::make('receipt_url')
                    ->label('Upload Receipt')
                    ->directory('receipts')
                    ->downloadable()
                    ->openable()
                  
                    ->visible(fn (callable $get) => $get('payment_method') === 'receipt') // إظهار فقط إذا لم يكن النوع نصًا

                    // ->visible(fn ($record) => $record && $record->payment_method === 'receipt')
                    ->required(),

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
//         $subscriptionsWithoutPayments = Subscription::doesntHave('payment')->get();

// foreach ($subscriptionsWithoutPayments as $subscription) {
//     Payment::create([
//         'subscription_id' => $subscription->id,
//         'payment_method' => 'receipt', // أو أي قيمة افتراضية مناسبة
//         'payment_status' => 'pending',
//     ]);
// }

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
                    Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment Status')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ]),

                    Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->getStateUsing(fn ($record) => $record->payment_method ?? 'N/A'),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID'),

                    Tables\Columns\TextColumn::make('receipt_url')
                    ->label('Receipt')
                    ->url(fn ($record) => $record->receipt_url??"", true),
                    // ->visible(fn ($record) => $record->payment_method && $record->payment_method === 'receipt'),



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
                                ? (round($remainingDays)).' days remaining'
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
                Tables\Actions\Action::make('Subscribe Online')
                ->label('Subscribe')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function ($record) {
                    if ($record->payment_method === 'online') {
                        return redirect('https://payment-gateway.com/pay?amount=' . $record->plan->price . '&subscription_id=' . $record->id);
                    }
                })
                ->visible(fn ($record) => $record->payment_method === 'online' && $record->payment_status === 'pending'),

                Tables\Actions\Action::make('Approve Payment')
    ->label('Approve')
    ->color('success')
    ->requiresConfirmation()
    ->action(function ($record) {
        $record->update(['payment_status' => 'approved']);
    })
    ->visible(fn ($record) => $record->payment_method === 'receipt' && $record->payment_status === 'pending'),

Tables\Actions\Action::make('Reject Payment')
    ->label('Reject')
    ->color('danger')
    ->requiresConfirmation()
    ->action(function ($record) {
        $record->update(['payment_status' => 'rejected']);
    })
    ->visible(fn ($record) => $record->payment_method === 'receipt' && $record->payment_status === 'pending'),


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
