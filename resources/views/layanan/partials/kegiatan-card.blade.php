<div class="col-12 col-lg-6">
    <div class="saas-card h-100 d-flex flex-column justify-content-between p-4 border" style="border-radius: 16px; border-color: #E9EEEA !important; transition: transform 0.2s, box-shadow 0.2s;">
        <div>
            {{-- Badges & Status --}}
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge {{ $kegiatan->cakupan === 'rw' ? 'badge-soft-emerald' : 'badge-soft-info' }}">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $kegiatan->cakupan === 'rw' ? 'Tingkat RW' : ($kegiatan->rt ? 'RT ' . $kegiatan->rt->kode_rt : 'Tingkat RT') }}
                    </span>
                    <span class="badge badge-soft-secondary text-uppercase">
                        {{ str_replace('_', ' ', $kegiatan->kategori) }}
                    </span>
                </div>

                <div>
                    @if($kegiatan->status === 'dibuka')
                        <span class="badge-soft-info"><i class="fas fa-bullhorn me-1"></i>Akan Datang</span>
                    @elseif($kegiatan->status === 'berlangsung')
                        <span class="badge-soft-warning"><i class="fas fa-play me-1"></i>Berlangsung</span>
                    @elseif($kegiatan->status === 'selesai')
                        <span class="badge-soft-emerald"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                    @elseif($kegiatan->status === 'dibatalkan')
                        <span class="badge-soft-danger"><i class="fas fa-ban me-1"></i>Dibatalkan</span>
                    @else
                        <span class="badge-soft-secondary"><i class="fas fa-pencil-alt me-1"></i>Draft</span>
                    @endif
                </div>
            </div>

            {{-- Judul & Deskripsi --}}
            <h5 class="fw-bold text-dark mb-2">{{ $kegiatan->judul }}</h5>
            <p class="text-secondary small mb-3" style="line-height: 1.5;">
                {{ Str::limit($kegiatan->deskripsi ?? 'Tidak ada keterangan tambahan.', 130) }}
            </p>

            {{-- Detail Info Strip --}}
            <div class="p-3 rounded-3 mb-3 bg-light border small" style="border-color: #E9EEEA !important; border-radius: 12px;">
                <div class="d-flex align-items-center mb-2 text-dark">
                    <i class="fas fa-calendar-days text-emerald me-2" style="width: 16px;"></i>
                    <span class="fw-semibold">
                        {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->translatedFormat('d M Y, H:i') : '-' }} s/d 
                        {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->translatedFormat('d M Y, H:i') : '-' }} WIB
                    </span>
                </div>
                <div class="d-flex align-items-center mb-2 text-dark">
                    <i class="fas fa-location-dot text-danger me-2" style="width: 16px;"></i>
                    <span class="text-truncate">{{ $kegiatan->lokasi }}</span>
                </div>
                <div class="d-flex align-items-center mb-2 text-dark">
                    <i class="fas fa-user-tie text-secondary me-2" style="width: 16px;"></i>
                    <span>Penyelenggara: <strong>{{ $kegiatan->cakupan === 'rw' ? 'Pengurus RW' : ($kegiatan->rt ? 'RT ' . $kegiatan->rt->kode_rt : 'Pengurus RT') }}</strong> ({{ $kegiatan->pembuat?->name ?? 'Pengurus' }})</span>
                </div>
                <div class="d-flex align-items-center text-dark">
                    <i class="fas fa-users text-info me-2" style="width: 16px;"></i>
                    <span>Partisipasi: <strong>{{ $kegiatan->peserta_count ?? ($kegiatan->peserta ? $kegiatan->peserta->count() : 0) }}</strong> / {{ $kegiatan->kuota_peserta ? $kegiatan->kuota_peserta . ' Peserta' : 'Tanpa Batas Kuota' }}</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top">
            <div class="d-flex gap-2">
                @if($kegiatan->peserta && $kegiatan->peserta->contains('user_id', Auth::id()))
                    <form method="POST" action="{{ route('kegiatan.batal-daftar', $kegiatan) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: 8px;">
                            <i class="fas fa-user-minus me-1"></i> Batal Daftar
                        </button>
                    </form>
                @elseif(in_array($kegiatan->status, ['dibuka', 'berlangsung']))
                    <form method="POST" action="{{ route('kegiatan.daftar', $kegiatan) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-emerald text-white btn-sm px-3" style="border-radius: 8px;">
                            <i class="fas fa-user-plus me-1"></i> Daftar Saya
                        </button>
                    </form>
                @endif
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetailKegiatan{{ $kegiatan->id }}" style="border-radius: 8px;">
                    <i class="fas fa-circle-info me-1"></i> Detail
                </button>
            </div>

            <div class="d-flex gap-2">
                @php
                    $userRtId = Auth::user()->managedRtId() ?? Auth::user()->profile?->rt_id;
                    $canManageThis = $isRw || ($kegiatan->cakupan === 'rt' && (int) $kegiatan->rt_id === (int) $userRtId);
                @endphp
                @if($canManage && $canManageThis)
                    <a href="{{ route('kegiatan.peserta', $kegiatan) }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                        <i class="fas fa-users me-1"></i> Peserta
                    </a>

                    @if($kegiatan->status !== 'dibatalkan')
                        <form method="POST" action="{{ route('kegiatan.destroy', $kegiatan) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-warning btn-sm" style="border-radius: 8px;" data-confirm="Apakah Anda yakin ingin membatalkan kegiatan {{ $kegiatan->judul }}?" data-confirm-title="Batalkan Kegiatan" data-confirm-btn="Ya, Batalkan">
                                <i class="fas fa-times me-1"></i> Batalkan
                            </button>
                        </form>
                    @else
                        {{-- Tombol Soft Delete untuk status dibatalkan --}}
                        <button type="button" class="btn btn-outline-danger btn-sm" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalHapusKegiatan{{ $kegiatan->id }}">
                            <i class="fas fa-trash-alt me-1"></i> Hapus
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
