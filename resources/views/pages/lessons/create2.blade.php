@extends('layouts.app')

@section('page_title', 'Yangi imtihon javoblarini yuklash')
@section('styles')
    <link rel="stylesheet"
          href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
@endsection
@section('content')

    <div id="sync-preloader"
         style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.85); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;"></div>
        <h4 class="mt-3 text-primary font-weight-bold">
            Talabalar ro‘yxati HEMIS tizimidan sinxronizatsiya qilinmoqda...
        </h4>
        <p class="text-muted">Iltimos, sahifani yopmang.</p>
    </div>
    <form action="{{ route('lessons.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-4">
                        <select name="department_id" id="department" class="form-control custom-select" required>
                            <option selected disabled>Fakultetni tanlang</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <select name="specialty_id" id="specialty" class="form-control custom-select" required disabled>
                            <option value="">Mutaxassisliklar ro‘yxati</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <select name="group_id" id="group" class="form-control custom-select" required disabled>
                            <option value="">Guruhlar ro‘yxati</option>
                        </select>
                    </div>
                    <div class="col-md-9 mb-3">
                        <input type="text" class="form-control" name="name" id="name" required placeholder="Fan nomi">
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="input-group date" id="reservationdatetime" data-target-input="nearest">
                            <input type="text"
                                   name="exam_date"
                                   class="form-control datetimepicker-input"
                                   data-target="#reservationdatetime"
                                   placeholder="Imtihon sanasi"
                                   required/>
                            <div class="input-group-append" data-target="#reservationdatetime"
                                 data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="text-center bg-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th class="text-left">Talaba F.I.Sh.</th>
                                    <th style="width: 40%;">PDF fayl</th>
                                </tr>
                                </thead>
                                <tbody id="students-tbody">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Talabalar ro‘yxatini HEMIS tizimi orqali shakllantiring.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-flat btn-success btn-sm" id="save-btn" disabled>
                                <i class="fas fa-check mr-1"></i> Fayllarni yuklash
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Yo‘riqnoma</h3>
                    </div>
                    <div class="card-body" style="text-align: justify; font-size: small">
                        <div>1. Fakultet, mutaxassislik va guruhni ketma-ketlikda tanlang.</div>
                        <div>2. Tizim avtomatik tarz HEMIS tizimidan talabalarni yuklab oladi.</div>
                        <div>3. Har bir talaba qarshisiga uning yozma ishini (faqat PDF) yuklab chiqing.</div>
                        <div>4. Fan nomini va imtihon sanasini kiriting.</div>
                        <div>5. Saqlashdan oldin ma’lumotlar to‘g‘riligiga ishonch hosil qiling va saqlang.</div>
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
                format: 'DD.MM.YYYY',
                defaultDate: new Date()
            });

            $('#department').change(function () {
                let deptId = $(this).val();
                let specialtySelect = $('#specialty');
                let groupSelect = $('#group');
                specialtySelect.html('<option value="">Kutilmoqda...</option>').prop('disabled', true);
                groupSelect.html('<option value="">Guruhlar ro‘yxati</option>').prop('disabled', true);
                $('#save-btn').prop('disabled', true);
                if (deptId) {
                    $.get('/api/departments/' + deptId + '/specialties', function (data) {
                        let options = '<option selected disabled>Mutaxassisliklar ro‘yxati</option>';
                        data.forEach(function (item) {
                            options += `<option value="${item.id}">${item.code} – ${item.name}</option>`;
                        });
                        specialtySelect.html(options).prop('disabled', false);
                    });
                }
            });

            $('#specialty').change(function () {
                let specialtyId = $(this).val();
                let groupSelect = $('#group');
                groupSelect.html('<option value="">Kutilmoqda...</option>').prop('disabled', true);
                $('#save-btn').prop('disabled', true);
                if (specialtyId) {
                    $.get('/api/specialties/' + specialtyId + '/groups', function (data) {
                        let options = '<option selected disabled>Guruhlar ro‘yxati</option>';
                        data.forEach(function (item) {
                            options += `<option value="${item.id}">${item.name}</option>`;
                        });
                        groupSelect.html(options).prop('disabled', false);
                    });
                }
            });

            $('#group').change(function () {
                let groupId = $(this).val();
                if (groupId) {
                    $('#sync-preloader').css('display', 'flex');
                    $.ajax({
                        url: '{{ route("api.sync_students") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            group_id: groupId
                        },
                        success: function (students) {
                            let tbody = '';
                            if (students.length > 0) {
                                students.forEach(function (student, index) {
                                    tbody += `
                                        <tr>
                                            <td class="align-middle text-center">${index + 1}</td>
                                            <td class="align-middle">${student.name}</td>
                                            <td class="text-right">
                                                <div class="d-flex align-items-center upload-wrapper justify-content-end" id="wrapper_${student.id}">
                                                    <label class="btn btn-outline-primary btn-sm mb-0 mr-2" for="file_${student.id}" id="label_${student.id}">
                                                        <i class="fas fa-file-pdf"></i> PDF tanlash
                                                    </label>
                                                    <input type="file"
                                                           name="files[${student.id}]"
                                                           id="file_${student.id}"
                                                           class="d-none custom-file-input-pdf"
                                                           accept=".pdf"
                                                           data-student-id="${student.id}">
                                                    <span class="text-muted file-name-text text-left" id="filename_${student.id}" style="font-size: 0.9rem; min-width: 150px;">
                                                        Fayl tanlanmadi
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                });
                                $('#save-btn').prop('disabled', false); // Yuklash tugmasini yoqish
                                toastr.success(students.length + ' ta talaba muvaffaqiyatli sinxronlandi!');
                            } else {
                                tbody = '<tr><td colspan="3" class="text-center text-danger py-4">Bu guruhda talabalar mavjud emas.</td></tr>';
                                $('#save-btn').prop('disabled', true);
                            }

                            $('#students-tbody').html(tbody);
                        },
                        error: function (xhr) {
                            toastr.error('Talabalarni yuklashda xatolik yuz berdi. Server bilan aloqa yo‘q.');
                            $('#students-tbody').html('<tr><td colspan="3" class="text-center text-danger py-4">Xatolik yuz berdi. Iltimos qaytadan urinib ko‘ring.</td></tr>');
                        },
                        complete: function () {
                            $('#sync-preloader').css('display', 'none');
                        }
                    });
                } else {
                    $('#students-tbody').html('<tr><td colspan="3" class="text-center text-muted py-4">Talabalar ro‘yxatini ko‘rish uchun yuqoridan guruhni tanlang.</td></tr>');
                    $('#save-btn').prop('disabled', true);
                }
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
        });
    </script>
@endpush
