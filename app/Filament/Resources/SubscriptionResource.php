<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\helper\ModelLabelHelper;
use App\Models\Payment;
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
                    ->default(auth()->id())
                    ->hidden()
                    ->disabled()
                    ->required(),
                Forms\Components\Select::make('device_id')
                    ->relationship('device', 'nickname')
                    ->required(),
                Forms\Components\Select::make('plan_id')
                    ->relationship('plan', 'title', fn ($query) => $query->where('is_free', false))
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'receipt' => 'Receipt',
                        'online' => 'Online Payment',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\FileUpload::make('receipt_url')
                    ->directory('receipts')
                    ->downloadable()
                    ->disk('public')
                    ->openable()
                    ->visible(fn (callable $get) => $get('payment_method') === 'receipt')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('device.nickname')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable()
                    ->color('success'),
                Tables\Columns\BadgeColumn::make('plan.title')
                    ->badge()
                    ->colors([
                        'primary' => 'Basic Plan',
                        'success' => 'Premium Plan',
                        'danger' => 'Trial Plan',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('payment_method')
                    ->getStateUsing(fn ($record) => $record->payment_method ?? 'N/A'),
                Tables\Columns\TextColumn::make('transaction_id'),
                Tables\Columns\TextColumn::make('receipt_url')
                    ->url(fn ($record) => $record->receipt_url ?? '', true),
                Tables\Columns\TextColumn::make('remaining_time')
                    ->badge()
                    ->getStateUsing(function (Subscription $record) {
                        $expirationDate = $record->getExpirationDate();
                        if ($expirationDate && now()->lessThan($expirationDate)) {
                            $remainingDays = now()->diffInDays($expirationDate);

                            return $remainingDays > 0
                                ? (round($remainingDays)).' days remaining'
                                : 'Less than a day remaining';
                        }

                        return 'Expired';
                    })
                    ->sortable()
                    ->colors([
                        'danger' => fn ($state) => $state === 'Expired',
                        'warning' => fn ($state) => is_numeric($state) && $state <= 5,
                        'success' => fn ($state) => is_numeric($state) && $state > 5,
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('start_date')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_from'),
                        Forms\Components\DatePicker::make('start_date_to'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['start_date_from'], fn ($q) => $q->where('start_date', '>=', $data['start_date_from']))
                            ->when($data['start_date_to'], fn ($q) => $q->where('start_date', '<=', $data['start_date_to']));
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Subscribe Online')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record->payment_method === 'online') {
                            return redirect('https://payment-gateway.com/pay?amount='.$record->plan->price.'&subscription_id='.$record->id);
                        }
                    })
                    ->visible(fn ($record) => $record->payment_method === 'online' && $record->payment_status === 'pending'),
                // Tables\Actions\Action::make('Approve Payment')
                //     ->color('success')
                //     ->requiresConfirmation()
                //     ->action(fn ($record) => $record->update(['payment_status' => 'approved']))
                //     ->visible(fn ($record) => $record->payment_method === 'receipt' && $record->payment_status === 'pending'),
                // Tables\Actions\Action::make('Reject Payment')
                //     ->color('danger')
                //     ->requiresConfirmation()
                //     ->action(fn ($record) => $record->update(['payment_status' => 'rejected']))
                //     ->visible(fn ($record) => $record->payment_method === 'receipt' && $record->payment_status === 'pending'),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
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
            ->where('user_id', auth()->id());
    }

    public static function getModelLabel(): string
    {
        return ModelLabelHelper::getModelLabel(static::$model);
    }

    public static function getPluralModelLabel(): string
    {
        return ModelLabelHelper::getPluralModelLabel(static::$model);
    }
}
