@extends('layouts.app')

@section('title', 'Imtihonlar ro\'yxati')
@section('page_title', 'Fanlar va Imtihonlar')
@section('breadcrumb')
    <li class="breadcrumb-item active">Imtihonlar</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">

                <div class="card-header">
                    <h3 class="card-title mt-1 font-weight-bold">
                        Imtihonlar ro‘yxati
                    </h3>

                    <div class="card-tools">
                        <a href="{{ route('lessons.create') ?? '#' }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i> Yangi yaratish
                        </a>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-center align-middle small">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align: start">Fan nomi</th>
                            <th>Talabalar soni</th>
                            <th>Fayllar soni</th>
                            <th>Imtihon sanasi</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="table-body">
                        @forelse($lessons as $lesson)
                            <tr>
                                <td class="align-middle">#{{ $lesson->id }}</td>
                                <td class="align-middle text-left">
                                    @if($lesson->status != '1')
                                        <div>
                                            <a href="{{ route('lessons.edit', $lesson->id) }}" class="font-weight-bold">
                                                {{ $lesson->name }}
                                            </a>
                                        </div>
                                        <div>
                                            {{ $lesson->group->name }}
                                        </div>
                                    @else
                                        <div class="font-weight-bold">
                                            {{ $lesson->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $lesson->group->students->count() ?? 0}}</td>
                                <td class="align-middle">
                                    {{ $lesson->files->where('participant', '0')->count() ?? 0 }}
                                </td>
                                <td class="align-middle">{{ $lesson->exam_date->format('d.m.Y') }}</td>

                                @if($lesson->status === '0')
                                    <td class="text-center align-middle">
                                        <span class="badge bg-secondary">Yangi yaratilgan</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($lesson->canBeChecked())
                                            <form action="{{ route('lessons.start_checking', $lesson->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Haqiqatan ham fayllarni tekshiruvga yubormoqchimisiz?');">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm"
                                                        title="AI yordamida tekshirish">
                                                    Tekshirish
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm disabled"
                                                    title="Bo‘sh API akkaunt yo‘q yoki Kunlik RPD limiti yetarli emas">
                                                Tekshirish
                                            </button>
                                        @endif
                                        <form action="{{ route('lessons.destroy', $lesson->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Diqqat! Ushbu imtihon va unga tegishli barcha PDF fayllar butunlay o‘chib ketadi. Rozimisiz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                O‘chirish
                                            </button>
                                        </form>
                                    </td>

                                @elseif($lesson->status === '1')
                                    @php
                                        $total = $lesson->files->where('participant', '0')->count();
                                        $finished = $lesson->files->where('status', '2')->where('participant', '0')->count();
                                        $precent = $total > 0 ? round(($finished / $total) * 100) : 0;
                                    @endphp
                                    <td class="text-center align-middle" colspan="2" style="width: 20%;">
                                        <div class="progress position-relative rounded"
                                             style="height: 20px; border: 1px solid #ccc; background: transparent;">
                                            <div
                                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                                role="progressbar"
                                                style="width: {{ $precent }}%;"
                                                aria-valuenow="{{ $precent }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>

                                            <div
                                                class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
                                                style="top: 0; left: 0; z-index: 10;">
                                                {{ $precent }}%
                                            </div>

                                        </div>
                                    </td>

                                @elseif($lesson->status === '2')
                                    <td class="text-center align-middle" colspan="2" style="width: 20%">
                                        <div class="progress" role="progressbar" aria-valuenow="100" aria-valuemin="0"
                                             aria-valuemax="100">
                                            <div
                                                class="progress-bar progress-bar-striped bg-primary rounded"
                                                style="width: 100%;">
                                                Yakunlangan (100%)
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i><br>
                                    Hozircha imtihonlar mavjud emas.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lessons->total() > 15)
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right" id="pagination-container">
                            {{ $lessons->links('pagination::bootstrap-4') }}
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

    <script>
        $(document).ready(function () {
            // Konsolga ma'lumot chiqarishni yoqish (Qayerda xatolik borligini ko'rish uchun)
            Pusher.logToConsole = false;

            // Pusherga ulanish
            var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                forceTLS: true
            });

            // 1-QADAM: Laravel Event faylidagi 'broadcastOn()' kanal nomi yoziladi.
            // Agar test qilayotgan bo'lsangiz 'my-channel', ilova uchun 'lessons'
            var channel = pusher.subscribe('lessons');

            // 2-QADAM: Laravel Event faylidagi 'broadcastAs()' nomi yoziladi.
            // Test uchun 'my-event', loyiha uchun masalan 'LessonProgressUpdatedEvent'
            channel.bind('LessonProgressUpdatedEvent', function (data) {
                console.log("Xabar qabul qilindi! ID:", data);

                // 3-QADAM: Realtime yangilash (Jadvalni o'zini qayta chizish)
                $.ajax({
                    url: window.location.href, // Hozirgi turgan sahifa manzili
                    type: 'GET',
                    cache: false,
                    success: function (response) {
                        // Keltirilgan butun HTML ichidan faqat jadval tanasini (#table-body) ajratib olamiz
                        let newTbody = $(response).find('#table-body').html();

                        // Agar yangi jadval topilsa, eskisini o'rniga qo'yamiz
                        if (newTbody) {
                            $('#table-body').html(newTbody);
                            console.log("Sahifa realtime yangilandi!");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Yangilash xato yakunlandi:", error);
                    }
                });
            });
        });
    </script>
@endpush
