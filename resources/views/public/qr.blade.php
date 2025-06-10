{{-- resources/views/public/qr.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>ربط واتساب – {{ $device?->nickname ?? 'جهاز' }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!-- Tailwind CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.3/dist/tailwind.min.css" rel="stylesheet">
    <!-- Heroicons مصغرة -->
    <script src="https://unpkg.com/heroicons@2.0.18/dist/24/outline.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-300 p-4">

    <div  x-data="qrPage()" x-init="init()"
          class="w-full max-w-sm bg-white/90 backdrop-blur rounded-2xl shadow-2xl p-6 text-center">

        {{-- عنوان --}}
        <template x-if="state === 'qr'">
            <h1 class="text-xl font-bold mb-4">امسح الكود لربط واتساب</h1>
        </template>

        {{-- صورة QR أو رسالة نجاح --}}
        <template x-if="state === 'qr'">
            <img  id="qr-img" :src="qrCodeUrl" class="w-64 h-64 mx-auto rounded border"
                  alt="QR Code" />
        </template>

        <template x-if="state === 'authorized'">
            <div class="flex flex-col items-center gap-4">
                <svg class="w-24 h-24 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5">
                    <use href="#check-circle"></use>
                </svg>
                <h2 class="text-lg font-semibold text-green-700">
                    تم الربط بنجاح!
                </h2>
            </div>
        </template>
        <template x-if="state === 'loading'">
    <div class="flex flex-col items-center gap-4 animate-pulse">
        <svg class="w-24 h-24 text-sky-600" fill="none" stroke="currentColor" stroke-width="1.5">
            <use href="#qr-code"></use>
        </svg>
        <p class="text-sm text-sky-600">جـارٍ إنشاء الكود...</p>
    </div>
</template>


        <template x-if="state === 'error'">
            <p class="text-red-600 font-medium">تعذّر جلب الكود حاليًا.</p>
        </template>

        {{-- تذييل --}}
        <p class="mt-4 text-xs text-gray-500" x-show="state==='qr'">يُحدَّث كل 7 ثوانٍ</p>
    </div>

    <script>
        function qrPage() {
            return {
                state     : '{{ $status }}',    // qr | authorized | error
                qrCodeUrl : @json($qrCodeUrl),
                refreshMs : {{ $refreshMs }},
                refreshUrl: '{{ route('public.qr.refresh', $profile) }}',

             init() {
   if (this.state === 'qr') {
       this.state = 'loading'        // أولاً تحميل
       this.updateQr().then(() => {
           if (this.state === 'loading') this.state = 'qr' // نجح
       })
       setInterval(() => this.updateQr(), this.refreshMs)
   }
},

                async updateQr() {
                    try {
                        const res   = await fetch(this.refreshUrl, { cache:'no-store' })
                        const data  = await res.json()

                        if (data.qrCode) {
                            // إن كان Data-URI ضعه كما هو
                            this.qrCodeUrl = data.qrCode.startsWith('data:')
                                ? data.qrCode
                                : data.qrCode + '&_=' + Date.now()
                        }
                        else if (data.authorized) {
                            this.state = 'authorized'
                        }
                    } catch (_) {
                        /* إبقاء الصورة كما هي */
                    }
                }
            }
        }
    </script>
</body>
</html>
