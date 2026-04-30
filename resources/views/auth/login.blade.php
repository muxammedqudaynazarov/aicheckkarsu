<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sun’iy intellektga asoslangan talabalar yozma ishlarini tekshirish va bilimini baholash tizimi |
        RegOFIS.UZ</title>
    <meta name="description"
          content="Qoraqalpoq davlat universiteti tomonidan ishlab chiqilgan talabalarining yozma ishlari va imtihon javoblarini Sun’iy intellekt (AI) yordamida avtomatik tekshirish va chuqur tahlil qilish platformasi">
    <meta name="keywords"
          content="AIcheck, KarSU, Qoraqalpoq davlat universiteti, sun’iy intellekt, imtihon tekshirish, talabalar bilimi, avtomatlashtirilgan tizim, AI ta’lim, QQDU, QMU, yozma ish tekshirish, Qudaynazarov Muxammed">
    <meta name="author" content="Qoraqalpoq davlat universiteti">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="google-site-verification" content="uY4c-9ZVaqhKCGu1Vy3znBxQzV-vc_HoHUalbCkRyCI" />
    <meta name="yandex-verification" content="e8c44c5faf4ec232" />
    <meta property="og:site_name" content="AIcheck.RegOFIS.UZ">
    <meta property="og:title"
          content="AICheck KarSU - Sun’iy intellektga asoslangan talabalar yozma ishlarini tekshirish va bilimini baholash tizimi">
    <meta property="og:description"
          content="Sun’iy intellektga asoslangan talabalar yozma ishlarini tekshirish va bilimini baholash tizimi">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('dist/img/og-image.jpg') }}">
    <meta property="og:locale" content="uz_UZ">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AIcheck - KarSU">
    <meta name="twitter:description"
          content="Sun’iy intellektga asoslangan talabalar yozma ishlarini tekshirish va bilimini baholash tizimi">
    <meta name="twitter:image" content="{{ asset('dist/img/og-image.jpg') }}">
    <meta name="theme-color" content="#0056b3">
    <link rel="icon" type="image/png" href="{{ asset('dist/img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('dist/img/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-indigo-800 to-purple-900 relative overflow-hidden">

<div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
    <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-500 opacity-20 blur-3xl"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 rounded-full bg-purple-500 opacity-20 blur-3xl"></div>
</div>

<div class="max-w-md w-full mx-4 glass-effect rounded-2xl shadow-2xl overflow-hidden z-10 border border-white/20">
    <div class="p-8 space-y-8">

        <div class="text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-50 rounded-full mb-4 shadow-inner">
                <img src="{{ asset('dist/img/logo.png') }}" alt="AIcheck Logo" class="w-16 h-16 object-contain"
                     onerror="this.src='https://karsu.uz/logo.png'">
            </div>
            <h2 class="text-2xl font-bold text-indigo-600 tracking-tight">AIcheck KarSU</h2>
            <p class="mt-2 text-sm text-gray-500">
                Sun’iy intellektga asoslangan talabalar yozma ishlarini tekshirish va bilimini baholash tizimi
            </p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <a href="{{ route('auth.hemis') }}"
           class="group relative w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-university text-blue-200 group-hover:text-white transition-colors"></i>
                    </span>
            HEMIS orqali kirish
            <i class="fas fa-arrow-right ml-2 text-sm opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
        </a>
    </div>

    <div class="bg-gray-50/50 px-8 py-4 border-t border-gray-100 text-center">
        <p class="text-xs text-gray-500 font-medium">
            Berdaq nomidagi Qoraqalpoq davlat universiteti <br>
            AIcheck KarSU &copy; {{ date('Y') }}
        </p>
    </div>
</div>

</body>
</html>
