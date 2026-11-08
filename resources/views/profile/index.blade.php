@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profil Pengguna</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-id-card text-success me-2"></i>Profil Saya</h1>
            <p class="page-subtitle">Kelola informasi identitas diri, data kependudukan, alamat, dan keamanan akun.</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-saas alert-saas-success d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-check-circle fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-saas alert-saas-danger d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <div>
                <strong>Data belum tersimpan:</strong>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Kiri -->
        <div class="col-12 col-md-4">
            <div class="saas-card mb-4 text-center p-4">
                <div class="position-relative d-inline-block mb-3">
                    @php
                        if ($user->profile && $user->profile->foto_base64) {
                            $fotoUrl = $user->profile->foto_base64;
                        } else {
                            $fotoUrl = 'https://ui-avatars.com/api/?background=147A45&color=fff&rounded=true&size=150&name=' . urlencode($user->name);
                        }
                    @endphp
                    <img id="profileImage" 
                         src="{{ $fotoUrl }}" 
                         alt="Profile Photo" 
                         class="rounded-circle border border-4 border-success-subtle shadow-sm"
                         style="width: 140px; height: 140px; object-fit: cover;">
                    <button type="button" class="btn btn-saas-primary position-absolute bottom-0 end-0 rounded-circle p-2 shadow" 
                            data-bs-toggle="modal" data-bs-target="#uploadFotoModal" title="Ubah Foto Profil">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                <p class="text-secondary small mb-2">{{ $user->email }}</p>
                <span class="badge badge-soft-success mb-3">{{ ucfirst($user->role->nama_role ?? 'Warga') }}</span>
                
                <div class="row g-2 mt-2 pt-3 border-top">
                    <div class="col-6">
                        <div class="p-2 border rounded-3 bg-light">
                            <small class="text-secondary d-block">Status Akun</small>
                            <h6 class="mb-0 mt-1">
                                @if($user->profile && $user->profile->status == 'aktif')
                                    <span class="badge badge-soft-success">Aktif</span>
                                @else
                                    <span class="badge badge-soft-danger">Nonaktif</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-3 bg-light">
                            <small class="text-secondary d-block">Bergabung Sejak</small>
                            <h6 class="text-dark fw-bold mb-0 mt-1">{{ $user->created_at->format('d/m/Y') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Cepat -->
            <div class="saas-card">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-success me-2"></i>Ringkasan Identitas</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <small class="text-secondary">Nomor Induk Kependudukan (NIK)</small>
                        <strong class="text-dark">{{ $user->profile->nik ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <small class="text-secondary">Nomor Kartu Keluarga (KK)</small>
                        <strong class="text-dark">{{ $user->profile->no_kk ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <small class="text-secondary">Nomor WhatsApp / HP</small>
                        <strong class="text-dark">{{ $user->profile->no_hp ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-secondary">Domisili RT / RW</small>
                        <strong class="text-dark">{{ $user->profile->rt ?? '-' }} / {{ $user->profile->rw ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Profile -->
        <div class="col-12 col-md-8">
            <div class="saas-card">
                <div class="card-header bg-white p-2 border-bottom">
                    <ul class="nav nav-tabs-saas">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#biodata">
                                <i class="fas fa-user me-1"></i> Biodata
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#alamat">
                                <i class="fas fa-map-marker-alt me-1"></i> Alamat
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#keluarga">
                                <i class="fas fa-users me-1"></i> Keluarga
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#akun">
                                <i class="fas fa-key me-1"></i> Keamanan
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="tab-content">
                            <!-- Tab Biodata -->
                            <div class="tab-pane fade show active" id="biodata">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control-saas" value="{{ $user->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Alamat Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control-saas" value="{{ $user->email }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">NIK</label>
                                        <input type="text" name="nik" class="form-control-saas" value="{{ $user->profile->nik ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Nomor Kartu Keluarga (KK)</label>
                                        <input type="text" name="no_kk" class="form-control-saas" value="{{ $user->profile->no_kk ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="form-control-saas" value="{{ $user->profile->tempat_lahir ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control-saas" value="{{ $user->profile->tanggal_lahir ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select-saas">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" {{ ($user->profile->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="P" {{ ($user->profile->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Agama</label>
                                        <select name="agama" class="form-select-saas">
                                            <option value="">Pilih Agama</option>
                                            <option value="Islam" {{ ($user->profile->agama ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                            <option value="Kristen" {{ ($user->profile->agama ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                            <option value="Katolik" {{ ($user->profile->agama ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                            <option value="Hindu" {{ ($user->profile->agama ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                            <option value="Buddha" {{ ($user->profile->agama ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">No. Telepon / WhatsApp</label>
                                        <input type="text" name="no_hp" class="form-control-saas" value="{{ $user->profile->no_hp ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Pekerjaan</label>
                                        <input type="text" name="pekerjaan" class="form-control-saas" value="{{ $user->profile->pekerjaan ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Status Perkawinan</label>
                                        <select name="status_perkawinan" class="form-select-saas">
                                            <option value="">Pilih Status</option>
                                            <option value="Belum Kawin" {{ ($user->profile->status_perkawinan ?? '') == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                                            <option value="Kawin" {{ ($user->profile->status_perkawinan ?? '') == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                                            <option value="Cerai" {{ ($user->profile->status_perkawinan ?? '') == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Pendidikan Terakhir</label>
                                        <select name="pendidikan" class="form-select-saas">
                                            <option value="">Pilih Pendidikan</option>
                                            <option value="SD" {{ ($user->profile->pendidikan ?? '') == 'SD' ? 'selected' : '' }}>SD</option>
                                            <option value="SMP" {{ ($user->profile->pendidikan ?? '') == 'SMP' ? 'selected' : '' }}>SMP</option>
                                            <option value="SMA" {{ ($user->profile->pendidikan ?? '') == 'SMA' ? 'selected' : '' }}>SMA</option>
                                            <option value="D3" {{ ($user->profile->pendidikan ?? '') == 'D3' ? 'selected' : '' }}>D3</option>
                                            <option value="S1" {{ ($user->profile->pendidikan ?? '') == 'S1' ? 'selected' : '' }}>S1</option>
                                            <option value="S2" {{ ($user->profile->pendidikan ?? '') == 'S2' ? 'selected' : '' }}>S2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Alamat -->
                            <div class="tab-pane fade" id="alamat">
                                <div class="mb-3">
                                    <label class="form-label-saas">Alamat Lengkap</label>
                                    <textarea name="alamat" rows="3" class="form-control-saas">{{ $user->profile->alamat ?? '' }}</textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label-saas">RT</label>
                                        <input type="text" name="rt" class="form-control-saas" value="{{ $user->profile->rt ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-saas">RW</label>
                                        <input type="text" name="rw" class="form-control-saas" value="{{ $user->profile->rw ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-saas">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control-saas" value="{{ $user->profile->kelurahan ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control-saas" value="{{ $user->profile->kecamatan ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Kota / Kabupaten</label>
                                        <input type="text" name="kota" class="form-control-saas" value="{{ $user->profile->kota ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Keluarga -->
                            <div class="tab-pane fade" id="keluarga">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Nama Ayah Kandung</label>
                                        <input type="text" name="nama_ayah" class="form-control-saas" value="{{ $user->profile->nama_ayah ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Nama Ibu Kandung</label>
                                        <input type="text" name="nama_ibu" class="form-control-saas" value="{{ $user->profile->nama_ibu ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Keamanan -->
                            <div class="tab-pane fade" id="akun">
                                <div class="alert alert-saas alert-saas-warning mb-3">
                                    <i class="fas fa-shield-alt me-1"></i> Kosongkan kolom password di bawah bila Anda tidak bermaksud mengubah kata sandi akun.
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Password Baru</label>
                                        <input type="password" name="password" class="form-control-saas" placeholder="Minimal 8 karakter">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-saas">Konfirmasi Password Baru</label>
                                        <input type="password" name="password_confirmation" class="form-control-saas" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-saas-primary px-4">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Foto Profil -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-camera text-success me-2"></i>Upload Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.update.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body text-center p-4">
                    <img id="previewFoto" src="#" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; display: none;" class="border border-3 border-success-subtle shadow-sm">
                    <div class="mb-3 text-start">
                        <label class="form-label-saas">Pilih File Foto Profil</label>
                        <input type="file" name="avatar" id="avatarInput" class="form-control-saas" accept=".jpg,.jpeg,.png,.gif" onchange="previewImage(event)" required>
                        <small class="text-secondary mt-1 d-block">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</small>
                        <div id="avatarFilename" class="small text-success fw-semibold mt-2" style="display:none;"></div>
                        @error('avatar')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-saas" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-saas-primary">Simpan Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const file = input.files && input.files[0];
    const preview = document.getElementById('previewFoto');
    const filenameText = document.getElementById('avatarFilename');

    if (!file) {
        preview.style.display = 'none';
        filenameText.style.display = 'none';
        preview.src = '#';
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        window.showToast('Format file tidak valid. Gunakan JPG, PNG, atau GIF.', 'error');
        input.value = '';
        preview.style.display = 'none';
        filenameText.style.display = 'none';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        window.showToast('Ukuran file terlalu besar. Maksimal 2MB.', 'error');
        input.value = '';
        preview.style.display = 'none';
        filenameText.style.display = 'none';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'inline-block';
        filenameText.textContent = 'Nama file: ' + file.name;
        filenameText.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', function() {
    @if($errors->has('avatar'))
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadFotoModal'));
        uploadModal.show();
    @endif
});
</script>
@endsection
