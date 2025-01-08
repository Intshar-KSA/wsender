<?php

namespace App\Filament\Resources\QuickSendResource\Pages;

use Filament\Actions;
use App\Services\QuickSendService;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\QuickSendResource;

class CreateQuickSend extends CreateRecord
{
    protected static string $resource = QuickSendResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    // before save
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            // استدعاء الخدمة لإضافة mass_posting_id إلى البيانات
            QuickSendService::createCampaign($data);
            $data["status"] = "started";
        } catch (\Exception $e) {
            // التعامل مع الخطأ إذا فشل الاتصال بـ API
            throw new \Exception('Failed to create campaign: ' . $e->getMessage());
        }

        return $data;
    }


}
