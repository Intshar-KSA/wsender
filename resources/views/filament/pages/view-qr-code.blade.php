<x-filament::page>
    <div class="flex justify-center items-center" wire:poll.7s="refreshQrCode">
        @if(isset($errorMessage))
        <p>{{ $errorMessage }}</p>
        @else
        <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrCodeImage" />
        @endif
    </div>
    <div class="flex justify-center items-center mt-4">
        <x-filament::button wire:click="closeQrCode">Close</x-filament::button>
        <x-filament::button
    x-data
    x-on:click="
        await navigator.clipboard.writeText('{{ route('public.qr', $qrCodeUrl2) }}');
        $dispatch('notify', { type: 'success', title: 'تم نسخ الرابط!' });
    "
    icon="heroicon-o-clipboard"
    color="primary"
>
    نسخ رابط الربط
</x-filament::button>


    </div>

    <script>
    window.addEventListener('notify', event => {
        alert(event.detail.message); // عرض الرسالة بنجاح
    });
    </script>

</x-filament::page>