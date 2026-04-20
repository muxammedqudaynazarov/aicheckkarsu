<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Vedmost - {{ $lesson->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .mt-4 {
            margin-top: 20px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        /* Jadval stillari */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.table, th.table, td.table {
            border: 1px solid black;
        }

        th, td {
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .header-text {
            font-size: 16px;
            text-transform: uppercase;
        }

        .qr-container {
            margin-top: 40px;
            text-align: center;
        }

        .doc-id {
            font-size: 10px;
            color: #555;
            text-align: right;
            margin-bottom: 20px;
            font-family: "Courier New", Courier, monospace;
        }
    </style>
</head>
<body>

<div class="doc-id">
    Document ID: {{ $uniqueId }}
    <br>
    Sana: {{ $lesson->updated_at->format('d.m.Y H:i:s') }}
</div>

<div class="text-center font-weight-bold header-text">
    <p>
        O‘ZBEKISTON RESPUBLIKASI OLIY TA’LIM, FAN VA INNOVATSIYALAR VAZIRLIGI
        <br>
        BERDAQ NOMIDAGI QORAQALPOQ DAVLAT UNIVERSITETI
    </p>
    <img src="{{ public_path('dist/img/karsu_logo.png') }}" alt="" style="width: 100px; height: 100px;">
</div>

<div class="mt-4" style="text-align: justify; text-indent: 1.5cm; font-size: 16px;">
    <b>{{ $department->name }}</b> fakulteti <b>{{ $specialty->name }}</b> ta’lim yo‘nalishi <b>{{ $group->name }}
        ({{ $language->name }})</b> guruhi talabalarining <b>{{ $lesson->name }}</b> fanidan
    yozgan yakuniy nazorat yozma ishlariga AI tekshiruvchi orqali qo‘yilgan baholari
</div>

<div class="text-center font-weight-bold mt-4" style="font-size: 18px; letter-spacing: 2px;">
    H I S O B O T I
</div>

<table class="table" style="font-size: 12px">
    <thead>
    <tr>
        <th class="table">Talaba ID</th>
        <th class="table">Talaba F.I.Sh.</th>
        <th class="table">Bilet №</th>
        <th class="table" style="width: 35px">1</th>
        <th class="table" style="width: 35px">2</th>
        <th class="table" style="width: 35px">3</th>
        <th class="table" style="width: 35px">4</th>
        <th class="table" style="width: 35px">5</th>
        <th class="table" style="width: 65px">E</th>
    </tr>
    </thead>
    <tbody>
    @php $totalScore = 0; @endphp
    @forelse($files as $index => $file)
        <tr>
            <td class="text-center table">{{ $file->student_id_number }}</td>
            <td class="table">{{ $file->name }}</td>
            @if ($file->fileForLesson($lesson->id)->first()?->participant == '1')
                <td class="text-center table" colspan="6" style="font-style: italic">Qatnashmadi</td>
            @else
                <td class="text-center table">{{ $file->fileForLesson($lesson->id)->first()?->ticket_number }}</td>
                <td class="text-center table">{{ $file->fileForLesson($lesson->id)->first()?->results->where('question_number', '1')->first()?->point ?? 0 }}</td>
                <td class="text-center table">{{ $file->fileForLesson($lesson->id)->first()?->results->where('question_number', '2')->first()?->point ?? 0 }}</td>
                <td class="text-center table">{{ $file->fileForLesson($lesson->id)->first()?->results->where('question_number', '3')->first()?->point ?? 0 }}</td>
                <td class="text-center table">{{ $file->fileForLesson($lesson->id)->first()?->results->where('question_number', '4')->first()?->point ?? 0 }}</td>
                <td class="text-center table">{{ $file->fileForLesson($lesson->id)->first()?->results->where('question_number', '5')->first()?->point ?? 0 }}</td>
            @endif
            <td class="text-center table">{{ number_format($file->fileForLesson($lesson->id)->first()?->over_all() ?? 0, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center table">Natijalar topilmadi.</td>
        </tr>
    @endforelse
    {{--<tr>
        <td colspan="2" class="text-right font-weight-bold table">Umumiy ball:</td>
        <td class="text-center font-weight-bold table">{{ number_format($totalScore, 2) }}</td>
    </tr>--}}
    </tbody>
</table>


<div class="qr-container" style="text-align: right">
    <img src="data:image/svg+xml;base64, {{ $qrCode }}" alt="QR Code" style="width: 80px; height: 80px;">
</div>
</body>
</html>
