@extends('layouts.app')

@section('title', 'Guruhlar ro\'yxati')

@section('content')
    <div class="row">
        <div class="col-12">

            <div class="my-2">
                <form action="{{ route('groups.index') }}" method="GET" id="filter-form">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <select name="department_id" id="department" class="form-control custom-select rounded-0">
                                    <option value="" {{ !request('department_id') ? 'selected' : '' }}>
                                        Fakultetlar ro‘yxati
                                    </option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('department_id'))
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default text-danger clear-btn" data-target="department">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <select name="specialty_id" id="specialty" class="form-control custom-select rounded-0" {{ (!isset($specialties) || $specialties->isEmpty()) ? 'disabled' : '' }}>
                                    <option value="">Mutaxassislik ro‘yxati</option>
                                    @if(isset($specialties))
                                        @foreach($specialties as $specialty)
                                            <option value="{{ $specialty->id }}" {{ request('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                                {{ $specialty->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @if(request('specialty_id'))
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default text-danger clear-btn" data-target="specialty">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <input type="search" name="search_name" id="search_name" class="form-control rounded-0"
                                       placeholder="Guruh nomi bo‘yicha qidirish" value="{{ request('search_name') }}"
                                       autocomplete="off">
                                @if(request('search_name'))
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default text-danger clear-btn" data-target="search_name">
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
                        Guruhlar ro‘yxati va statistikasi
                    </h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-center align-middle small">
                        <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="text-align: start;">Guruh nomi</th>
                            <th style="width: 20%">Fakultet</th>
                            <th style="width: 20%">Mutaxassislik</th>
                            <th style="width: 10%">Talabalar soni</th>
                            <th style="width: 10%">Fanlar soni</th>
                        </tr>
                        </thead>
                        <tbody id="table-body">
                        @forelse($groups as $group)
                            <tr>
                                <td class="align-middle">#{{ $group->id }}</td>
                                <td class="align-middle text-left">
                                    <div class="font-weight-bold">
                                        {{ $group->name }}
                                    </div>
                                </td>
                                <td class="align-middle">
                                    {{ $group->specialty->department->name ?? '' }}
                                </td>
                                <td class="align-middle">
                                    {{ $group->specialty->name ?? '' }}
                                </td>
                                <td class="align-middle">
                                    {{ $group->students->count() }}
                                </td>
                                <td class="align-middle">
                                    {{ $group->lessons->count() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-search fa-3x mb-3 text-light"></i><br>
                                    <h5 class="font-weight-light">Hech qanday guruh topilmadi.</h5>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($groups->total() > 15)
                    <div class="card-footer clearfix bg-white border-top">
                        <ul class="pagination pagination-sm m-0 float-right" id="pagination-container">
                            {{ $groups->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                $('#specialty').val(''); // Fakultet o'zgarsa, mutaxassislik tozalanadi
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
