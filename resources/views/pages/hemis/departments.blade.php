@extends('layouts.app')

@section('title', 'Fakultetlar ro\'yxati')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title mt-1 font-weight-bold">
                        Fakultetlar ro‘yxati va statistikasi
                    </h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-center align-middle small">
                        <thead class="bg-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="text-align: start;">Fakultet nomi</th>
                            <th style="width: 10%">Mutaxassisliklar</th>
                            <th style="width: 10%">O‘quv rejalar</th>
                            <th style="width: 10%">Guruhlar</th>
                            <th style="width: 10%">Talabalar</th>
                            <th style="width: 10%">Fanlar</th>
                        </tr>
                        </thead>
                        <tbody id="table-body">
                        @forelse($departments as $department)
                            <tr>
                                <td class="align-middle">#{{ $department->id }}</td>
                                <td class="align-middle text-left">
                                    <div class="font-weight-bold">
                                        {{ $department->name }}
                                    </div>
                                </td>
                                <td class="align-middle">
                                    {{ $department->specialties->count() ?? 0 }}
                                </td>
                                <td class="align-middle">
                                    {{ $department->curricula->count() ?? 0 }}
                                </td>
                                <td class="align-middle">
                                    {{ $department->groups->count() ?? 0 }}
                                </td>
                                <td class="align-middle">
                                    {{ $department->groups->sum(fn($group) => $group->students->count()) }}
                                </td>
                                <td class="align-middle">
                                    {{ $department->groups->sum(fn($group) => $group->lessons->count()) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-search fa-3x mb-3 text-light"></i><br>
                                    <h5 class="font-weight-light">Hech qanday ma'lumot topilmadi.</h5>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($departments->total() > 15)
                    <div class="card-footer clearfix bg-white border-top">
                        <ul class="pagination pagination-sm m-0 float-right" id="pagination-container">
                            {{ $departments->links('pagination::bootstrap-4') }}
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
