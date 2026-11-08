<div class="col-12 col-lg-6">
    <div class="saas-card h-100 d-flex flex-column justify-content-between p-4 border">
        <div>
            {{-- Badges & Status --}}
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge {{ $kegiatan->cakupan === 'rw' ? 'badge-soft-primary' : 'badge-soft-success' }}">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $kegiatan->cakupan === 'rw' ? 'Tingkat RW' : ($kegiatan->rt ? 'RT ' . $kegiatan->rt->kode_rt : 'Tingkat RT') }}
                    </span>
                    <span class="badge badge-soft-secondary text-uppercase">
                        {{ str_replace('_', ' ', $kegiatan->kategori) }}
                    </span>
                </div>

                <div>
                    @if($kegiatan->status === 'dibuka')
                        <span class="badge badge-soft-info"><i class="fas fa-bullhorn me-1"></i>Dibuka</span>
                    @elseif($kegiatan->status === 'berlangsung')
                        <span class="badge badge-soft-warning"><i class="fas fa-play me-1"></i>Berlangsung</span>
                    @elseif($kegiatan->status === 'selesai')
                        <span class="badge badge-soft-success"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                    @elseif($kegiatan->status === 'dibatalkan')
                        <span class="badge badge-soft-danger"><i class="fas fa-ban me-1"></i>Dibatalkan</span>
                    @else
                        <span class="badge badge-soft-secondary"><i class="fas fa-pencil-alt me-1"></i>Draft</span>
                    @endif
                </div>
            </div>

            {{-- Judul & Deskripsi --}}
            <h5 class="fw-bold text-dark mb-2">{{ $kegiatan->judul }}</h5>
            <p class="text-secondary small mb-3" style="line-height: 1.5;">
                {{ $kegiatan->deskripsi ?? 'Tidak ada keterangan tambahan.' }}
            </p>

            {{-- Detail Info --}}
            <div class="p-3 rounded-3 mb-3 bg-light border small">
                <div class="d-flex align-items-center mb-2 text-dark">
                    <i class="fas fa-calendar-alt text-success me-2" style="width: 16px;"></i>
                    <span>
                        {{ $kegiatan->tanggal_mulai ? $kegiatan->tanggal_mulai->translatedFormat('d M Y, H:i') : '-' }} s/d 
                        {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->translatedFormat('d M Y, H:i') : '-' }}
                    </span>
                </div>
                <div class="d-flex align-items-center mb-2 text-dark">
                    <i class="fas fa-location-dot text-danger me-2" style="width: 16px;"></i>
                    <span class="text-truncate">{{ $kegiatan->lokasi }}</span>
                </div>
                <div class="d-flex align-items-center text-dark">
                    <i class="fas fa-users text-info me-2" style="width: 16px;"></i>
                    <span>{{ $kegiatan->peserta_count ?? ($kegiatan->peserta ? $kegiatan->peserta->count() : 0) }} / {{ $kegiatan->kuota_peserta ? $kegiatan->kuota_peserta . ' Peserta' : 'Tanpa Batas Kuota' }}</span>
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
                        <button type="submit" class="btn btn-outline-saas btn-sm text-danger border-danger">
                            <i class="fas fa-user-minus me-1"></i> Batal Daftar
                        </button>
                    </form>
                @elseif(in_array($kegiatan->status, ['dibuka', 'berlangsung']))
                    <form method="POST" action="{{ route('kegiatan.daftar', $kegiatan) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-saas-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Daftar Saya
                        </button>
                    </form>
                @endif
            </div>

            <div class="d-flex gap-2">
                @php
                    $userRtId = Auth::user()->managedRtId() ?? Auth::user()->profile?->rt_id;
                    $canManageThis = $isRw || ($kegiatan->cakupan === 'rt' && (int) $kegiatan->rt_id === (int) $userRtId);
                @endphp
                @if($canManage && $canManageThis)
                    <a href="{{ route('kegiatan.peserta', $kegiatan) }}" class="btn btn-outline-saas btn-sm">
                        <i class="fas fa-users me-1"></i> Peserta
                    </a>

                    @if($kegiatan->status !== 'dibatalkan')
                        <form method="POST" action="{{ route('kegiatan.destroy', $kegiatan) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-saas btn-sm text-warning border-warning" onclick="return confirm('Apakah Anda yakin ingin membatalkan kegiatan {{ addslashes($kegiatan->judul) }}?')">
                                <i class="fas fa-times me-1"></i> Batalkan
                            </button>
                        </form>
                    @else
                        {{-- Tombol Soft Delete untuk status dibatalkan --}}
                        <button type="button" class="btn btn-outline-saas btn-sm text-danger border-danger" data-bs-toggle="modal" data-bs-target="#modalHapusKegiatan{{ $kegiatan->id }}">
                            <i class="fas fa-trash-alt me-1"></i> Hapus
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
