<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Taqriz - {{ $student->name }}</title>
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
            padding: 8px;
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
    Sana: {{ $file->updated_at->format('d.m.Y H:i:s') }}
</div>

<div class="text-center font-weight-bold header-text">
    <p>
        O‘ZBEKISTON RESPUBLIKASI OLIY TA’LIM, FAN VA INNOVATSIYALAR VAZIRLIGI
        <br>
        BERDAQ NOMIDAGI QORAQALPOQ DAVLAT UNIVERSITETI
    </p>
    <img src="{{ public_path('dist/img/karsu_logo.png') }}" alt="" style="width: 100px; height: 100px;">
</div>

<div class="mt-4" style="text-align: justify; text-indent: 1.5cm; font-size: 15px;">
    {{ str_replace('\'', '’', $department->name) }} fakulteti {{ $specialty->code }} –
    {{ str_replace('\'', '’', $specialty->name) }}
    @if($specialty->code[0] == '6')
        bakalavr ta’lim yo‘nalishi
    @elseif($specialty->code[0] == '7')
        magistratura mutaxassisligi
    @endif
    <u>{{ str_replace('\'', '’', $group->name) }}
        ({{ $language->name }})</u> guruhi talabasi <b>{{ $student->name }}</b>ning
    {{ $lesson->eduYear->name }}-o‘quv yili, {{ $lesson->level->name }} {{ $lesson->semester->name }} yakunidagi
    <b>{{ str_replace('\'', '’', $lesson->name) }}</b> fanidan yozgan yakuniy nazorat
    yozma ishiga AI tekshiruvi orqali qo‘yilgan baho bo‘yicha
</div>

<div class="text-center font-weight-bold mt-4" style="font-size: 18px; letter-spacing: 2px;">
    T A Q R I Z I
</div>

<table class="table">
    <thead>
    <tr>
        <th style="width: 5%;" class="table">№</th>
        <th style="width: 80%;" class="table">AI qisqacha tavsifi</th>
        <th style="width: 15%;" class="table">Baho</th>
    </tr>
    </thead>
    <tbody>
    @php $totalScore = 0; @endphp
    @forelse($results as $index => $result)
        @php $totalScore += $result->point; @endphp
        <tr>
            <td class="text-center table">{{ $index + 1 }}</td>
            <td class="table">{{ $result->description }}</td>
            <td class="text-center table">{{ number_format($result->point, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center table">Natijalar topilmadi.</td>
        </tr>
    @endforelse

    <tr>
        <td colspan="2" class="text-right font-weight-bold table">Umumiy ball:</td>
        <td class="text-center font-weight-bold table">{{ number_format($totalScore, 2) }}</td>
    </tr>
    </tbody>
</table>


<div class="qr-container" style="text-align: right">
    <img src="data:image/svg+xml;base64, {{ $qrCode }}" alt="QR Code" style="width: 120px; height: 120px;">
</div>
</body>
</html>
