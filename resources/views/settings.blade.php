@extends('layouts.app')

@section('title', 'Shaxsiy sozlamalar')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Profil sozlamalari</h3>
                </div>
                <form action="{{ route('options.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body p-4">
                        <div class="text-center mb-4 position-relative">
                            <input type="file" id="avatar" name="avatar" class="d-none"
                                   accept="image/jpeg, image/png, image/webp">
                            <div class="d-inline-block position-relative">
                                <div id="avatar-wrapper" style="cursor: pointer;"
                                     onclick="document.getElementById('avatar').click()" title="Rasmni o‘zgartirish">
                                    <div id="avatar-preview-default"
                                         class="img-circle elevation-2 justify-content-center align-items-center bg-light text-muted {{ $user->image ? 'd-none' : 'd-flex' }}"
                                         style="width: 120px; height: 120px; font-size: 3rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <img id="avatar-preview" src="{{ $user->image ?? '' }}" alt="User avatar"
                                         class="img-circle elevation-2 {{ $user->image ? '' : 'd-none' }}"
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                    <div
                                        class="img-circle d-flex justify-content-center align-items-center position-absolute"
                                        style="top: 0; left: 0; width: 120px; height: 120px; background: rgba(0,0,0,0.4); opacity: 0; transition: 0.2s; color: #fff;"
                                        onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                        <i class="fas fa-camera fa-2x"></i>
                                    </div>
                                </div>
                                <button type="button"
                                        class="btn btn-danger btn-sm rounded-circle shadow-sm position-absolute {{ $user->image ? '' : 'd-none' }}"
                                        id="btn-delete-avatar" style="bottom: 0; right: -5px; z-index: 10;"
                                        title="Rasmni o‘chirish">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="delete_avatar" id="delete_avatar_input" value="0">
                        <hr class="my-4 text-muted">
                        <div class="form-group">
                            <label for="per_page">Sahifadagi elementlar soni</label>
                            <select name="per_page" id="per_page" class="form-control custom-select">
                                @for($i = 10; $i <= 50; $i+=5)
                                    <option
                                        value="{{ $i }}" {{ old('per_page', $user->per_page) == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-right bg-white">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Saqlash
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Rasm tanlanganda jonli ko'rsatish (Live Preview)
            $('#avatar').on('change', function (event) {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        // Eski holatlarni (duplikatsiyani) oldini olish uchun faqat classlarni o'zgartiramiz
                        $('#avatar-preview-default').removeClass('d-flex').addClass('d-none');
                        $('#avatar-preview').attr('src', e.target.result).removeClass('d-none');

                        // O'chirish tugmasini ko'rsatish
                        $('#btn-delete-avatar').removeClass('d-none');

                        // O'chirish komandasini bekor qilish
                        $('#delete_avatar_input').val('0');
                    }

                    reader.readAsDataURL(this.files[0]);
                }
            });

            // O'chirish tugmasi bosilganda
            $('#btn-delete-avatar').on('click', function (e) {
                e.stopPropagation(); // Rasmni bosish bilan chalkashib fayl oynasi ochilib ketmasligi uchun

                // Inputni tozalaymiz
                $('#avatar').val('');

                // Rasmni yashirib, original default ikonkani qaytaramiz
                $('#avatar-preview').addClass('d-none').attr('src', '');
                $('#avatar-preview-default').removeClass('d-none').addClass('d-flex');

                // Axlat qutisi tugmasini yashiramiz
                $(this).addClass('d-none');

                // Backendga rasmni o'chirish signalini beramiz
                $('#delete_avatar_input').val('1');
            });
        });
    </script>
@endpush
