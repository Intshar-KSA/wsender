<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Status;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('caption')
                    ->label('Caption')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('devices')
                    ->label('Devices')
                    ->multiple()
                    ->relationship('devices', 'nickname') // اسم العلاقة وعمود العرض
                    ->preload()
                    ->searchable()
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->nullable(), // ✅ اجعل الحقل اختياري

                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->nullable(), // ✅ اجعل الحقل اختياري

                Forms\Components\TimePicker::make('time')
                    ->label('Execution Time')
                    ->required(),
                Forms\Components\FileUpload::make('file_url')
                    ->label('Upload Image/Video')
                    ->directory('statuses')
                    ->disk('public')
                    ->acceptedFileTypes(['image/*', 'video/*'])
                    ->maxSize(10240) // 10MB
                    ->downloadable()
                    ->openable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active Status')
                    ->default(true)
                    ->inline(false),

                Forms\Components\DateTimePicker::make('last_run_at')
                    ->label('Last Run At')
                    ->disabled()
                    ->default(null),

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Caption')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('time')
                    ->label('Execution Time')
                    ->time()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_run_at')
                    ->label('Last Run At')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->last_run_at ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('devices_list')
                    ->label('Devices')
                    ->getStateUsing(function ($record) {
                        return $record->devices->pluck('nickname')->implode(', ');
                    })
                    ->wrap()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->isActive() ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => fn ($state) => $state === 'Active',
                        'danger' => fn ($state) => $state === 'Inactive',
                    ]),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Is Active')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('file_url')
                    ->label('File')
                    ->square()
                    ->disk('public') // إذا كنت تستخدم التخزين العام
                    ->hidden(fn ($record) => empty($record->file_url)),

                Tables\Columns\TextColumn::make('file_url')
                    ->label('File URL')
                    ->url(fn ($record) => $record->file_url, true)
                    ->hidden(fn ($record) => empty($record->file_url)),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_status')
                    ->label('Active Status')
                    ->query(fn ($query) => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // reset last run at
                Tables\Actions\Action::make('resetLastRun')
                    ->label('Reset Run')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['last_run_at' => null]);
                        \Filament\Notifications\Notification::make()
                            ->title('تم تصفير وقت التنفيذ')
                            ->success()
                            ->send();
                    }),

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
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
