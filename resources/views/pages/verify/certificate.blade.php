<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VERIFICATION: {{ $uniqueId }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-6xl w-full bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">

        <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-center text-white">
            <div class="inline-block p-3 bg-white bg-opacity-20 rounded-full mb-4">
                <i class="fas fa-check text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold mb-2">
                HUJJAT TASDIQLANGAN!
            </h1>
            <p class="text-green-100 text-sm md:text-base" style="font-size: 12px">
                Ushbu hujjat tizim tomonidan tasdiqlangan va o‘zgartirilmasligi kafolatlanadi.
            </p>
        </div>

        <div class="p-6 md:p-10">
            <div
                class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-6 border-b border-gray-100 pb-8 mb-8">
                <img src="https://karsu.uz/logo.png" alt="Logo" class="w-20 h-20 object-contain">
                <div class="text-center md:text-left">
                    <h2 class="font-bold text-gray-800 text-lg uppercase tracking-wide">
                        Berdaq nomidagi Qoraqalpoq davlat universiteti
                    </h2>
                    <p class="text-sm text-gray-500">
                        Sun’iy intellekt (AI) asosida tahlil qilingan yozma ish taqrizi
                    </p>
                </div>
            </div>
            @php
                $totalScore = $results->sum('point');
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Talaba F.I.Sh</p>
                    <p class="font-bold text-gray-800 text-lg">{{ $student->name }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Hujjat ID (UUID)</p>
                    <p class="font-mono text-gray-700">{{ $uniqueId }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Fan va Guruh</p>
                    <p class="font-semibold text-gray-800">{{ $lesson->name }}</p>
                    <p class="text-sm text-gray-500">{{ $group->name }} ({{ $specialty->code }})</p>
                </div>

                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <p class="text-xs text-blue-400 uppercase tracking-wider font-semibold mb-1">Umumiy Ball</p>
                    <p class="font-black text-3xl text-blue-600">{{ number_format($totalScore, 2) }}</p>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Tahlil natijalari</h3>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="p-4 border-b">№</th>
                            <th class="p-4 border-b">AI qisqacha tavsifi</th>
                            <th class="p-4 border-b text-center">Baho</th>
                        </tr>
                        </thead>
                        <tbody class="text-sm">
                        @forelse($results as $index => $result)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-500 font-medium">{{ $index + 1 }}</td>
                                <td class="p-4 text-gray-700 leading-relaxed">{{ $result->description }}</td>
                                <td class="p-4 text-center font-bold text-gray-800">
                                        <span class="bg-green-100 text-green-700 py-1 px-3 rounded-full">
                                            {{ number_format($result->point, 2) }}
                                        </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-4 text-center text-gray-500">Natijalar topilmadi.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center justify-between border-t border-gray-100 pt-6">
                <div class="text-sm text-gray-500 mb-4 md:mb-0">
                    <p><i class="far fa-clock mr-1"></i>
                        <strong>Sana:</strong> {{ $file->updated_at->format('d.m.Y H:i:s') }}</p>
                </div>

                <a href="{{ route('lessons.certificate', ['lesson' => $lesson->id, 'student' => $student->id]) }}"
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-file-pdf mr-2"></i> Asl nusxani yuklab olish
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>
