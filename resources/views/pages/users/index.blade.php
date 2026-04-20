@extends('layouts.app')
@section('styles')
    <style>
        .blink {
            animation: blinker 1s linear infinite;
        }

        @keyframes blinker {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
    </style>
@endsection
@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mt-1 font-weight-bold">
                <div>Tizim foydalanuvchilari</div>
            </h3>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-center align-middle">
                <thead class="bg-light">
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="text-align: start;"></th>
                    <th>Foydalanuvchi F.I.Sh.</th>
                    <th>Fakultet / bo‘lim</th>
                    <th>Telefon raqami</th>
                    <th>Tizimdagi roli</th>
                    <th>Ro‘yxatdan o‘tgan</th>
                    <th></th>
                </tr>
                </thead>
                <tbody id="table-body">
                @forelse($users as $user)
                    <tr>
                        <td class="align-middle">#{{ $user->id }}</td>
                        <td class="align-middle">
                            <img src="{{ $user->image }}" alt="" class="rounded-circle" style="width: 48px">
                        </td>
                        <td class="align-middle text-left">
                            <div class="font-weight-bold">
                                {{ json_decode($user->name)->full_name }}
                            </div>
                            <code>{{ $user->employee_id_number }}</code>
                        </td>
                        <td class="align-middle">
                            {{ $user->department->name ?? '' }}
                        </td>
                        <td class="align-middle">
                            {{ $user->phone }}
                        </td>
                        <td class="align-middle">
                            {{ $roles[$user->pos] }}
                        </td>
                        <td class="align-middle">
                            {{ $user->created_at->format('d.m.Y H:i:s') }}
                        </td>
                        <td class="align-middle">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3 text-light"></i><br>
                            <h5 class="font-weight-light">Hech qanday ma’lumot topilmadi.</h5>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->total() > 15)
            <div class="card-footer clearfix bg-white border-top">
                <ul class="pagination pagination-sm m-0 float-right" id="pagination-container">
                    {{ $users->links('pagination::bootstrap-4') }}
                </ul>
            </div>
        @endif
    </div>
@endsection
