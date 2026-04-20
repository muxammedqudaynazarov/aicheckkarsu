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

    <div id="scan-preloader"
         style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
        <i class="fas fa-print fa-4x text-white fa-pulse mb-3"></i>
        <h4 class="text-white font-weight-bold">
            Skaner ishlamoqda...
        </h4>
        <p class="text-light">Iltimos, jarayon tugagunicha kuting.</p>
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

                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                <tr>
                                    <th style="width: 5%;" class="text-center">#</th>
                                    <th class="text-left">Talaba F.I.Sh.</th>
                                    <th style="width: 40%;" class="text-right"></th>
                                </tr>
                                </thead>
                                <tbody id="students-tbody">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="fas fa-users fa-3x mb-3 opacity-50"></i><br>
                                        Talabalar ro‘yxatini HEMIS tizimi orqali shakllantiring.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-success" id="save-btn" disabled>
                                <i class="fas fa-check mr-1"></i> Ma’lumotlarni saqlash
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-info card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Yo‘riqnoma</h3>
                    </div>
                    <div class="card-body text-muted" style="text-align: justify; font-size: 0.9rem;">
                        <div>
                            1. Fakultet, mutaxassislik va guruhni ketma-ketlikda tanlang.
                        </div>
                        <div>
                            2. Tizim avtomatik tarz HEMIS tizimidan talabalarni yuklab oladi.</div>
                        <div>
                            3. Har bir talaba qarshisidagi <b>&laquo;Skanerlash&raquo;</b> tugmasini bosing. Qog‘ozlar
                            avtomatik A4, formatida skaner bo‘lib tizimga ulanadi.
                        </div>
                        <div>
                            4. Fan nomini va imtihon sanasini kiriting.
                        </div>
                        <div class="mb-0">
                            5. Saqlash tugmasini bosish orqali barcha qog‘ozlarni serverga yuboring.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="https://cdn.asprise.com/scannerjs/scanner.js" type="text/javascript"></script>

    <script>
        let currentScanningStudentId = null;
        $(document).ready(function () {
            $('#reservationdatetime').datetimepicker({
                icons: {time: 'far fa-clock'},
                format: 'DD.MM.YYYY',
                defaultDate: new Date()
            });

            // Fakultet o'zgarganda
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
                                            <td class="align-middle text-center font-weight-bold text-muted">${index + 1}</td>
                                            <td class="align-middle font-weight-bold" style="color: #343a40;">${student.name}</td>
                                            <td class="text-right align-middle">
                                                <div class="d-flex align-items-center upload-wrapper justify-content-end" id="wrapper_${student.id}">

                                                    <span class="text-muted file-name-text text-left mr-3" id="filename_${student.id}" style="font-size: 0.9rem; min-width: 140px;">
                                                        <i class="fas fa-times-circle text-danger mr-1"></i> Skan qilinmadi
                                                    </span>

                                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" id="btn_scan_${student.id}" onclick="startScanForStudent(${student.id})">
                                                        <i class="fas fa-print mr-1"></i> Skanerlash
                                                    </button>

                                                    <input type="file"
                                                           name="files[${student.id}]"
                                                           id="file_${student.id}"
                                                           class="d-none"
                                                           accept=".pdf">
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                });
                                $('#save-btn').prop('disabled', false);
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
        });

        function startScanForStudent(studentId) {
            currentScanningStudentId = studentId;
            $('#scan-preloader').css('display', 'flex');
            scanner.scan(processScannedPdf, {
                "use_asprise_dialog": false,
                "output_settings": [
                    {
                        "type": "return-base64",
                        "format": "pdf"
                    }
                ],
                "twain_cap_setting": {
                    "ICAP_PIXELTYPE": "TWPT_GRAY",
                    "ICAP_XRESOLUTION": "200",
                    "ICAP_YRESOLUTION": "200",
                    "ICAP_SUPPORTEDSIZES": "TWSS_A4"
                }
            });
        }

        function processScannedPdf(successful, mesg, response) {
            $('#scan-preloader').css('display', 'none');

            if (!successful) {
                toastr.error('Skanerlashda xatolik yuz berdi yoki skaner topilmadi!');
                return;
            }

            if (mesg != null && mesg.toLowerCase().indexOf('user cancel') >= 0) {
                toastr.error('Skanerlash foydalanuvchi tomonidan bekor qilindi.');
                return;
            }

            let pdfBase64 = null;
            if (response.output && response.output.length > 0) {
                pdfBase64 = response.output[0].base64;
            } else {
                toastr.error('PDF ma’lumotini shakllantirib bo‘lmadi.');
                return;
            }

            if (pdfBase64 && currentScanningStudentId) {
                const byteCharacters = atob(pdfBase64);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], {type: 'application/pdf'});
                const file = new File([blob], "scanned_exam_" + currentScanningStudentId + ".pdf", {type: 'application/pdf'});
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('file_' + currentScanningStudentId).files = dataTransfer.files;
                let fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                $('#filename_' + currentScanningStudentId)
                    .html(`<i class="fas fa-check-circle text-success mr-1"></i> Tayyor (${fileSizeMB} MB)`)
                    .removeClass('text-muted')
                    .addClass('text-success font-weight-bold');
                $('#btn_scan_' + currentScanningStudentId)
                    .removeClass('btn-outline-primary')
                    .addClass('btn-success')
                    .html('<i class="fas fa-sync-alt mr-1"></i> Qayta skanerlash');
                toastr.success('Talaba ishi muvaffaqiyatli skanerlandi va biriktirildi!');
                currentScanningStudentId = null;
            }
        }
    </script>
@endpush
