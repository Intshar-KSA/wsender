<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatBotResource\Pages;
use App\Models\ChatBot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class ChatBotResource extends Resource
{
    protected static ?string $model = ChatBot::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('device_id')
                    ->relationship('user_device', 'nickname')
                    ->required()
                    ->label('Device'),
                Forms\Components\Textarea::make('msg')
                    ->required()
                    ->columnSpanFull()
                    ->label('Message'),
                Forms\Components\Select::make('content_id')
                    ->relationship('content', 'title')
                    ->required()
                    ->label('Content'),
                Forms\Components\Select::make('type')
                    ->options([
                        'exact' => 'Exact',
                        'contains' => 'Contains',
                    ])
                    ->required()
                    ->label('Match Type'),
                Forms\Components\Select::make('msg_type')
                    ->options([
                        'reply' => 'Reply',
                    ])
                    ->required()
                    ->label('Message Type'),
                Forms\Components\Toggle::make('status')
                    ->required()
                    ->onColor('success')
                    ->offColor('danger')
                    ->label('Status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_device.nickname')
                    ->label('Device')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('content.title')
                    ->label('Content')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Match Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('msg_type')
                    ->label('Message Type')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
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
                SelectFilter::make('device_id')
                    ->label('Device')
                    ->relationship('user_device', 'nickname')
                    ->searchable(),
                
                SelectFilter::make('content_id')
                    ->label('Content')
                    ->relationship('content', 'title')
                    ->searchable(),
                
                SelectFilter::make('type')
                    ->options([
                        'exact' => 'Exact',
                        'contains' => 'Contains',
                    ])
                    ->label('Match Type'),
                
                SelectFilter::make('msg_type')
                    ->options([
                        'reply' => 'Reply',
                    ])
                    ->label('Message Type'),
                
                SelectFilter::make('status')
                    ->options([
                        'on' => 'On',
                        'off' => 'Off',
                    ])
                    ->label('Status'),
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
            // Define related resources if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatBots::route('/'),
            'create' => Pages\CreateChatBot::route('/create'),
            'edit' => Pages\EditChatBot::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user_device', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
    }
}