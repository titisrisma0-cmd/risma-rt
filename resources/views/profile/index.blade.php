@extends('layouts.app')

@section('title', 'Profil Pengguna - RW SMART')

@section('content')
<div class="content-container">
    {{-- ==================== PAGE HEADER ==================== --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon" style="background: rgba(14, 122, 75, 0.1); color: #0e7a4b; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.35rem;">
                <i class="fas fa-user-gear"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="font-size: 1.4rem; font-weight: 700; color: #1e293b;">Profil Pengguna</h1>
                    @if($user->isSuperAdmin())
                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                            <i class="fas fa-crown me-1"></i> SUPER ADMIN
                        </span>
                    @else
                        <span class="badge-rt-pill">{{ $user->getRoleDisplayName() }}</span>
                    @endif
                </div>
                <p class="page-subtitle mb-0 text-muted small">Kelola informasi identitas diri, kependudukan, alamat domisili, dan keamanan akun Anda</p>
            </div>
        </div>
        <div class="page-header-actions d-flex flex-wrap gap-2">
            @if($user->isSuperAdmin())
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                    <i class="fas fa-arrows-rotate me-1.5"></i> Beralih Peran (Ganti Role)
                </button>
                <a href="{{ route('superadmin.dashboard') }}" class="btn btn-success fw-semibold rounded-3 shadow-xs">
                    <i class="fas fa-shield-halved me-1.5"></i> Super Admin Dashboard
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-3">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- ==================== SUPER ADMIN EXCLUSIVE BADGE BANNER ==================== --}}
    @if($user->isSuperAdmin())
        <div class="card hero-surface border-0 rounded-4 shadow-sm mb-4 text-white overflow-hidden" data-hero
             style="background: linear-gradient(135deg, #0e7a4b 0%, #177245 60%, #0a4f30 100%);">
            <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle bg-warning text-dark shadow-sm flex-shrink-0">
                        <i class="fas fa-crown fa-2x"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1">PENGEMBANG SISTEM</span>
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1">ROOT PRIVILEGES</span>
                        </div>
                        <h5 class="fw-bold text-white mb-1">Titis Rismawati &bull; Hak Akses Tertinggi Aktif</h5>
                        <p class="text-white-50 mb-0 small">
                            Akun ini memiliki hak akses penuh ke seluruh master wilayah (RW & seluruh unit RT) dan dapat menguji pengalaman sistem sebagai RT/RW atau Warga secara dinamis.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                        <i class="fas fa-arrows-rotate me-1"></i> Ganti Role Simulasi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== ALERTS ==================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between mb-4 shadow-xs rounded-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-lg me-2 text-success"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-xs rounded-3" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="fas fa-exclamation-circle fa-lg mt-1 text-danger"></i>
                <div>
                    <strong class="d-block mb-1">Terdapat kesalahan pada formulir:</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ==================== MAIN PROFILE LAYOUT ==================== --}}
    <div class="row g-4">
        <!-- Sidebar Kiri: Kartu Identitas & Foto -->
        <div class="col-12 col-lg-4">
            {{-- Foto & Nama --}}
            <div class="card border-0 rounded-4 shadow-sm mb-4 text-center p-4 bg-white">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    @php
                        if ($user->profile && $user->profile->foto_base64) {
                            $fotoUrl = $user->profile->foto_base64;
                        } else {
                            $fotoUrl = 'https://ui-avatars.com/api/?background=0e7a4b&color=fff&rounded=true&size=160&name=' . urlencode($user->name);
                        }
                    @endphp
                    <img id="profileImage" 
                         src="{{ $fotoUrl }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-circle border border-4 border-success-subtle shadow-sm"
                         style="width: 140px; height: 140px; object-fit: cover;">
                    <button type="button" class="btn btn-success position-absolute bottom-0 end-0 rounded-circle p-2 shadow-sm text-white" 
                            data-bs-toggle="modal" data-bs-target="#uploadFotoModal" title="Ubah Foto Profil"
                            style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                <p class="text-secondary small mb-2 font-monospace">{{ $user->email }}</p>

                <div class="mb-3">
                    @if($user->isSuperAdmin())
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-shield-halved me-1"></i> SUPER ADMIN
                        </span>
                    @else
                        <span class="badge bg-success bg-opacity-15 text-success px-3 py-1.5 rounded-pill fw-semibold">
                            {{ $user->getRoleDisplayName() }}
                        </span>
                    @endif
                </div>

                @if($user->isSuperAdmin())
                    <button type="button" class="btn btn-outline-warning text-dark fw-bold w-100 rounded-3 mb-3" data-bs-toggle="modal" data-bs-target="#modalSwitchRole">
                        <i class="fas fa-arrows-rotate me-1.5"></i> Beralih Peran (Ganti Role)
                    </button>
                @endif
                
                <div class="row g-2 pt-3 border-top text-start">
                    <div class="col-6">
                        <div class="p-2.5 border rounded-3 bg-light">
                            <small class="text-secondary d-block" style="font-size: 0.75rem;">Status Akun</small>
                            <span class="badge bg-success text-white mt-1">Aktif</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 border rounded-3 bg-light">
                            <small class="text-secondary d-block" style="font-size: 0.75rem;">Terdaftar Sejak</small>
                            <span class="text-dark fw-bold small mt-1 d-block">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ringkasan Kependudukan -->
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-address-card text-success me-2"></i>Ringkasan Identitas
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <small class="text-secondary">Nomor Induk Kependudukan (NIK)</small>
                        <strong class="text-dark font-monospace">{{ optional($user->profile)->nik ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <small class="text-secondary">Nomor Kartu Keluarga (KK)</small>
                        <strong class="text-dark font-monospace">{{ optional($user->profile)->no_kk ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <small class="text-secondary">WhatsApp / Handphone</small>
                        <strong class="text-dark">{{ optional($user->profile)->no_hp ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-secondary">Wilayah Domisili</small>
                        <strong class="text-dark">
                            RT {{ optional($user->profile)->rt ?? ($user->getRtKode() ?? '-') }} / RW {{ optional($user->profile)->rw ?? '01' }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Lengkap Profile (Tabs) -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-white p-2 border-bottom">
                    <ul class="nav nav-pills gap-1" id="profileTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-3 px-3 py-2 fw-semibold" id="biodata-tab" data-bs-toggle="tab" data-bs-target="#biodata" type="button" role="tab">
                                <i class="fas fa-user me-1.5"></i> Biodata Diri
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 px-3 py-2 fw-semibold" id="alamat-tab" data-bs-toggle="tab" data-bs-target="#alamat" type="button" role="tab">
                                <i class="fas fa-map-marker-alt me-1.5"></i> Alamat Domisili
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 px-3 py-2 fw-semibold" id="keluarga-tab" data-bs-toggle="tab" data-bs-target="#keluarga" type="button" role="tab">
                                <i class="fas fa-users me-1.5"></i> Data Keluarga
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 px-3 py-2 fw-semibold" id="keamanan-tab" data-bs-toggle="tab" data-bs-target="#keamanan" type="button" role="tab">
                                <i class="fas fa-shield-halved me-1.5"></i> Keamanan Akun
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="tab-content" id="profileTabContent">
                            <!-- Tab 1: Biodata -->
                            <div class="tab-pane fade show active" id="biodata" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Alamat Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Nomor Induk Kependudukan (NIK)</label>
                                        <input type="text" name="nik" class="form-control rounded-3 font-monospace" value="{{ old('nik', optional($user->profile)->nik) }}" placeholder="16 digit NIK">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Nomor Kartu Keluarga (KK)</label>
                                        <input type="text" name="no_kk" class="form-control rounded-3 font-monospace" value="{{ old('no_kk', optional($user->profile)->no_kk) }}" placeholder="16 digit No KK">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="form-control rounded-3" value="{{ old('tempat_lahir', optional($user->profile)->tempat_lahir) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control rounded-3" value="{{ old('tanggal_lahir', optional($user->profile)->tanggal_lahir ? \Carbon\Carbon::parse($user->profile->tanggal_lahir)->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select rounded-3">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" {{ old('jenis_kelamin', optional($user->profile)->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="P" {{ old('jenis_kelamin', optional($user->profile)->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Agama</label>
                                        <select name="agama" class="form-select rounded-3">
                                            <option value="">Pilih Agama</option>
                                            @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                                <option value="{{ $agm }}" {{ old('agama', optional($user->profile)->agama) == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">No. Handphone / WhatsApp</label>
                                        <input type="text" name="no_hp" class="form-control rounded-3" value="{{ old('no_hp', optional($user->profile)->no_hp) }}" placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Pekerjaan</label>
                                        <input type="text" name="pekerjaan" class="form-control rounded-3" value="{{ old('pekerjaan', optional($user->profile)->pekerjaan) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Status Perkawinan</label>
                                        <select name="status_perkawinan" class="form-select rounded-3">
                                            <option value="">Pilih Status</option>
                                            @foreach(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'] as $sp)
                                                <option value="{{ $sp }}" {{ old('status_perkawinan', optional($user->profile)->status_perkawinan) == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Pendidikan Terakhir</label>
                                        <select name="pendidikan" class="form-select rounded-3">
                                            <option value="">Pilih Pendidikan</option>
                                            @foreach(['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2', 'S3'] as $pdd)
                                                <option value="{{ $pdd }}" {{ old('pendidikan', optional($user->profile)->pendidikan) == $pdd ? 'selected' : '' }}>{{ $pdd }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab 2: Alamat -->
                            <div class="tab-pane fade" id="alamat" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Alamat Jalan / Rumah</label>
                                    <textarea name="alamat" rows="3" class="form-control rounded-3" placeholder="Nama jalan, nomor rumah, blok...">{{ old('alamat', optional($user->profile)->alamat) }}</textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-secondary">RT</label>
                                        <input type="text" name="rt" class="form-control rounded-3" value="{{ old('rt', optional($user->profile)->rt ?? ($user->getRtKode() ?? '')) }}" placeholder="Contoh: 01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-secondary">RW</label>
                                        <input type="text" name="rw" class="form-control rounded-3" value="{{ old('rw', optional($user->profile)->rw ?? '01') }}" placeholder="Contoh: 01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-secondary">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control rounded-3" value="{{ old('kelurahan', optional($user->profile)->kelurahan) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control rounded-3" value="{{ old('kecamatan', optional($user->profile)->kecamatan) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Kota / Kabupaten</label>
                                        <input type="text" name="kota" class="form-control rounded-3" value="{{ old('kota', optional($user->profile)->kota) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab 3: Keluarga -->
                            <div class="tab-pane fade" id="keluarga" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Nama Ayah Kandung</label>
                                        <input type="text" name="nama_ayah" class="form-control rounded-3" value="{{ old('nama_ayah', optional($user->profile)->nama_ayah) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Nama Ibu Kandung</label>
                                        <input type="text" name="nama_ibu" class="form-control rounded-3" value="{{ old('nama_ibu', optional($user->profile)->nama_ibu) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab 4: Keamanan -->
                            <div class="tab-pane fade" id="keamanan" role="tabpanel">
                                <div class="alert alert-warning border-0 rounded-3 mb-3 small d-flex align-items-center gap-2">
                                    <i class="fas fa-key text-warning fa-lg"></i>
                                    <div>Kosongkan isian password jika Anda tidak bermaksud mengganti kata sandi.</div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Password Baru</label>
                                        <input type="password" name="password" class="form-control rounded-3" placeholder="Minimal 8 karakter">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-secondary">Konfirmasi Password Baru</label>
                                        <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success px-4 rounded-3 fw-semibold">
                                <i class="fas fa-save me-1.5"></i> Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak valid. Gunakan JPG, PNG, atau WEBP.');
        input.value = '';
        preview.style.display = 'none';
        filenameText.style.display = 'none';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 2MB.');
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
    @if(isset($errors) && $errors->has('avatar'))
        var uploadModalEl = document.getElementById('uploadFotoModal');
        if (uploadModalEl) {
            var uploadModal = new bootstrap.Modal(uploadModalEl);
            uploadModal.show();
        }
    @endif
});
</script>
@endsection

@push('modals')
<!-- Modal Upload Foto Profil (outside main container to avoid stacking context trapping) -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1" aria-labelledby="uploadFotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark mb-0" id="uploadFotoModalLabel">
                    <i class="fas fa-camera text-success me-2"></i>Upload Foto Profil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.update.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body text-center p-4">
                    <img id="previewFoto" src="#" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; display: none;" class="border border-3 border-success-subtle shadow-sm">
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-semibold text-secondary">Pilih Berkas Foto Profil</label>
                        <input type="file" name="avatar" id="avatarInput" class="form-control rounded-3" accept=".jpg,.jpeg,.png,.gif,.webp" onchange="previewImage(event)" required>
                        <small class="text-secondary mt-1 d-block">Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 2MB.</small>
                        <div id="avatarFilename" class="small text-success fw-semibold mt-2" style="display:none;"></div>
                        @error('avatar')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-light rounded-bottom-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 rounded-3 fw-semibold">Simpan Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
