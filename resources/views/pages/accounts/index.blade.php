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
                <div>Tekshiruvchi API profillar</div>
                <div style="font-size: 12px; font-weight: normal">
                    RPD – bu bugun maksimal tekshirish mumkin bo‘lgan hujjatlar soni
                </div>
            </h3>

            <div class="card-tools">
                <a href="{{ route('accounts.create') ?? '#' }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Yangi yaratish
                </a>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-center align-middle">
                <thead class="bg-light">
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="text-align: start;">E-mail</th>
                    <th>AI model</th>
                    <th>Muallifi</th>
                    <th>Fakulteti</th>
                    <th>RPD</th>
                    <th style="width: 15%">Token</th>
                    <th style="width: 10%"></th>
                </tr>
                </thead>
                <tbody id="table-body">
                @forelse($accounts as $account)
                    <tr>
                        <td class="align-middle">#{{ $account->id }}</td>
                        <td class="align-middle text-left">
                            {{ $account->email }}
                        </td>
                        <td class="align-middle">
                            {{ $account->model }}
                        </td>
                        <td class="align-middle">
                            {{ json_decode($account->user->name ?? '')->short_name ?? '' }}
                        </td>
                        <td class="align-middle">
                            {{ $account->department->name ?? '' }}
                        </td>
                        <td class="align-middle">
                            @php($rpd = $account->rpd ?? 0)
                            @if($rpd == 0)
                                <div class="font-weight-bold text-danger"><s>{{ $rpd }}</s></div>
                            @elseif($rpd < 10)
                                <div class="font-weight-bold text-danger blink">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $rpd }}
                                </div>
                            @elseif($rpd < 50)
                                <div class="font-weight-bold text-warning">{{ $rpd }}</div>
                            @elseif($rpd < 100)
                                <div class="font-weight-bold text-secondary">{{ $rpd }}</div>
                            @elseif($rpd < 200)
                                <div class="font-weight-bold text-info">{{ $rpd }}</div>
                            @else
                                <div class="font-weight-bold text-success">{{ $rpd }}</div>
                            @endif
                        </td>
                        <td class="align-middle">
                            <div class="badge border">
                                {{ Str::limit(base64_encode($account->token), 20, '...') }}
                            </div>
                        </td>
                        <td class="align-middle">
                            @can('accounts.edit')
                                <a href="{{ route('accounts.edit', $account->id) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-pen"></i>
                                </a>
                            @endcan
                            @can('accounts.delete')
                                <form action="{{ route('accounts.destroy', $account->id) }}" method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Ushbu akkauntni o‘chirishni xohlaysizmi?')">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3 text-light"></i><br>
                            <h5 class="font-weight-light">Hech qanday ma'lumot topilmadi.</h5>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->total() > 15)
            <div class="card-footer clearfix bg-white border-top">
                <ul class="pagination pagination-sm m-0 float-right" id="pagination-container">
                    {{ $accounts->links('pagination::bootstrap-4') }}
                </ul>
            </div>
        @endif
    </div>
@endsection
