<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('device_id')
                //     ->required()
                //     ->numeric(),
                    Forms\Components\Select::make('device_id')
                    ->relationship('user_device', 'nickname'),
                Forms\Components\TextInput::make('mass_prsting_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('content_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('receivers_phones')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('message_every')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('last_phone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('starting_time')
                    ->required(),
                Forms\Components\TextInput::make('allowed_period_from')
                    ->required(),
                Forms\Components\TextInput::make('allowed_period_to')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mass_prsting_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message_every')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starting_time'),
                Tables\Columns\TextColumn::make('allowed_period_from'),
                Tables\Columns\TextColumn::make('allowed_period_to'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
