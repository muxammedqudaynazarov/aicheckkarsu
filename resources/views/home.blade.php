@extends('layouts.app')

@section('title', 'Bosh sahifa')
@section('page_title', 'Boshqaruv paneli (Dashboard)')
@section('breadcrumb')
    <li class="breadcrumb-item active">Bosh sahifa</li>
@endsection

@section('content')
    <div class="container-fluid">
        @if (session('status'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow-sm rounded-lg">
                    <div class="inner">
                        <h3>{{ $counters['lessons'] }}</h3>
                        <p>Jami imtihonlar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <a href="{{ route('lessons.index') ?? '#' }}" class="small-box-footer" style="font-size: 12px">
                        <i class="fas fa-arrow-circle-right" style="font-size: 11px; vertical-align: center"></i>
                        Fanlar ro‘yxati
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow-sm rounded-lg">
                    <div class="inner">
                        <h3>
                            @if($counters['files'] > 50)
                                {{ floor($counters['files'] / 50) * 50 }}<sup style="font-size: 20px">+</sup>
                            @else
                                {{ $counters['files'] }}<sup style="font-size: 20px"></sup>
                            @endif
                        </h3>
                        <p>AI tekshirgan fayllar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <a href="{{ route('lessons.index') ?? '#' }}" class="small-box-footer" style="font-size: 12px">
                        <i class="fas fa-arrow-circle-right" style="font-size: 11px; vertical-align: center"></i>
                        Fayllar ro‘yxati
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning shadow-sm rounded-lg">
                    <div class="inner">
                        <h3>
                            @if($counters['students'] > 50)
                                {{ floor($counters['students'] / 50) * 50 }}<sup style="font-size: 20px">+</sup>
                            @else
                                {{ $counters['students'] }}
                            @endif
                        </h3>
                        <p>Talabalar soni</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="{{ route('lessons.index') ?? '#' }}" class="small-box-footer" style="font-size: 12px">
                        <i class="fas fa-arrow-circle-right" style="font-size: 11px; vertical-align: center"></i>
                        Talabalar ro‘yxati
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger shadow-sm rounded-lg">
                    <div class="inner">
                        <h3>{{ $counters['today'] }}</h3>
                        <p>Bugungi so‘rovlar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <a href="{{ route('lessons.index') ?? '#' }}" class="small-box-footer" style="font-size: 12px">
                        <i class="fas fa-arrow-circle-right" style="font-size: 11px; vertical-align: center"></i>
                        Vedmostlar ro‘yxati
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            So‘ngi tekshirilgan imtihonlar
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table text-center m-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Imtihon (fan nomi)</th>
                                    <th>Imtihon sanasi</th>
                                    <th>Holati</th>
                                    <th>O‘zlashtirish ko‘rsatkichi</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($lessons as $lesson)
                                    @php
                                        $kpi = 0;
                                        foreach ($lesson->files as $file) $kpi += $file->overall;
                                        $kpi /= $lesson->group->students->count();
                                    @endphp
                                    <tr>
                                        <td class="align-middle">#{{ $lesson->id }}</td>
                                        <th class="align-middle" style="text-align: left">
                                            {{ $lesson->name }} ({{ $lesson->level->name }})
                                        </th>
                                        <td class="align-middle">{{ $lesson->exam_date->format('d.m.Y') }}</td>
                                        <td class="align-middle">
                                            @if($lesson->status == '0')
                                                <span class="badge badge-info">Yangi yaratilgan</span>
                                            @elseif($lesson->status == '1')
                                                <span class="badge badge-secondary">Tekshirilmoqda</span>
                                            @elseif($lesson->status == '2')
                                                <span class="badge badge-success">Yakunlangan</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-success">
                                                {{ number_format($kpi, 1) }}%
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
                                        <td class="text-center text-danger small">
                                            Imtihonlar yoq
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-info shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-info-circle mr-1"></i> Tizim haqida
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="text-center mb-4 mt-2">
                            <img class="img-fluid rounded-circle shadow border" src="{{ asset('dist/img/logo.png') }}"
                                 alt="{{ $options['title'] }}"
                                 style="width: 100px; height: 100px; object-fit: contain; background: #fff;">
                        </div>
                        <h4 class="text-center font-weight-bold">{{ $options['title'] }}</h4>
                        <p class="text-muted text-center text-sm px-3">
                            {{ $options['description'] }}
                        </p>

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item px-3">
                                <b>Tizim versiyasi</b> <a class="float-right text-dark">{{ $options['version'] }}</a>
                            </li>
                            <li class="list-group-item px-3">
                                <b>Asoschi</b> <a class="float-right text-dark">{{ $options['creator'] }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
