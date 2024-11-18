<?php

namespace App\Filament\Resources;

// use livewire ;
use Filament\Forms;
use Filament\Tables;
use App\Models\Device;
use Livewire\Livewire;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Services\ExternalApiService;
use Filament\Notifications\Notification;
use App\Filament\Resources\DeviceResource\Pages;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nickname')
                    ->required()
                    ->maxLength(255),
                // Forms\Components\Textarea::make('profile_id')
                //     ->required()
                //     ->columnSpanFull(),
                Forms\Components\TextInput::make('webhook_url')
                    ->maxLength(255)
                    ->default(null),
                    Forms\Components\Toggle::make('status')
                    ->label('Active Status')
                    ->default(false) // افتراضيًا غير نشط
                    ->inline(false),
    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nickname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('webhook_url')
                    ->searchable(),
                    Tables\Columns\BooleanColumn::make('status')
                    ->label('Active')
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
                \Filament\Tables\Filters\SelectFilter::make('subscriptions.plan_id')
                ->label('Plan')
                ->relationship('subscriptions', 'plan_id')
                ->options(fn () => \App\Models\Plan::pluck('title', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('deleteViaApi')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-trash')
                    ->action(function ($record, $data) {
                        $apiService = app(ExternalApiService::class);

                        try {
                            // dd($record->profile_id);
                            // استدعاء دالة الحذف من الـ API
                            $response = $apiService->deleteProfile($record->profile_id);

                            // التحقق من استجابة الـ API
                            if (! ($response['status'] == 'done')) {
                                throw new \Exception('Failed to delete profile via API. Response: '.json_encode($response));
                            }

                            // حذف السجل من قاعدة البيانات إذا كان استدعاء الـ API ناجحًا
                            $record->delete();

                        } catch (\Exception $e) {
                            // التعامل مع الخطأ: تسجيله وعرض رسالة للمستخدم
                            logger()->error($e->getMessage());
                            Notification::make()
                                ->title('حدث خطأ أثناء عملية الحذف.')
                                ->danger()
                                ->send();
                            // $this->notify('danger', 'حدث خطأ أثناء عملية الحذف.');
                        }
                    }),
                    Action::make('viewQrCode')
                    ->label('عرض QR Code')
                    // ->icon('heroicon-o-view-list')
                    ->action(function ($record,$livewire) {
                        $apiService = app(ExternalApiService::class);
                        $qrCodeText = $record->profile_id;

                        // تكوين رابط الـ QR Code كصورة
                        // $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeText);

                        $encodedUrl = urlencode($qrCodeText);
                        return redirect()->route('filament.pages.view-qr-code', ['qrCodeText' => $encodedUrl]);


                    try {
                        // استدعاء دالة الحصول على QR Code
                        // $response = $apiService->getQrCode($record->profile_id);

                        // // التحقق من استجابة الـ API
                        // if (!$response['success']) {
                        //     throw new \Exception('Failed to fetch QR Code. Response: ' . json_encode($response));
                        // }

                        // $qrCodeText = $response['qr_code_text'];

                        // عرض QR Code في نافذة Modal
                        // Livewire::emitTo('qr-code-modal', 'openQrCodeModal', $qrCodeUrl);
                        // $this->dispatchBrowserEvent('open-qr-code-modal', ['qrCodeUrl' => $qrCodeUrl]);
                        // app('livewire')->dispatchBrowserEvent('open-qr-code-modal', ['qrCodeUrl' => $qrCodeUrl]);
                        $script = <<<JS
                        window.livewire.emit('openQrCodeModal', '$qrCodeUrl');
                    JS;

                     redirect()->route('filament.pages.view-qr-code', ['qrCodeUrl' => $qrCodeUrl]);

                    // $livewire->emit('viewQrCode', $qrCodeUrl);
                    // $dispatch('open-modal', { id: 'edit-user' });
                    // $livewire = Livewire::with($dispatch('open-modal', { id: 'edit-user' }));
                    // $livewire->dispatchBrowserEvent('open-qr-code-modal', ['qrCodeUrl' => $qrCodeUrl]);
                    // $livewire->dispatchBrowserEvent('open-modal', [
                    //     'title' => 'QR Code',
                    //     'body' => "<img src='{$qrCodeUrl}' alt='QR Code' />",
                    // ]);

                    // إضافة السكربت إلى المحتوى
                    // app('livewire')->dispatchBrowserEvent('open-qr-code-modal', ['qrCodeUrl' => $qrCodeUrl]);
                    // $livewire->dispatchBrowserEvent('open-modal', ['id' => $record->id]);


                    } catch (\Exception $e) {
                        // التعامل مع الخطأ: تسجيله وعرض رسالة للمستخدم
                        logger()->error($e->getMessage());
                        Notification::make()
                            ->title('Error')
                            ->body('حدث خطأ أثناء جلب QR Code.'.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                    }),
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
            \App\Filament\Resources\SubscriptionResource\RelationManagers\SubscriptionsRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }

    public static function beforeDelete($record): void
    {
        $apiService = app(ExternalApiService::class);

        try {
            dd($record);
            // استدعاء دالة الحذف من الـ API
            $response = $apiService->deleteProfile($record->profile_id);

            // التحقق من استجابة الـ API
            if (! $response['success']) {
                throw new \Exception('Failed to delete profile via API. Response: '.json_encode($response));
            }

        } catch (\Exception $e) {
            // التعامل مع الخطأ: تسجيله ومنع الحذف في قاعدة البيانات إذا كان هناك مشكلة
            logger()->error($e->getMessage());

            // منع الحذف عن طريق طرح استثناء
            throw new \Exception('Could not delete the record due to API error.');
        }
    }
}