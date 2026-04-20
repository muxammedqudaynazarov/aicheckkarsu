@extends('layouts.app')
@section('content')
    <form action="{{ route('accounts.update', $account->id) }}" method="POST" autocomplete="off">
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
                    <div class="col-md-8 mb-3">
                        <label for="email" class="small mb-0">Elektron pochta (E-mail)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" maxlength="84" readonly
                               value="{{ old('email', $account->email) }}" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="ai_model" class="small mb-0">AI model</label>
                        <input type="text" class="form-control @error('ai_model') is-invalid @enderror" name="ai_model"
                               id="ai_model" value="{{ old('ai_model', $account->model) }}" required>
                        @error('ai_model')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="rpd_def" class="small mb-0">Kunlik RPD</label>
                        <input type="number" class="form-control @error('rpd_def') is-invalid @enderror"
                               id="rpd_def" min="1" value="{{ old('rpd_def', $account->rpd_default) }}" name="rpd_def"
                               required>
                        @error('rpd_def')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="mb-2">
                            <label for="token" class="small mb-0">API token</label>
                            <textarea class="form-control @error('token') is-invalid @enderror"
                                      name="token" id="token" rows="4"
                                      required>{{ old('token', $account->token) }}</textarea>
                            @error('token')
                            <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="department" class="small mb-0">Tokenni foydalanuvchiga biriktirish</label>
                            <select name="user_id" id="user_id" class="form-control" style="width: 100%;">
                                <option @if(is_null($account->user_id)) selected @endif></option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @if($user->id == $account->user_id) selected @endif>
                                        {{ json_decode($user->name)->full_name }}
                                    </option>
                                @endforeach
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
