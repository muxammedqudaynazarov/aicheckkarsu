@extends('layouts.app')

@section('title', 'Imtihonlar arxivi')
@section('content')
    <div class="row">
        <div class="col-12">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-custom">
                    <li class="breadcrumb-item">
                        <a href="{{ route('drive.index') }}">O‘quv yillari</a>
                    </li>
                    @foreach($breadcrumbs as $crumb)
                        @if($loop->last)
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb['name'] }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body @if(count($files) > 0) p-0 @else p-4 @endif">
                    @if(count($folders) > 0)
                        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3">
                            @foreach($folders as $folder)
                                <div class="col">
                                    <a href="{{ $folder['url'] }}" class="text-decoration-none">
                                        <div class="card shadow-sm border folder-card h-100 mb-0">
                                            <div class="card-body text-center p-3">
                                                <i class="fas fa-folder fa-3x folder-icon mb-2"></i>
                                                <div class="folder-name" title="{{ $folder['name'] }}">
                                                    {{ $folder['name'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(count($files) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover file-table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Fan nomi</th>
                                    <th>Imtihon guruhi</th>
                                    <th class="text-center">Talabalar</th>
                                    <th class="text-center">Fayllar</th>
                                    <th>Sana</th>
                                    <th style="width: 5%"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($files as $lesson)
                                    <tr>
                                        <td class="align-middle text-muted">{{ $loop->iteration }}</td>
                                        <td class="align-middle font-weight-bold text-dark">
                                            <a
                                                href="{{ route('drive.lesson', ['year' => $url['year'], 'department' => $url['department'], 'specialty' => $url['specialty'], 'level' => $url['level'], 'lesson' =>$lesson->id]) }}">
                                                {{ $lesson->name }}
                                            </a>
                                        </td>
                                        <td class="align-middle">{{ $lesson->group->name }}</td>
                                        <td class="align-middle text-center">
                                            {{ $lesson->group->students->count() ?? 0 }}
                                        </td>
                                        <td class="align-middle text-center">
                                            {{ $lesson->files->where('participant', '0')->count() ?? 0 }}
                                        </td>
                                        <td class="align-middle text-muted">{{ $lesson->exam_date->format('d.m.Y') }}</td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('lessons.statement', $lesson->id) }}"
                                               class="btn btn-outline-primary btn-sm rounded shadow-sm"
                                               title="Yuklab olish">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if(count($folders) == 0 && count($files) == 0)
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted">
                                <i class="fas fa-folder-open fa-4x" style="opacity: 0.3;"></i>
                            </div>
                            <h5 class="text-muted small">
                                Arxiv ma’lumotlari topilmadi
                            </h5>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
