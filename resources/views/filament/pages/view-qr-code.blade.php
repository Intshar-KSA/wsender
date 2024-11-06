<x-filament::page>
    <div class="flex justify-center items-center">
        @if(isset($errorMessage))
        <p>{{ $errorMessage }}</p>
        @else
        <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrCodeImage" />
        @endif
    </div>
    <div class="flex justify-center items-center mt-4">
        <x-filament::button wire:click="closeQrCode">Close</x-filament::button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // تحديث الباركود كل ثلاث ثواني
        setInterval(function() {
            Livewire.emit('refreshQrCode');
        }, 3000);
    });
    </script>
</x-filament::page>