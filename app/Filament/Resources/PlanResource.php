<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\helper\ModelLabelHelper;
use App\Models\Plan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static ?string $navigationGroup = 'Admin';


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required(),
                Forms\Components\TextInput::make('number_of_days')
                    ->label('Number of Days')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->required()
                    ->numeric(),
                    Forms\Components\Toggle::make('is_free')
    ->label('Is Free')
    ->default(false),
Forms\Components\TextInput::make('hours')
    ->label('Hours')
    ->numeric()
    ->nullable()
    ->visible(fn ($record) => $record && $record->is_free), // يظهر فقط إذا كانت الخطة مجانية

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('number_of_days')
                    ->label('Number of Days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->sortable(),
                    Tables\Columns\BadgeColumn::make('is_free')
    ->label('Free Plan')
    ->colors([
        'success' => fn ($state): bool => $state,
        'danger' => fn ($state): bool => !$state,
    ]),
Tables\Columns\TextColumn::make('hours')
    ->label('Hours')
    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // يمكن إضافة فلاتر إذا لزم الأمر
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        // تحقق من دور المستخدم
        return auth()->user()->role === 'admin'; // يعرض المورد فقط إذا كان الدور Admin
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
