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
    x-data="{
        link: @js(route('public.qr', ['profile' => $qrCodeUrl2])),
        async copy () {
            /* 1) حاول واجهة Clipboard الحديثة */
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(this.link)
                    this.notify()
                    return
                } catch (e) {
                    /* ستنتقل إلى الاحتياطي بالأسفل */
                }
            }

            /* 2) fallback قديم – يعمل حتى على iOS Safari */
            const el = document.createElement('textarea')
            el.value = this.link
            el.style.position = 'fixed'   // تجنّب انزياح الصفحة
            el.style.opacity = '0'
            document.body.appendChild(el)
            el.select()
            try { document.execCommand('copy'); this.notify() } catch (_) {}
            document.body.removeChild(el)
        },
        notify () {
            window.filament?.notifications?.push({
                title   : 'تم نسخ الرابط بنجاح',
                body    : this.link,
                icon    : 'heroicon-o-clipboard',
                color   : 'success',
                duration: 2500,
            })
        }
    }"
    x-on:click.prevent.stop="copy"
    icon="heroicon-o-clipboard"
    color="success"
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