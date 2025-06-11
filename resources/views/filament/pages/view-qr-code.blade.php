<x-filament::page>
    <div class="flex justify-center items-center" wire:poll.7s="refreshQrCode">
        @isset($errorMessage)
            <p>{{ $errorMessage }}</p>
        @else
            <img src="{{ $qrCodeUrl }}" alt="QR Code" id="qrCodeImage">
        @endisset
    </div>

    <div class="flex justify-center items-center mt-4 gap-4">
    

        {{-- زر نسخ الرابط --}}
        <x-filament::button
            color="success"
            icon="heroicon-o-clipboard"
            onclick="copyQrLink('{{ route('public.qr', ['profile' => $qrCodeUrl2]) }}')"
        >
            نسخ رابط الربط
        </x-filament::button>
            <x-filament::button wire:click="closeQrCode" >
            إغلاق
        </x-filament::button>
    </div>
</x-filament::page>

{{-- سكربت عالمي صغير --}}
<script>
function copyQrLink(link) {
    // Clipboard API أولاً
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(link)
            .then(() => toast(link))
            .catch(() => fallback(link));
    } else {
        fallback(link);
    }

    function fallback(text) {         // متوافق مع Safari iOS
        const t = document.createElement('textarea');
        t.value = text;
        t.style.position = 'fixed';
        t.style.opacity  = '0';
        document.body.appendChild(t);
        t.select();
        try { document.execCommand('copy'); } catch(_) {}
        document.body.removeChild(t);
        toast(text);
    }

    function toast(text) {
        window.filament?.notifications?.push({
            title   : 'تم نسخ الرابط',
            body    : text,
            icon    : 'heroicon-o-clipboard',
            color   : 'success',
            duration: 2500,
        });
    }
}
</script>
