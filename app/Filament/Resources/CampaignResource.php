<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('device_id')
                    ->relationship('user_device', 'nickname')
                    ->required(),
                Forms\Components\TextInput::make('mass_prsting_id')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Select::make('content_id')
                    ->relationship('content', 'title')
                    ->required(),
                Forms\Components\Textarea::make('receivers_phones')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('message_every')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('last_phone')
                    ->tel()
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TimePicker::make('starting_time')
                    ->required(),
                Forms\Components\TimePicker::make('allowed_period_from')
                    ->required(),
                Forms\Components\TimePicker::make('allowed_period_to')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(['on' => 'On', 'off' => 'Off'])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_device.nickname')
                    ->label('Device Nickname')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mass_prsting_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content.title')
                    ->label('Content Title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('message_every')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starting_time'),
                Tables\Columns\TextColumn::make('allowed_period_from'),
                Tables\Columns\TextColumn::make('allowed_period_to'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => fn ($state): bool => $state === 'on',
                        'danger' => fn ($state): bool => $state === 'off',
                    ]),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options(['on' => 'On', 'off' => 'Off']),
                Filter::make('starting_time')
                    ->form([
                        Forms\Components\TimePicker::make('starting_time')
                            ->label('Starting Time')
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['starting_time']) {
                            $query->whereTime('starting_time', $data['starting_time']);
                        }
                    }),
                Filter::make('allowed_period_from')
                    ->form([
                        Forms\Components\TimePicker::make('allowed_period_from')
                            ->label('Allowed Period From')
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['allowed_period_from']) {
                            $query->whereTime('allowed_period_from', $data['allowed_period_from']);
                        }
                    }),
                Filter::make('allowed_period_to')
                    ->form([
                        Forms\Components\TimePicker::make('allowed_period_to')
                            ->label('Allowed Period To')
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['allowed_period_to']) {
                            $query->whereTime('allowed_period_to', $data['allowed_period_to']);
                        }
                    }),
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
            // Add any relation managers if needed
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