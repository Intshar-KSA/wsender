<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\helper\ModelLabelHelper;
use App\Models\Campaign;
use App\Models\ContactCat;
use App\Services\CampaignService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('device_id')
                    ->relationship('user_device', 'nickname')
                    ->required(),
                // Forms\Components\TextInput::make('mass_prsting_id')
                //     ->numeric()
                //     ->nullable(),
                Forms\Components\Select::make('content_id')
                    ->relationship('content', 'title')
                    ->required(),
                Forms\Components\Select::make('contact_cat_ids')
                    ->multiple()
                    ->options(ContactCat::where('user_id', auth()->id())->pluck('name', 'id')->toArray())
                    ->required(),

                Forms\Components\TextInput::make('message_every')
                    ->required()
                    ->numeric(),
                Forms\Components\TimePicker::make('starting_time'),
                Forms\Components\TimePicker::make('allowed_period_from'),
                Forms\Components\TimePicker::make('allowed_period_to'),
                // Forms\Components\Toggle::make('status')
                //     ->label('Status')
                //     ->onColor('success')
                //     ->offColor('danger')
                //     ->default(false)
                //     ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_device.nickname')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mass_prsting_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content.title')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('contactCats.name')
                // ->label('Contact Groups')
                // ->formatStateUsing(fn ($state) => implode(', ', $state)) // عرض المجموعات
                // ->sortable(),
                Tables\Columns\TextColumn::make('contact_cat_ids')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return implode(', ', $state); // إذا كانت البيانات مصفوفة
                        }

                        $decoded = json_decode($state, true);

                        return is_array($decoded) ? implode(', ', $decoded) : 'N/A'; // إذا كانت JSON
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('message_every')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starting_time'),
                Tables\Columns\TextColumn::make('allowed_period_from'),
                Tables\Columns\TextColumn::make('allowed_period_to'),
                // Tables\Columns\BadgeColumn::make('status')
                //     ->colors([
                //         'success' => fn ($state): bool => $state === 'on',
                //         'danger' => fn ($state): bool => $state === 'off',
                //     ]),
                // Tables\Columns\ToggleColumn::make('status')
                //     ->label('Status')
                //     ->onColor('success')
                //     ->offColor('danger')
                //     ->onIcon('heroicon-o-check-circle')
                //     ->offIcon('heroicon-o-x-circle')
                //     ->action(function (Campaign $record, $state): void {
                //         // تحديث الحالة مباشرة
                //         $record->update(['status' => $state ? 'on' : 'off']);
                //     })
                //     ->sortable()
                //     ->tooltip('Click to toggle status'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'started',
                        'warning' => 'paused',
                        'danger' => 'failed',
                        'gray' => 'created',
                    ])
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

                // Tables\Filters\SelectFilter::make('device_id')
                // ->label('Device')
                // ->options(function () {
                //     return \App\Models\Device::where('user_id', auth()->id())
                //         ->pluck('nickname', 'id')
                //         ->toArray();
                // })
                // ->query(function (Builder $query, $state) {
                //     if ($state) {
                //         $query->where('device_id', $state);
                //     }
                // }),

                // Tables\Filters\SelectFilter::make('content_id')
                //     ->label('Content')
                //     ->options(function () {
                //         return \App\Models\Content::where('user_id', auth()->id())
                //             ->pluck('title', 'id')
                //             ->toArray();
                //     })
                //     ->query(function (Builder $query, $state) {
                //         if ($state) {
                //             $query->where('content_id', $state);
                //         }
                //     }),

                // Tables\Filters\SelectFilter::make('status')
                //     ->options([
                //         1 => 'On',  // 1 تمثل القيمة المنطقية true
                //         0 => 'Off', // 0 تمثل القيمة المنطقية false
                //     ])
                //     ->query(function (Builder $query, $state) {
                //         if ($state !== null) {
                //             $query->where('status', $state);
                //         }
                //     }),
            ])

            ->actions([
                Action::make('toggle_status')
                    ->label(fn ($record) => match ($record->status) {
                        'created' => 'Start',
                        'started' => 'Pause',
                        'paused' => 'Resume',
                        'resumed' => 'Pause',
                        default => 'Unknown', // للعرض الافتراضي
                    })
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
                                'created' => CampaignService::createCampaignFromCampaignsTable($record),
                                'started' => CampaignService::pauseCampaign($record),
                                'paused' => CampaignService::resumeCampaign($record),
                                'resumed' => CampaignService::pauseCampaign($record),
                                default => throw new \Exception("Unhandled status: {$record->status}"),
                            };
                        } catch (\Exception $e) {
                            \Log::error('Error in campaign toggle: '.$e->getMessage());
                            throw $e; // إعادة رمي الاستثناء للتعامل معه في الواجهة
                        }
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle(fn ($record) => match ($record->status) {
                        'created' => 'Campaign started successfully.',
                        'started' => 'Campaign paused successfully.',
                        'paused' => 'Campaign resumed successfully.',
                    }),
                Action::make('delete')
                    ->icon(fn ($record) => in_array($record->status, ['started', 'resumed'])
                        ? 'heroicon-o-information-circle'
                        : 'heroicon-o-trash')
                    ->color('danger')
                    ->label('Delete')
                    ->requiresConfirmation()
                    ->extraAttributes(fn ($record) => [
                        'title' => in_array($record->status, ['started', 'resumed'])
                            ? __('❗ You must pause or stop the campaign before deleting.')
                            : __('Delete this campaign'),
                    ])
                    ->action(function ($record) {
                        if (in_array($record->status, ['started', 'resumed'])) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Cannot delete an active campaign'))
                                ->body(__('Please pause or stop the campaign first.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title(__('Campaign deleted successfully.'))
                            ->success()
                            ->send();
                    }),

                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        \Log::info('Fetching campaigns for user_id: '.auth()->id());

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
