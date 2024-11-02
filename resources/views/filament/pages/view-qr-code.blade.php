<x-filament::page>
    <div class="flex justify-center items-center">
        @if(isset($errorMessage))
        <p>{{ $errorMessage }}</p>
        @else
        <img src="{{ $qrCodeUrl }}" alt="QR Code" />
        @endif
    </div>
    <div class="flex justify-center items-center mt-4">
        <x-filament::button wire:click="closeQrCode">Close</x-filament::button>
    </div>
</x-filament::page>