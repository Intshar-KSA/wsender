<div
     x-data="{ open: false }"
     x-show="open"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
     style="display: none;"
 >
     <div class="bg-white rounded-lg shadow-lg w-1/3 p-6">
         <h2 class="text-lg font-bold mb-4">{{ $title }}</h2>
         <div class="mb-4">
             {{ $slot }}
         </div>
         <div class="flex justify-end space-x-4">
             <button @click="open = false" class="bg-gray-500 text-white px-4 py-2 rounded">Close</button>
             <button @click="{{ $submitAction }}" class="bg-blue-500 text-white px-4 py-2 rounded">Submit</button>
         </div>
     </div>
</div>


public function viewQrCode($qrCodeUrl)
{
    $this->dispatchBrowserEvent('open-modal', [
        'title' => 'QR Code',
        'body' => "<img src='{$qrCodeUrl}' alt='QR Code' />",
    ]);
}
