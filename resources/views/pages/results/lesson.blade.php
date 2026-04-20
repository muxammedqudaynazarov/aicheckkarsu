@extends('layouts.app')

@section('page_title', 'Imtihon ma\'lumotlarini tahrirlash')

@section('styles')
    <link rel="stylesheet"
          href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
@endsection

@section('content')
    <form action="{{ route('lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data"
          autocomplete="off">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-9">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item">
                            <a href="{{ route('drive.index') }}">O‘quv yillari</a>
                        </li>
                        @foreach($breadcrumbs as $crumb)
                            @if($loop->last)
                                <li class="breadcrumb-item active" aria-current="page">{!! $crumb['name'] !!}</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a></li>
                            @endif
                        @endforeach
                    </ol>
                </nav>

                <div class="card card-warning card-outline">

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="text-center bg-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th class="text-left">Talaba F.I.Sh.</th>
                                    <th style="width: 40%;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($students as $index => $student)
                                    @php
                                        $existingFile = $files->get($student->id);
                                        $hasFile = $existingFile && $existingFile->file_url;
                                    @endphp
                                    <tr>
                                        <td class="align-middle text-center">{{ $index + 1 }}</td>
                                        <td class="align-middle">{{ $student->name }}</td>
                                        <td class="text-right">
                                            @php($file = $student->fileForLesson($lesson->id)->first())
                                            <input type="hidden" name="remove_files[{{ $student->id }}]"
                                                   id="remove_flag_{{ $student->id }}" value="0">

                                            <div id="existing_file_div_{{ $student->id }}"
                                                 class="d-flex justify-content-end align-items-center {{ $hasFile ? '' : 'd-none' }}">
                                                @if($hasFile)
                                                    @if($file->status == '2')
                                                        <div class="mr-4">
                                                            {{ number_format($file->over_all(), 2) }}
                                                        </div>
                                                    @else
                                                        <div class="text-success font-weight-bold mr-3"
                                                             style="font-size: 0.9rem;">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>
                                                    @endif
                                                    <a href="{{ $hasFile ? Storage::url($existingFile->file_url) : '#' }}"
                                                       target="_blank" class="btn btn-sm btn-info mr-2">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($file->status == '2')
                                                        <a href="{{ route('lessons.certificate', ['lesson' => $lesson->id, 'student' => $student->id]) }}"
                                                           target="_blank" class="btn btn-sm btn-danger mr-2">
                                                            <i class="fas fa-scroll"></i>
                                                        </a>
                                                    @endif
                                                    @if($lesson->status == '0')
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger remove-file-btn"
                                                                data-student-id="{{ $student->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                            @if($lesson->status == '0')
                                                <div
                                                    class="align-items-center upload-wrapper justify-content-end {{ $hasFile ? 'd-none' : 'd-flex' }}"
                                                    id="new_file_div_{{ $student->id }}">
                                                    <label class="btn btn-outline-primary btn-sm mb-0 mr-2"
                                                           for="file_{{ $student->id }}" id="label_{{ $student->id }}">
                                                        <i class="fas fa-file-pdf"></i> PDF tanlash
                                                    </label>
                                                    <input type="file"
                                                           name="files[{{ $student->id }}]"
                                                           id="file_{{ $student->id }}"
                                                           class="d-none custom-file-input-pdf"
                                                           accept=".pdf"
                                                           data-student-id="{{ $student->id }}">

                                                    <span class="text-muted file-name-text text-left"
                                                          id="filename_{{ $student->id }}"
                                                          style="font-size: 0.9rem; min-width: 150px;">
                                                    Fayl tanlanmadi
                                                </span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($lesson->status =='0')
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-flat btn-warning btn-sm" id="save-btn">
                                    <i class="fas fa-check mr-1"></i> O‘zgarishlarni saqlash
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            Guruh ma’lumotlari
                        </h3>
                    </div>
                    <div class="card-body p-0" style="text-align: justify; font-size: small">
                        <table class="table">
                            <tr>
                                <th>O‘quv yili</th>
                                <td>{{ $year->name }}</td>
                            </tr>
                            <tr>
                                <th>Fakultet</th>
                                <td>{{ $department->name }}</td>
                            </tr>
                            <tr>
                                <th>Mutaxassislik</th>
                                <td>{{ $specialty->code }} – {{ $specialty->name }}</td>
                            </tr>
                            <tr>
                                <th>Kurs</th>
                                <td>{{ $level->name }}</td>
                            </tr>
                            <tr>
                                <th>Guruh</th>
                                <td>{{ $group->name }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('#reservationdatetime').datetimepicker({
                icons: {time: 'far fa-clock'},
                format: 'DD.MM.YYYY'
            });
            $(document).on('change', '.custom-file-input-pdf', function () {
                const studentId = $(this).data('student-id');
                const fileNameSpan = $('#filename_' + studentId);
                const labelBtn = $('#label_' + studentId);
                if (this.files && this.files.length > 0) {
                    fileNameSpan.text(this.files[0].name).removeClass('text-muted').addClass('text-success font-weight-bold');
                    labelBtn.removeClass('btn-outline-primary').addClass('btn-primary');
                } else {
                    fileNameSpan.text('Fayl tanlanmadi').removeClass('text-success font-weight-bold').addClass('text-muted');
                    labelBtn.removeClass('btn-primary').addClass('btn-outline-primary');
                }
            });
            $(document).on('click', '.remove-file-btn', function () {
                const studentId = $(this).data('student-id');
                $('#remove_flag_' + studentId).val('1');
                $('#existing_file_div_' + studentId).removeClass('d-flex').addClass('d-none');
                $('#new_file_div_' + studentId).removeClass('d-none').addClass('d-flex');
            });
        });
    </script>
@endpush
