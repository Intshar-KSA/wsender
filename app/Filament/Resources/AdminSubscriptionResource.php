<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminSubscriptionResource\Pages;
use App\helper\ModelLabelHelper;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class AdminSubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Admin';

    protected static ?string $label = 'Subscriptions request';

    // public static function canAccess(): bool
    // {
    //     // تحقق من دور المستخدم
    //     return auth()->user()->role === 'admin'; // يعرض المورد فقط إذا كان الدور Admin
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        // تصفير الجهاز عند تغيير المستخدم
                        $set('device_id', null);
                    }),

                Forms\Components\Select::make('device_id')
                    ->label('Device')
                    ->required()
                    ->options(fn (callable $get) => \App\Models\Device::withoutGlobalScopes()
                        ->where('user_id', $get('user_id'))
                        ->pluck('nickname', 'id')
                        ->toArray()
                    )
                    ->searchable()
                    ->reactive()
                    ->disabled(fn (callable $get) => ! $get('user_id'))
                    ->hint('Select a user first'),

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
                    ->required(),
                Forms\Components\FileUpload::make('receipt_url')
                    ->label('Upload Receipt')
                    ->directory('receipts')
                    ->disk('public')
                    ->downloadable()
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
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('device.nickname')
                    ->label('Device')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('plan.title')
                    ->label('Plan')
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
                    ->label('Payment Method'),
                Tables\Columns\TextColumn::make('receipt_url')
                    ->label('Receipt')
                    ->url(fn ($record) => $record->receipt_url ? asset('storage/'.$record->receipt_url) : null, true)
                    ->openUrlInNewTab(),

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
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn ($record) => AdminSubscriptionResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Approve Payment')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['payment_status' => 'approved']);
                    })
                    ->visible(fn ($record) => $record->payment_status === 'pending'),
                Tables\Actions\Action::make('Reject Payment')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['payment_status' => 'rejected']);
                    })
                    ->visible(fn ($record) => $record->payment_status === 'pending'),
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
            'index' => Pages\ListAdminSubscriptions::route('/'),
            'create' => Pages\CreateAdminSubscription::route('/create'),

            'edit' => Pages\EditAdminSubscription::route('/{record}/edit'),
            'view' => Pages\ViewAdminSubscription::route('/{record}/view'),
        ];
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
