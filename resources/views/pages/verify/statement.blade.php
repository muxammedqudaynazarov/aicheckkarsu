<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VERIFICATION STATEMENT: {{ $uniqueId }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-5xl w-full bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">

        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-center text-white">
            <div class="inline-block p-3 bg-white bg-opacity-20 rounded-full mb-4">
                <i class="fas fa-file-signature text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold mb-2" style="text-transform: uppercase">Qaydnoma (vedomost) tasdiqlandi!</h1>
            <p class="text-blue-100 text-sm md:text-base" style="font-size: 12px">
                Ushbu qaydnoma tizimda avtomatik shakllantirilgan va AI tekshiruvi asosida tasdiqlangan.
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
                    <p class="text-sm text-gray-500">Sun’iy intellekt (AI) ishtirokidagi yakuniy nazorat qaydnomasi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 mb-8">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Fan nomi</p>
                    <p class="font-bold text-gray-800">{{ $lesson->name }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Guruh va yo‘nalish</p>
                    <p class="font-bold text-gray-800">{{ $group->name }}</p>
                    <p class="text-xs text-gray-500">{{ $specialty->code }} – {{ $specialty->name }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">O‘quv yili / Semestr</p>
                    <p class="font-bold text-gray-800">{{ $lesson->eduYear->name ?? 'Noma’lum' }}</p>
                    <p class="text-xs text-gray-500">{{ $lesson->semester->name ?? 'Noma’lum' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Hujjat ID</p>
                    <p class="font-mono text-sm text-gray-700 break-all">{{ $uniqueId }}</p>
                </div>
            </div>

            <div class="mb-8">
                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="p-4 border-b w-12 text-center">№</th>
                            <th class="p-4 border-b">Talaba F.I.Sh</th>
                            <th class="p-4 border-b text-center">Holati</th>
                            <th class="p-4 border-b text-center">Umumiy Ball</th>
                            <th class="p-4 border-b text-center">Hujjatlar</th>
                        </tr>
                        </thead>
                        <tbody class="text-sm">
                        @forelse($students as $index => $student)
                            @php
                                $file = $student->files->first();
                                $score = $file ? $file->results->sum('point') : 0;

                                if(!$file) {
                                    $status = 'Fayl yo‘q';
                                    $statusClass = 'bg-gray-100 text-gray-600';
                                } elseif($file->participant == '1') {
                                    $status = '<s>Qatnashmadi</s>';
                                    $statusClass = '';
                                } elseif($file->status == '0' || $file->status == '1') {
                                    $status = 'Tekshirilmoqda';
                                    $statusClass = 'bg-yellow-100 text-yellow-700';
                                } else {
                                    $status = 'Baholandi';
                                    $statusClass = 'bg-green-100 text-green-700';
                                }
                            @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 text-center text-gray-500 font-medium">{{ $index + 1 }}</td>

                                <td class="p-4 font-semibold text-gray-700">
                                    {{ $student->name }}
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">
                                        ID: {{ $student->student_id_number ?? '-' }}</div>
                                </td>

                                <td class="p-4 text-center">
                                        <span class="py-1 px-3 rounded-full text-xs {{ $statusClass }}">
                                            {!! $status !!}
                                        </span>
                                </td>

                                <td class="p-4 text-center font-black text-gray-800 text-base">
                                    {{ $file && $file->status == '2' ? number_format($score, 2) : '-' }}
                                </td>

                                <td class="p-4 text-center space-x-2">

                                    @if($file && $file->file_url && $file->participant == '0')
                                        <a href="{{ Storage::url($file->file_url) }}" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors text-xs font-semibold"
                                           title="Asl yozma ishni ko'rish">
                                            Yozma ish
                                        </a>
                                    @endif

                                    @if($file && $file->status == '2' && $file->uuid && $file->participant == '0')
                                        <a href="{{ route('lessons.certificate', ['lesson' => $lesson->id, 'student' => $student->id]) }}"
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-600 border border-purple-200 rounded hover:bg-purple-100 transition-colors text-xs font-semibold"
                                           title="AI tomonidan yozilgan taqrizni ko'rish">
                                            AI taqriz
                                        </a>
                                    @endif

                                    @if(!$file || ($file && !$file->file_url && $file->status != '2'))
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    Ushbu guruhda talabalar topilmadi.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center justify-between border-t border-gray-100 pt-6">
                <div class="text-sm text-gray-500 mb-4 md:mb-0 flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2 text-lg"></i>
                    <span>Tizim tomonidan tasdiqlangan va o‘zgartirishdan himoyalangan.</span>
                </div>

                <a href="{{ route('lessons.statement', $lesson->id) }}"
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="fas fa-download mr-2"></i> Qaydnomani yuklab olish
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>
