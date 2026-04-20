@extends('layouts.app')

@section('title', 'Bosh sahifa')
@section('page_title', 'Boshqaruv paneli (Dashboard)')
@section('breadcrumb')
    <li class="breadcrumb-item active">Bosh sahifa</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body text-center text-danger">
                        Sizning rolingiz taqdiqlanishi kutilmoqda...
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
