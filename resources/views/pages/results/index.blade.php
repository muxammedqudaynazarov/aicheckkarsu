@extends('layouts.app')

@section('title', 'Imtihonlar royxati')

@section('content')
    <div class="row">
        <div class="col-12">

            <div class="my-2">
                <form action="{{ route('results.index') }}" method="GET" id="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <select name="department_id" id="department"
                                        class="form-control custom-select rounded-0">
                                    <option value="" {{ !request('department_id') ? 'selected' : '' }}>
                                        Fakultetlar ro‘yxati
                                    </option>
                                    @foreach($departments as $department)
                                        <option
                                            value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('department_id'))
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default text-danger clear-btn"
                                                data-target="department">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <select name="specialty_id" id="specialty"
                                        class="form-control custom-select rounded-0" {{ $specialties->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Mutaxassislik ro‘yxati</option>
                                    @foreach($specialties as $specialty)
                                        <option
                                            value="{{ $specialty->id }}" {{ request('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('specialty_id'))
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default text-danger clear-btn"
                                                data-target="specialty">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <input type="search" name="search_name" id="search_name" class="form-control rounded-0"
                                       placeholder="Fan bo‘yicha qidirish (Enter)" value="{{ request('search_name') }}"
                                       autocomplete="off">
                                @if(request('search_name'))
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default text-danger clear-btn"
                                                data-target="search_name">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title mt-1 font-weight-bold">
                        Fan vedomostlari
                    </h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-center align-middle small">
                        <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="text-align: start;">Fan nomi</th>
                            <th style="text-align: start;">Imtihon guruhi</th>
                            <th>Talabalar</th>
                            <th>Fayllar</th>
                            <th>Imtihon sanasi</th>
                            <th>Holati</th>
                            <th style="width: 5%"></th>
                        </tr>
                        </thead>
                        <tbody id="table-body">
                        @forelse($lessons as $lesson)
                            <tr>
                                <td class="align-middle">#{{ $lesson->uuid }}</td>
                                <td class="align-middle text-left">
                                    <div class="font-weight-bold">
                                        {{ $lesson->name }}
                                    </div>
                                </td>
                                <td class="align-middle text-left">{{ $lesson->group->name }}</td>
                                <td class="align-middle">
                                    {{ $lesson->group->students->count() ?? 0}}
                                </td>
                                <td class="align-middle">
                                    {{ $lesson->files->where('participant', '0')->count() ?? 0 }}
                                </td>
                                <td class="align-middle">{{ $lesson->exam_date->format('d.m.Y') }}</td>
                                <td class="align-middle">
                                    <div class="badge badge-success px-2 py-1">
                                        <i class="fas fa-check-circle mr-1"></i> Yakunlangan
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('lessons.statement', $lesson->id) ?? '#' }}"
                                       class="btn btn-outline-primary btn-sm shadow-sm">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-search fa-3x mb-3"></i><br>
                                    <h5 class="small">Hech qanday ma’lumot topilmadi.</h5>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lessons->total() > 15)
                    <div class="card-footer clearfix bg-white border-top">
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
    <script>
        $(document).ready(function () {
            const form = $('#filter-form');
            $('#department').change(function () {
                $('#specialty').val('');
                form.submit();
            });
            $('#specialty').change(function () {
                form.submit();
            });
            $('#search_name').keypress(function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                    form.submit();
                }
            });
            $('.clear-btn').click(function () {
                let targetId = $(this).data('target');
                $('#' + targetId).val('');
                if (targetId === 'department') {
                    $('#specialty').val('');
                }
                form.submit();
            });
        });
    </script>
@endpush
