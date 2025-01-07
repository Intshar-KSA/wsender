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

class QuickSendResource extends Resource
{
    protected static ?string $model = QuickSend::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('profile_id')
                ->label('Select Device')
                ->options(Device::all()->pluck('nickname', 'profile_id'))
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Choose a device to send the message.'),

            Textarea::make('message_text')
                ->label('Message Text')
                ->required()
                ->rows(3)
                ->helperText('Enter the message content to send.'),

            Textarea::make('phone_numbers')
                ->label('Phone Numbers')
                ->required()
                ->rows(3)
                ->helperText('Enter phone numbers, one per line.'),

            FileUpload::make('image')
                ->label('Upload Image')
                ->image()
                ->directory('uploads/images')
                ->visibility('private')
                ->helperText('Optional: Attach an image to your message.'),

            TextInput::make('timeout_from')
                ->numeric()
                ->default(5)
                ->required()
                ->label('Timeout From (seconds)')
                ->helperText('Minimum time delay for sending messages.'),

            TextInput::make('timeout_to')
                ->numeric()
                ->default(8)
                ->required()
                ->label('Timeout To (seconds)')
                ->helperText('Maximum time delay for sending messages.'),

            TextInput::make('file_name')
                ->default('картинка')
                ->label('File Name')
                ->required()
                ->helperText('Name of the uploaded file (default: картинка).'),
        ]);
    }


public static function table(Table $table): Table
{
    return  $table->columns([
        TextColumn::make('profile_id')
            ->label('Profile ID')
            ->sortable()
            ->searchable(),

        TextColumn::make('message_text')
            ->label('Message')
            ->limit(30),

        TextColumn::make('phone_numbers')
            ->label('Phones')
            ->limit(30),

        BadgeColumn::make('status')
            ->label('Status')
            ->colors([
                'success' => 'resumed',
                'warning' => 'paused',
                'danger' => 'failed',
                'gray' => 'created',
            ])
            ->sortable(),

        TextColumn::make('created_at')
            ->label('Created At')
            ->dateTime()
            ->sortable(),

        TextColumn::make('updated_at')
            ->label('Updated At')
            ->dateTime()
            ->sortable(),
    ])->actions([
        Action::make('pause')
        ->label('Pause')
        ->icon('heroicon-o-pause')
        ->action(fn ($record) => QuickSendService::pauseCampaign($record))
        ->requiresConfirmation()
        ->successNotificationTitle('Campaign paused successfully.'),

    Action::make('resume')
        ->label('Resume')
        ->icon('heroicon-o-play')
        ->action(fn ($record) => QuickSendService::resumeCampaign($record))
        ->requiresConfirmation()
        ->successNotificationTitle('Campaign resumed successfully.'),

    Action::make('delete')
        ->label('Delete')
        ->icon('heroicon-o-trash')
        ->action(fn ($record) => $record->delete())
        ->color('danger')
        ->requiresConfirmation()
        ->successNotificationTitle('Campaign deleted successfully.'),
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
}
