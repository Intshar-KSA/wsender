<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Device;
use Filament\Forms\Form;
use App\Models\QuickSend;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\QuickSendService;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\NumberInput;

use App\Filament\Resources\QuickSendResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\QuickSendResource\RelationManagers;
use App\helper\ModelLabelHelper;

class QuickSendResource extends Resource
{
    protected static ?string $model = QuickSend::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('profile_id')
                ->options(Device::all()->pluck('nickname', 'profile_id'))
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Choose a device to send the message.'),

            Textarea::make('message_text')
                ->required()
                ->rows(3)
                ->helperText('Enter the message content to send.'),

            Textarea::make('phone_numbers')
                ->required()
                ->rows(3)
                ->helperText('Enter phone numbers, one per line.'),

            FileUpload::make('image')
                ->image()
                ->directory('uploads/images')
                ->visibility('public')
                ->helperText('Optional: Attach an image to your message.'),

            TextInput::make('timeout_from')
                ->numeric()
                ->default(5)
                ->required()
                ->helperText('Minimum time delay for sending messages.'),

            TextInput::make('timeout_to')
                ->numeric()
                ->default(8)
                ->required()
                ->helperText('Maximum time delay for sending messages.'),

            TextInput::make('file_name')
                ->default('')
                ->hidden()
                ->required()
                ->helperText('Name of the uploaded file (default: картинка).'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('profile_id')
                ->sortable()
                ->searchable(),

            TextColumn::make('message_text')
                ->limit(30),

            TextColumn::make('phone_numbers')
                ->limit(30)
                ->copyable(),

            BadgeColumn::make('status')
                ->colors([
                    'success' => 'started',
                    'warning' => 'paused',
                    'danger' => 'failed',
                    'gray' => 'created',
                ])
                ->sortable(),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),

            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable(),
        ])->actions([
            Action::make('toggle_status')
                ->icon(fn ($record) => match ($record->status) {
                    'created' => 'heroicon-o-play',
                    'started' => 'heroicon-o-pause',
                    'paused' => 'heroicon-o-play',
                    'resumed' => 'heroicon-o-pause',
                    default => 'heroicon-o-question-mark-circle',
                })
                ->action(function ($record) {
                    try {
                        match ($record->status) {
                            'created' => QuickSendService::startCampaign($record),
                            'started' => QuickSendService::pauseCampaign($record),
                            'paused' => QuickSendService::resumeCampaign($record),
                            'resumed' => QuickSendService::pauseCampaign($record),
                            default => throw new \Exception("Unhandled status: {$record->status}"),
                        };
                    } catch (\Exception $e) {
                        \Log::error("Error in campaign toggle: " . $e->getMessage());
                        throw $e;
                    }
                })
                ->requiresConfirmation()
                ->successNotificationTitle(fn ($record) => match ($record->status) {
                    'created' => 'Campaign started successfully.',
                    'started' => 'Campaign paused successfully.',
                    'paused' => 'Campaign resumed successfully.',
                }),

            Action::make('delete')
                ->icon('heroicon-o-trash')
                ->action(fn ($record) => $record->delete())
                ->color('danger')
                ->requiresConfirmation()
                ->successNotificationTitle('Campaign deleted successfully.'),
        ])->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListQuickSends::route('/'),
            'create' => Pages\CreateQuickSend::route('/create'),
            'edit' => Pages\EditQuickSend::route('/{record}/edit'),
        ];
    }

    public static function afterCreate($record)
    {
        QuickSendService::createCampaign($record->toArray());
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
