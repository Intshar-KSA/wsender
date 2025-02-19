<?php

namespace App\Filament\Resources\ContactResource\Pages;


use App\Models\Contact;
use App\Models\ContactCat;
use Filament\Forms\Components;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use App\Filament\Resources\ContactResource;
use App\helper\ModelLabelHelper;
use Illuminate\Contracts\Support\Htmlable;

class BulkCreateContacts extends Page
{
    protected static string $resource = ContactResource::class; // تحديد المورد

    protected static string $view = 'filament.resources.contact-resource.pages.bulk-create-contacts';

    public $data = [
        'contact_cat_id' => null,
        'numbers' => '',
    ];



    public function saveContacts()
    {
        // فصل النص المدخل إلى أسطر بناءً على السطر الجديد
    $numbers = preg_split('/\r\n|\r|\n/', $this->data['numbers']);

    // تنظيف الأرقام من المسافات أو القيم الفارغة
    $validNumbers = array_filter(array_map('trim', $numbers), function ($number) {
        return !empty($number) && preg_match('/^\d+$/', $number); // التحقق من أن الرقم يحتوي فقط على أرقام
    });

    \Log::info('Raw Numbers Input:', ['numbers' => $this->data['numbers']]);
\Log::info('Processed Numbers:', ['validNumbers' => $validNumbers]);


        if (empty($validNumbers)) {
            Notification::make()
                ->title('No valid numbers were provided!')
                ->danger()
                ->send();

            return;
        }

        if (!$this->data['contact_cat_id']) {
            Notification::make()
                ->title('Please select a category!')
                ->danger()
                ->send();

            return;
        }
// dd($validNumbers);
        foreach ($validNumbers as $number) {
            Contact::create([
                'user_id' => auth()->id(),
                'contact_cat_id' => $this->data['contact_cat_id'],
                'name' => 'Unknown', // تخصيص الاسم إذا كان مطلوبًا
                'phone' => $number,
            ]);
        }

        // إرسال إشعار النجاح
        Notification::make()
            ->title('Contacts have been added successfully!')
            ->success()
            ->send();

         return redirect(static::getResource()::getUrl('index'));;
    }





    protected function getFormSchema(): array
    {
        return [
            Components\Select::make('data.contact_cat_id') // لاحظ الربط بـ 'data.contact_cat_id'
                ->options(ContactCat::where('user_id', auth()->id())->pluck('name', 'id'))
                ->required(),

            Components\Textarea::make('data.numbers') // لاحظ الربط بـ 'data.numbers'
                ->placeholder("1234567890\n0987654321")
                ->rows(10)
                ->required(),
        ];
    }
    public function getTitle(): string | Htmlable
    {

        return ModelLabelHelper::getModelLabel(static::class);
    }

}
