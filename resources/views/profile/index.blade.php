@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-white">
                <i class="fas fa-id-card"></i> Profil Saya
            </h2>
            <p class="text-white-50">Kelola informasi profil dan data diri Anda</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- Sidebar Kiri -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        @php
                            if ($user->profile && $user->profile->foto_base64) {
                                $fotoUrl = $user->profile->foto_base64;
                            } else {
                                $fotoUrl = 'https://ui-avatars.com/api/?background=3b82f6&color=fff&rounded=true&size=150&name=' . urlencode($user->name);
                            }
                        @endphp
                        <img id="profileImage" 
                             src="{{ $fotoUrl }}" 
                             alt="Profile Photo" 
                             class="rounded-circle border border-4 border-primary"
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                data-bs-toggle="modal" data-bs-target="#uploadFotoModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <h4 class="text-white mb-1">{{ $user->name }}</h4>
                    <p class="text-secondary mb-2">{{ $user->email }}</p>
                    <span class="badge bg-primary mb-3">{{ ucfirst($user->role->nama_role ?? 'Warga') }}</span>
                    
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="border rounded p-2" style="border-color: #334155;">
                                <small class="text-secondary">Status</small>
                                <h6 class="mb-0">
                                    @if($user->profile && $user->profile->status == 'aktif')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2" style="border-color: #334155;">
                                <small class="text-secondary">Bergabung</small>
                                <h6 class="text-white mb-0">{{ $user->created_at->format('d/m/Y') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Cepat -->
            <div class="card mt-4">
                <div class="card-header bg-primary">
                    <h6 class="mb-0 text-white"><i class="fas fa-info-circle"></i> Info Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-secondary">NIK</small>
                        <strong class="text-white">{{ $user->profile->nik ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-secondary">No. KK</small>
                        <strong class="text-white">{{ $user->profile->no_kk ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-secondary">No. HP</small>
                        <strong class="text-white">{{ $user->profile->no_hp ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-secondary">RT/RW</small>
                        <strong class="text-white">{{ $user->profile->rt ?? '-' }}/{{ $user->profile->rw ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Profile -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#biodata">
                                <i class="fas fa-user"></i> Biodata
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#alamat">
                                <i class="fas fa-map-marker-alt"></i> Alamat
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#keluarga">
                                <i class="fas fa-users"></i> Keluarga
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#akun">
                                <i class="fas fa-key"></i> Keamanan
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="tab-content">
                            <!-- Tab Biodata -->
                            <div class="tab-pane fade show active" id="biodata">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="{{ $user->name }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Email</label>
                                        <input type="email" name="email" class="form-control bg-dark text-white border-secondary" value="{{ $user->email }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">NIK</label>
                                        <input type="text" name="nik" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->nik ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">No. KK</label>
                                        <input type="text" name="no_kk" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->no_kk ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->tempat_lahir ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->tanggal_lahir ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select bg-dark text-white border-secondary">
                                            <option value="">Pilih</option>
                                            <option value="L" {{ ($user->profile->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="P" {{ ($user->profile->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Agama</label>
                                        <select name="agama" class="form-select bg-dark text-white border-secondary">
                                            <option value="">Pilih</option>
                                            <option value="Islam" {{ ($user->profile->agama ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                            <option value="Kristen" {{ ($user->profile->agama ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                            <option value="Katolik" {{ ($user->profile->agama ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                            <option value="Hindu" {{ ($user->profile->agama ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                            <option value="Buddha" {{ ($user->profile->agama ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">No. HP</label>
                                        <input type="text" name="no_hp" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->no_hp ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Pekerjaan</label>
                                        <input type="text" name="pekerjaan" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->pekerjaan ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Status Perkawinan</label>
                                        <select name="status_perkawinan" class="form-select bg-dark text-white border-secondary">
                                            <option value="">Pilih</option>
                                            <option value="Belum Kawin" {{ ($user->profile->status_perkawinan ?? '') == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                                            <option value="Kawin" {{ ($user->profile->status_perkawinan ?? '') == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                                            <option value="Cerai" {{ ($user->profile->status_perkawinan ?? '') == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Pendidikan</label>
                                        <select name="pendidikan" class="form-select bg-dark text-white border-secondary">
                                            <option value="">Pilih</option>
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
                                    <label class="form-label text-white">Alamat Lengkap</label>
                                    <textarea name="alamat" rows="3" class="form-control bg-dark text-white border-secondary">{{ $user->profile->alamat ?? '' }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-white">RT</label>
                                        <input type="text" name="rt" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->rt ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-white">RW</label>
                                        <input type="text" name="rw" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->rw ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label text-white">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->kelurahan ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->kecamatan ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Kota</label>
                                        <input type="text" name="kota" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->kota ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Keluarga -->
                            <div class="tab-pane fade" id="keluarga">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Nama Ayah</label>
                                        <input type="text" name="nama_ayah" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->nama_ayah ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-white">Nama Ibu</label>
                                        <input type="text" name="nama_ibu" class="form-control bg-dark text-white border-secondary" value="{{ $user->profile->nama_ibu ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab Keamanan -->
                            <div class="tab-pane fade" id="akun">
                                <div class="alert alert-warning">
                                    <i class="fas fa-shield-alt"></i> Kosongkan jika tidak ingin mengubah password
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Password Baru</label>
                                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" class="form-control bg-dark text-white border-secondary">
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-gradient px-4">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Foto SEDERHANA -->
<div class="modal fade" id="uploadFotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white"><i class="fas fa-camera"></i> Upload Foto Profil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.update.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body text-center">
                    <img id="previewFoto" src="#" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; display: none;">
                    <div class="mb-3 text-start">
                        <label class="form-label text-white">Pilih Foto Profil</label>
                        <input type="file" name="avatar" id="avatarInput" class="form-control bg-dark text-white border-secondary" accept=".jpg,.jpeg,.png,.gif" onchange="previewImage(event)" required>
                        <small class="text-secondary">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</small>
                        <div id="avatarFilename" class="small text-white mt-2" style="display:none;"></div>
                        @error('avatar')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    color: #94a3b8;
    border: none;
    padding: 10px 20px;
}
.nav-tabs .nav-link:hover {
    color: #60a5fa;
    border-bottom: 2px solid #60a5fa;
}
.nav-tabs .nav-link.active {
    color: #60a5fa;
    background: transparent;
    border-bottom: 2px solid #60a5fa;
}
.btn-gradient {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border: none;
}
.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59,130,246,0.4);
    color: white;
}
.card {
    background: rgba(30, 41, 59, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(96, 165, 250, 0.1);
}
.form-control:focus, .form-select:focus {
    background-color: #1e293b;
    border-color: #60a5fa;
    box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
}
.alert-success {
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #4ade80;
}
</style>

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
            alert('Format file tidak valid. Gunakan JPG, PNG, atau GIF.');
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
        @if($errors->has('avatar'))
            var uploadModal = new bootstrap.Modal(document.getElementById('uploadFotoModal'));
            uploadModal.show();
        @endif
    });
</script>
@endsection