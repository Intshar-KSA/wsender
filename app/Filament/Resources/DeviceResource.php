<?php

namespace App\Filament\Resources;

// use livewire ;
use Filament\Forms;
use Filament\Tables;
use App\Models\Device;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use App\Services\ExternalApiService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Livewire; // استيراد النوع الصحيح
use App\Filament\Resources\DeviceResource\Pages;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = -2;

    public static Collection $profiles;// لتخزين البيانات المحملة

    public static function loadProfiles(): void
    {
        if (empty(self::$profiles)) {
            $apiService = app(\App\Services\ExternalApiService::class);
            self::$profiles = collect($apiService->getProfiles());
        }
    }

    // public static function loadProfiles(): void
    // {
    //     // تحقق إذا كانت البيانات قد تم تحميلها بالفعل
    //     if (empty(self::$profiles)) {
    //         // إنشاء مفتاح كاش خاص
    //         $cacheKey = 'profiles_cache';

    //         // جلب البيانات من الكاش إذا كانت موجودة
    //         self::$profiles = cache()->remember($cacheKey, now()->addMinutes(5), function () {
    //             $apiService = app(\App\Services\ExternalApiService::class);
    //             return collect($apiService->getProfiles());
    //         });
    //     }
    // }

    public static function getProfiles(): array
    {
        // إرجاع البيانات المحملة
        return self::$profiles ?? [];
    }

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
                    ->hidden()
                    ->default(false) // افتراضيًا غير نشط
                    ->inline(false)
                    ->disabled(true),

            ]);
    }

    public static function table(Table $table): Table
    {
        self::loadProfiles();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nickname')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('profile_id')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('webhook_url')
                    ->searchable(),
                // Tables\Columns\BooleanColumn::make('status')
                //     ->label('Active')
                //     ->sortable(),
                    // Tables\Columns\TextColumn::make('extra_data.name')
                    // ->label('Profile Name')
                    // ->getStateUsing(function (Device $record) {
                    //     $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                    //     return $profile['name'] ?? 'N/A';
                    // }),
                    Tables\Columns\TextColumn::make('remaining_time')
                    ->label('Time Remaining')
                    ->badge()
                    ->getStateUsing(function (Device $record) {
                        // الحصول على الاشتراك النشط
                        $activeSubscription = $record->subscriptions()
                            ->where('start_date', '<=', now()) // الاشتراك بدأ
                            ->latest('start_date') // أحدث اشتراك
                            ->first();

                        if ($activeSubscription) {
                            // حساب تاريخ انتهاء الاشتراك باستخدام الدالة
                            $expirationDate = $activeSubscription->getExpirationDate();

                            if ($expirationDate && now()->lessThan($expirationDate)) {
                                $remainingDays = now()->diffInDays($expirationDate);

                                return $remainingDays > 0
                                    ? (round($remainingDays)) . ' days remaining'
                                    : 'Less than a day remaining';
                            }

                            return 'Expired'; // الاشتراك منتهي
                        }

                        return 'No Active Subscription'; // لا يوجد اشتراك نشط
                    })
                    ->sortable()
                    ->colors([
                        'danger' => fn ($state) => $state === 'Expired' || $state === 'No Active Subscription',
                        'warning' => fn ($state) => is_numeric($state) && $state <= 5, // أقل من 5 أيام
                        'success' => fn ($state) => is_numeric($state) && $state > 5,  // أكثر من 5 أيام
                    ]),
                Tables\Columns\TextColumn::make('extra_data.phone')
                    ->label('Phone Number')
                    ->getStateUsing(function (Device $record) {
                        $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                        return $profile['phone'] ?? 'N/A';
                    }),
                Tables\Columns\TextColumn::make('extra_data.app_status')
                    ->label('App Status')
                    ->getStateUsing(function (Device $record) {
                        $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                        return $profile['app_status'] ?? 'Unknown';
                    }),
                Tables\Columns\TextColumn::make('extra_data.worked_days')
                    ->label('Worked Days')
                    ->getStateUsing(function (Device $record) {
                        $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                        return $profile['worked_days'] ?? 0;
                    }),
                Tables\Columns\TextColumn::make('extra_data.message_count')
                    ->label('Message Count')
                    ->getStateUsing(function (Device $record) {
                        $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                        return $profile['message_count'] ?? 0;
                    }),
                Tables\Columns\BooleanColumn::make('extra_data.authorized')
                    ->label('Authorized')
                    ->getStateUsing(function (Device $record) {
                        $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                        return $profile['authorized'] ?? false;
                    }),
                Tables\Columns\TextColumn::make('extra_data.webhook_url')
                    ->label('Webhook URL')
                    ->getStateUsing(function (Device $record) {
                        $profile = self::$profiles->firstWhere('profile_id', $record->profile_id);
                        return $profile['webhook_url'] ?? 'N/A';
                    }),

                    ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('subscriptions.plan_id')
                    ->label('Plan')
                    ->relationship('subscriptions', 'plan_id')
                    ->options(fn () => \App\Models\Plan::pluck('title', 'id')->toArray()),
                // Tables\Filters\SelectFilter::make('subscriptions.plan_id')
                // ->label('Plan')
                // ->options(function () {
                //     return \App\Models\Subscription::whereHas('device', function ($query) {
                //         $query->where('user_id', auth()->id());
                //     })->get()
                //     ->pluck('plan.title', 'plan.id')
                //     ->toArray();
                // })

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
                    ->action(function ($record, $livewire) {
                        // التحقق من حالة الاشتراك
                        $activeSubscription = $record->subscriptions()
                            ->where('start_date', '<=', now())
                            ->latest('start_date')
                            ->first();

                        if ($activeSubscription) {
                            $expirationDate = $activeSubscription->getExpirationDate();

                            if (!$expirationDate || now()->greaterThanOrEqualTo($expirationDate)) {
                                // الاشتراك منتهي
                                Notification::make()
                                    ->title('انتهاء الاشتراك')
                                    ->body('انتهى اشتراك الجهاز. يُرجى الاشتراك من جديد.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                        } else {
                            // لا يوجد اشتراك نشط
                            Notification::make()
                                ->title('لا يوجد اشتراك نشط')
                                ->body('هذا الجهاز ليس لديه اشتراك نشط. يُرجى الاشتراك لتتمكن من عرض QR Code.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // إذا كان الاشتراك صالحًا، عرض رمز QR
                        $qrCodeText = $record->profile_id;
                        $encodedUrl = urlencode($qrCodeText);

                        return redirect()->route('filament.pages.view-qr-code', ['qrCodeText' => $encodedUrl]);
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery()->where('user_id', auth()->id());

    //     // استدعاء API لتحميل البيانات
    //     $apiService = app(\App\Services\ExternalApiService::class);
    //     $profiles = collect($apiService->getProfiles());

    //     // إضافة البيانات الإضافية إلى الأجهزة
    //     $query->get()->each(function ($device) use ($profiles) {
    //         $profile = $profiles->firstWhere('profile_id', $device->profile_id);
    //         $device->extra_data = $profile ?? []; // إضافة البيانات أو قيم افتراضية فارغة
    //     });

    //     return $query;
    // }


}
