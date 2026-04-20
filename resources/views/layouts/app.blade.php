<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AIcheck.KarSU.UZ')</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    @yield('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="{{ auth()->user()->image }}"
                         class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline font-weight-bold">
                        {{ json_decode(auth()->user()->name)->short_name }}
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right rounded-lg shadow border-0">
                    <li class="user-header bg-primary rounded-top">
                        <img src="{{ auth()->user()->image }}"
                             class="img-circle elevation-2" alt="User Image">
                        <p>
                            {{ json_decode(auth()->user()->name)->short_name }}
                            <small>Ro‘yxatdan o‘tgan: {{ auth()->user()->created_at->format('d.m.Y') }}</small>
                        </p>
                    </li>
                    <li class="user-footer bg-light p-2">
                        <div class="btn-group d-flex w-100" role="group">
                            <a href="#" class="btn btn-default btn-flat flex-fill border-right">
                                <i class="fas fa-user mr-1"></i> Profil
                            </a>

                            <a href="#" class="btn btn-default btn-flat flex-fill text-danger"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-1"></i> Chiqish
                            </a>
                        </div>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('home') ?? '#' }}" class="brand-link">
            <img src="{{ asset('dist/img/logo.png') }}" alt="AIcheck Logo"
                 class="brand-image img-circle elevation-3">
            <span class="brand-text font-weight-light">AIcheck KarSU</span>
        </a>

        <div class="sidebar" style="font-size: 13px">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">

                    <li class="nav-item">
                        <a href="{{ route('home') ?? '#' }}"
                           class="nav-link {{ request()->is('home') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt" style="font-size: 12px"></i>
                            <p>Asosiy sahifa</p>
                        </a>
                    </li>
                    @can('info.view')
                        <li class="nav-item {{ request()->is('home/hemis*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-database" style="font-size: 12px"></i>
                                <p>
                                    Ma’lumotlar
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="background: rgb(69, 69, 69);">
                                @can('info.faculties.view')
                                    <li class="nav-item">
                                        <a href="{{ route('departments.index') }}"
                                           class="nav-link {{ request()->is('home/hemis/departments*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size: 12px"></i>
                                            <p>Fakultetlar</p>
                                        </a>
                                    </li>
                                @endcan
                                @can('info.curricula.view')
                                    <li class="nav-item">
                                        <a href="{{ route('curricula.index') }}"
                                           class="nav-link {{ request()->is('home/hemis/curricula*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size: 12px"></i>
                                            <p>O‘quv rejalar</p>
                                        </a>
                                    </li>
                                @endcan
                                @can('info.specialties.view')
                                    <li class="nav-item">
                                        <a href="{{ route('specialties.index') }}"
                                           class="nav-link {{ request()->is('home/hemis/specialties*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size: 12px"></i>
                                            <p>Mutaxassisliklar</p>
                                        </a>
                                    </li>
                                @endcan
                                @can('info.groups.view')
                                    <li class="nav-item">
                                        <a href="{{ route('groups.index') }}"
                                           class="nav-link {{ request()->is('home/hemis/groups*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon" style="font-size: 12px"></i>
                                            <p>Akademik guruhlar</p>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan
                    @can('lessons.view')
                        @php($lessons_cnt = \App\Models\Lesson::all()->count())
                        <li class="nav-item">
                            <a href="{{ route('lessons.index') }}"
                               class="nav-link {{ request()->is('home/lessons*') ? 'active': '' }}">
                                <i class="nav-icon fas fa-book" style="font-size: 12px"></i>
                                <p>
                                    Fanlar (imtihonlar)
                                    <span class="badge badge-success right">{{ $lessons_cnt }}</span>
                                </p>
                            </a>
                        </li>
                    @endcan
                    @can('reports.view')
                        <li class="nav-item">
                            <a href="{{ route('results.index') }}"
                               class="nav-link {{ request()->is('home/results*') ? 'active': '' }}">
                                <i class="nav-icon fas fa-file-invoice" style="font-size: 12px"></i>
                                <p>Vedmostlar</p>
                            </a>
                        </li>
                    @endcan
                    @can('archives.view')
                        <li class="nav-item">
                            <a href="{{ route('drive.index') }}"
                               class="nav-link {{ request()->is('home/drive*') ? 'active': '' }}">
                                <i class="nav-icon fas fa-archive" style="font-size: 12px"></i>
                                <p>Arxiv ma’lumotlar</p>
                            </a>
                        </li>
                    @endcan
                    @can('users.view')
                        <li class="nav-header mt-2" style="font-size: 11px">TIZIM SOZLAMALARI</li>
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}"
                               class="nav-link {{ request()->is('home/users*') ? 'active': '' }}">
                                <i class="nav-icon fas fa-users" style="font-size: 12px"></i>
                                <p>Foydalanuvchilar</p>
                            </a>
                        </li>
                    @endcan
                    @can('accounts.view')
                        <li class="nav-item">
                            <a href="{{ route('accounts.index') }}"
                               class="nav-link {{ request()->is('home/accounts*') ? 'active': '' }}">
                                <i class="nav-icon fas fa-user-shield" style="font-size: 12px"></i>
                                <p>Akkauntlar</p>
                            </a>
                        </li>
                    @endcan
                    @can('options.view')
                        <li class="nav-item">
                            <a href="{{ route('options.index') }}"
                               class="nav-link {{ request()->is('home/options*') ? 'active': '' }}">
                                <i class="nav-icon fas fa-cogs" style="font-size: 12px"></i>
                                <p>Sozlamalar</p>
                            </a>
                        </li>
                    @endcan
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">

        </section>

        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Versiya</b> 1.0.0
        </div>
        <strong>&copy; {{ date('Y') }} <a href="#">AIcheck.KarSU.UZ</a>.</strong> Barcha huquqlar himoyalangan.
    </footer>

</div>

<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

<script>
    $(document).ready(function () {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            "timeOut": "5000"
        };
        @if($errors->any())
        @foreach($errors->all() as $error)
        toastr.error("{!! addslashes($error) !!}");
        @endforeach
        @endif
        @if(isset($success))
        toastr.success("{!! addslashes($success) !!}");
        @endif
        @if(isset($error))
        toastr.error("{!! addslashes($error) !!}");
        @endif
        @if(session('success'))
        toastr.success("{!! addslashes(session('success')) !!}");
        @endif
        @if(session('error'))
        toastr.error("{!! addslashes(session('error')) !!}");
        @endif
    });
</script>

@stack('scripts')
@yield('scripting')
</body>
</html>
