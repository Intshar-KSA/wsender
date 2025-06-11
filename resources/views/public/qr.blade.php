<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>ربط واتساب – {{ $device?->nickname ?? 'جهاز' }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.3/dist/tailwind.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-300 p-4">

    <div x-data="qrPage()" x-init="init()"
         class="w-full max-w-sm bg-white/90 backdrop-blur rounded-2xl shadow-2xl p-6 text-center">

        <!-- العنوان -->
        <template x-if="state === 'qr'">
            <h1 class="text-xl font-bold mb-4">امسح الكود لربط واتساب</h1>
        </template>

        <!-- تحميل -->
        <template x-if="state === 'loading'">
            <div class="flex flex-col items-center gap-4 animate-pulse">
                <svg class="w-24 h-24 text-sky-600" viewBox="0 0 24 24" fill="none" stroke-width="1.5"
                     stroke="currentColor"><path d="M12 2v4m6.364 1.636-2.828 2.828M22 12h-4m-1.636 6.364-2.828-2.828M12 22v-4m-6.364-1.636 2.828-2.828M2 12h4m1.636-6.364 2.828 2.828"/></svg>
                <p class="text-sm text-sky-600">جـارٍ إنشاء الكود...</p>
            </div>
        </template>

        <!-- QR -->
        <template x-if="state === 'qr'">
            <img :src="qrCodeUrl" class="w-64 h-64 mx-auto rounded border" alt="QR Code">
        </template>

        <!-- نجاح -->
        <template x-if="state === 'authorized'">
            <div class="flex flex-col items-center gap-4">
                <svg class="w-24 h-24 text-green-600" viewBox="0 0 24 24" fill="none" stroke-width="1.5"
                     stroke="currentColor"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                <h2 class="text-lg font-semibold text-green-700">تم الربط بنجاح!</h2>
            </div>
        </template>

        <!-- الملف غير موجود -->
        <template x-if="state === 'not_found'">
            <div class="flex flex-col items-center gap-4">
                <svg class="w-20 h-20 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5"
                     stroke="currentColor"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-lg font-semibold text-yellow-700">
                    الملف غير موجود<br>تواصل مع المسؤول.
                </h2>
            </div>
        </template>

        <!-- خطأ عام -->
        <template x-if="state === 'error'">
            <p class="text-red-600 font-medium">تعذّر الحصول على الكود حاليًا. حاول لاحقًا.</p>
        </template>

        <!-- تذييل التحديث -->
        <p class="mt-4 text-xs text-gray-500" x-show="state === 'qr'">يُحدَّث كل 7 ثوانٍ</p>
    </div>

    <script>
        function qrPage() {
            return {
                /* الحالة الأولية من الخادم */
                state      : '{{ $status }}',          // loading|qr|authorized|not_found|error
                qrCodeUrl  : @json($qrCodeUrl),
                refreshMs  : {{ $refreshMs }},
                refreshUrl : '{{ route('public.qr.refresh', $profile) }}',

                init() {
                    // إن كان لدينا كود جاهز نستعرضه مباشرة ونبدأ التحديث
                    if (this.state === 'qr') {
                        setInterval(() => this.updateQr(), this.refreshMs);
                    }
                    // إن كنا في وضع تحميل ابدأ الجلب الأولي
                    if (this.state === 'loading') {
                        this.updateQr().then(() =>
                            setInterval(() => this.updateQr(), this.refreshMs)
                        );
                    }
                },

                async updateQr() {
                    try {
                        const res  = await fetch(this.refreshUrl, { cache: 'no-store' });
                        if (!res.ok) { this.state = 'error'; return; }

                        const data = await res.json();

                        if (data.qrCode) {
                            this.qrCodeUrl = data.qrCode.startsWith('data:')
                                ? data.qrCode
                                : `${data.qrCode}&_=${Date.now()}`;  // كسر كاش
                            this.state = 'qr';
                        } else if (data.authorized) {
                            this.state = 'authorized';
                        } else if (data.notFound) {
                            this.state = 'not_found';
                        } else {
                            this.state = 'error';
                        }
                    } catch (e) {
                        console.error(e);
                        this.state = 'error';
                    }
                }
            };
        }
    </script>
</body>
</html>
