<x-filament::page>
    @if (empty($this->results))
        <div class="text-center text-gray-500">لا توجد نتائج لعرضها.</div>
    @else
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 mb-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-right">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-medium text-gray-700">رقم المستلم</th>
                        <th class="px-4 py-2 font-medium text-gray-700">الحالة</th>
                        <th class="px-4 py-2 font-medium text-gray-700">العنوان</th>
                        <th class="px-4 py-2 font-medium text-gray-700">الرسالة</th>
                        <th class="px-4 py-2 font-medium text-gray-700">وقت الإرسال</th>
                        <th class="px-4 py-2 font-medium text-gray-700">نوع المهمة</th>
                        <th class="px-4 py-2 font-medium text-gray-700">معرّف المهمة</th>
                        <th class="px-4 py-2 font-medium text-gray-700">اسم الجهاز</th>
                        <th class="px-4 py-2 font-medium text-gray-700">المنصة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($this->results as $item)
                        <tr>
                            <td class="px-4 py-2">{{ $item['recipient'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-red-600 font-bold">{{ $item['delivered'] ?? 'لا شيء' }}</td>






                            <td class="px-4 py-2">{{ $item['caption'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $item['message'] ?? '-' }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($item['created_at'] ?? now())->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-2">{{ $item['task_name'] ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $item['task_id'] ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $item['profile']['name'] ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $item['profile']['platform'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <x-filament::button wire:click="loadMore" color="primary">
                عرض المزيد
            </x-filament::button>
        </div>
    @endif
</x-filament::page>
