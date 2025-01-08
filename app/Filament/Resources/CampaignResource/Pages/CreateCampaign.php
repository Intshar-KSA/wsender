<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use Filament\Actions;
use App\Services\QuickSendService;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CampaignResource;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     try {
    //         // استدعاء الخدمة لإضافة mass_posting_id إلى البيانات
    //         QuickSendService::createCampaign($data);
    //         $data["status"] = "started";
    //     } catch (\Exception $e) {
    //         // التعامل مع الخطأ إذا فشل الاتصال بـ API
    //         throw new \Exception('Failed to create campaign: ' . $e->getMessage());
    //     }

    //     return $data;
    // }
}
