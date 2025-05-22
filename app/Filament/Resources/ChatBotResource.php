<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatBotResource\Pages;
use App\helper\ModelLabelHelper;
use App\Models\ChatBot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatBotResource extends Resource
{
    protected static ?string $model = ChatBot::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('device_id')
                    ->relationship('user_device', 'nickname', function (Builder $query) {
                        $query->where('user_id', auth()->id()); // تصفية الأجهزة لتكون خاصة بالمستخدم
                    })
                    ->required(),

                Forms\Components\Textarea::make('msg')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('content_id')
                    ->relationship('content', 'title', function (Builder $query) {
                        $query->where('user_id', auth()->id()); // تصفية المحتويات لتكون خاصة بالمستخدم
                    })
                    ->required(),

                Forms\Components\Select::make('type')
                    ->options([
                        'exact' => 'Exact',
                        'contains' => 'Contains',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_greeting')
                    ->label('رسالة ترحيبية؟')
                    ->helperText('فعّل هذا إذا كانت هذه الرسالة ترحيبية للجهاز')
                    ->onColor('success')
                    ->offColor('secondary'),

                Forms\Components\Toggle::make('status')
                    ->required()
                    ->onColor('success')
                    ->offColor('danger'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_device.nickname')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('msg')
                    // ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('content.title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_greeting')
                    ->label('ترحيبية؟')
                    // ->onIcon('heroicon-o-badge-check')
                    // ->offIcon('heroicon-o-badge-x')
                    ->onColor('success')
                    ->offColor('gray')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->action(function (ChatBot $record, $state): void {
                        $record->update(['status' => $state]);
                    })
                    ->sortable(),
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
                    ->options(function () {
                        return \App\Models\Device::where('user_id', auth()->id())
                            ->pluck('nickname', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('content_id')
                    ->options(function () {
                        return \App\Models\Content::where('user_id', auth()->id())
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('type')
                    ->options([
                        'exact' => 'Exact',
                        'contains' => 'Contains',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'on' => 'On',
                        'off' => 'Off',
                    ]),
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

    public static function getModelLabel(): string
    {
        return ModelLabelHelper::getModelLabel(static::$model);
    }

    public static function getPluralModelLabel(): string
    {
        return ModelLabelHelper::getPluralModelLabel(static::$model);
    }
}
