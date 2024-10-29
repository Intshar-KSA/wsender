<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatBotResource\Pages;
use App\Filament\Resources\ChatBotResource\RelationManagers;
use App\Models\ChatBot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatBotResource extends Resource
{
    protected static ?string $model = ChatBot::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('device_id')
                //     ->required()
                //     ->numeric(),
                    Forms\Components\Select::make('device_id')
                    ->relationship('user_device', 'nickname') // Assuming there is a relationship defined in the ChatBot model
                    ->required(),
                Forms\Components\Textarea::make('msg')
                    ->required()
                    ->columnSpanFull(),
                // Forms\Components\TextInput::make('content_id')
                //     ->required()
                //     ->numeric(),
                    Forms\Components\Select::make('content_id')
                    ->relationship('content', 'title') // Assuming there is a relationship defined in the ChatBot model
                    ->required(),
                // Forms\Components\TextInput::make('type')
                //     ->required(),
                    Forms\Components\Select::make('type')
                    ->options([
                        'exact' => 'exact',
                         'contains' => 'contains',
                        // 'doc' => 'Document',
                    ])
                    ->required(),
                // Forms\Components\TextInput::make('msg_type')
                //     ->required(),
                    Forms\Components\Select::make('msg_type')
                    ->options([
                        'reply' => 'reply',
                        // 'image' => 'Image',
                        // 'doc' => 'Document',
                    ])
                    ->required(),
                // Forms\Components\TextInput::make('status')
                //     ->required(),
                    Forms\Components\Toggle::make('status')
                    ->required()
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('msg_type'),
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
            'index' => Pages\ListChatBots::route('/'),
            'create' => Pages\CreateChatBot::route('/create'),
            'edit' => Pages\EditChatBot::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->user()->id;

        return parent::getEloquentQuery()
            ->whereHas('user_device', function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            });
    }
}
