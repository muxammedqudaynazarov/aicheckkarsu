@extends('layouts.app')

@section('content')
    <form action="{{ route('accounts.store') }}" method="POST" autocomplete="off">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="email" class="small mb-0">Elektron pochta (E-mail)</label>
                        <input type="email" class="form-control" name="email" id="email" maxlength="84" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="ai_model" class="small mb-0">AI model</label>
                        <input type="text" class="form-control" name="ai_model"
                               id="ai_model" value="gemini-3.1-pro-preview" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="rpd_def" class="small mb-0">Kunlik RPD</label>
                        <input type="number" class="form-control" name="rpd_def"
                               id="rpd_def" min="1" value="1" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <label for="token" class="small mb-0">API token</label>
                    <textarea class="form-control" name="token" id="token" required></textarea>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-flat btn-success btn-sm">
                    <i class="fas fa-check mr-1"></i> API yaratish
                </button>
            </div>
        </div>
    </form>
@endsection
