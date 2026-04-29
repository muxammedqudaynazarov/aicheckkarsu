@extends('layouts.app')
@section('content')
    <form action="{{ route('users.update', $user->id) }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mt-1 font-weight-bold">
                    API akkauntni tahrirlash
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="surname" class="small mb-0">Familiyasi</label>
                        <input type="text" class="form-control" readonly
                               value="{{ json_decode($user->name)->surname }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="firstname" class="small mb-0">Ismi</label>
                        <input type="text" class="form-control" readonly
                               value="{{ json_decode($user->name)->firstname }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="patronymic" class="small mb-0">Sharifi</label>
                        <input type="text" class="form-control" readonly
                               value="{{ json_decode($user->name)->patronymic }}">
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            <label for="department" class="small mb-0">Tuzilma yoki bo‘limni tanlang</label>
                            <select name="department" id="department"
                                    class="form-control select2 @error('department') is-invalid @enderror"
                                    data-placeholder="Bo‘limni tanlang" style="width: 100%;">
                                <option value=""></option>
                                @php
                                    $structureTitles = [
                                        '16' => 'Rahbariyat',
                                        '11' => 'Fakultetlar',
                                        '14' => 'Boshqarmalar',
                                        '13' => 'Bo‘limlar',
                                        '15' => 'Markazlar',
                                        '10' => 'Boshqa bo‘limlar'
                                    ];
                                @endphp
                                @foreach($departments as $structureCode => $items)
                                    <optgroup label="{{ $structureTitles[$structureCode] ?? 'Noma’lum tuzilma' }}">
                                        @foreach($items as $dept)
                                            <option value="{{ $dept->id }}"
                                                {{ (old('department', $user->department_id) == $dept->id) ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="department" class="small mb-0">Foydalanuvchi ro‘lini tanlang</label>
                            <select name="pos" id="pos" class="form-control" style="width: 100%;"
                                    @if($user->pos == 'super_admin') disabled @endif>
                                <option disabled></option>
                                <option value="user" @if($user->pos == 'user') selected @endif>Foydalanuvchi</option>
                                <option value="uploader" @if($user->pos == 'uploader') selected @endif>Yuklovchi</option>
                                <option value="moder" @if($user->pos == 'moder') selected @endif>Tekshiruvchi</option>
                                <option value="admin" @if($user->pos == 'admin') selected @endif>Administrator</option>
                                @if($user->pos == 'super_admin')
                                    <option value="admin" selected>Super admin</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-flat btn-danger btn-sm">
                    <i class="fas fa-save mr-1"></i> Saqlash
                </button>
            </div>
        </div>
    </form>
@endsection
