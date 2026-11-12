<div class="table-responsive">
    <table class="table table-saas align-middle mb-0">
        <thead>
            <tr>
                <th>Tanggal & Hari</th>
                <th>Shift & Pos</th>
                <th>Petugas Regu Warga (Tinggi Kontras)</th>
                <th>Presensi Kehadiran</th>
                <th class="text-end">Aksi Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $j)
                @php
                    $isMyJadwal = $j->petugas->contains('user_id', $user->id);
                    $myAbsen = $j->absensi->where('user_id', $user->id)->first();
                    $tglJakarta = \Carbon\Carbon::parse($j->tanggal)->setTimezone('Asia/Jakarta');
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $tglJakarta->isoFormat('dddd, D MMMM Y') }}</div>
                        <small class="text-secondary">{{ $tglJakarta->isToday() ? 'Malam Ini' : $tglJakarta->diffForHumans() }}</small>
                        @if($isMyJadwal)
                            <span class="badge bg-primary text-white ms-1 small">Tugas Saya</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-soft-info fw-semibold">{{ ucfirst($j->shift) }}</span>
                        <div class="small text-secondary mt-1"><i class="fas fa-location-dot me-1 text-emerald"></i>{{ $j->lokasi_pos }}</div>
                    </td>
                    <td>
                        @if($j->petugas->count() > 0)
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($j->petugas as $p)
                                    @if($p->user_id === $user->id)
                                        <span class="badge px-2 py-1" style="background-color: #147A45; color: #FFFFFF; font-weight: 700; border: 1px solid #0D5737;">
                                            <i class="fas fa-user-check me-1"></i>{{ $p->user->name ?? 'Saya' }}
                                        </span>
                                    @else
                                        <span class="badge px-2 py-1" style="background-color: #E8F5EC; color: #0D5737; font-weight: 600; border: 1px solid #A3D9B8;">
                                            {{ $p->user->name ?? 'Warga' }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">Belum ada petugas ditentukan</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge badge-soft-success">{{ $j->absensi->where('status', 'hadir')->count() }} Hadir</span>
                            <span class="badge badge-soft-warning">{{ $j->absensi->where('status', 'izin')->count() }} Izin</span>
                            <span class="badge badge-soft-danger">{{ $j->absensi->where('status', 'alpa')->count() }} Alpa</span>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if($isPengurus)
                                <form method="POST" action="{{ route('ronda.jadwal.ingatkan', $j) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-saas btn-sm text-primary" title="Kirim Pengingat Regu" data-confirm="Kirimkan notifikasi pengingat ronda kepada seluruh anggota regu ini?">
                                        <i class="fas fa-paper-plane me-1"></i>Ingatkan
                                    </button>
                                </form>
                                <button class="btn btn-outline-saas btn-sm text-info" data-bs-toggle="modal" data-bs-target="#modalAbsensiRonda" data-jadwal="{{ $j->id }}" title="Catat Presensi">
                                    <i class="fas fa-check-double me-1"></i>Absen
                                </button>
                                <button class="btn btn-outline-saas btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditJadwalRonda{{ $j->id }}" title="Edit Jadwal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('ronda.jadwal.destroy', $j) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-saas btn-sm text-danger" title="Hapus Jadwal" data-confirm="Hapus jadwal ronda shift tanggal {{ $tglJakarta->format('d/m/Y') }}?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                @if($isMyJadwal)
                                    @if(!$myAbsen || $myAbsen->status !== 'hadir')
                                        <form method="POST" action="{{ route('ronda.absensi.store') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="jadwal_ronda_id" value="{{ $j->id }}">
                                            <input type="hidden" name="status" value="hadir">
                                            <button type="submit" class="btn btn-saas-primary btn-sm">
                                                <i class="fas fa-check me-1"></i>Absen Hadir
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-soft-success py-2"><i class="fas fa-check-circle me-1"></i>Telah Hadir</span>
                                    @endif
                                @else
                                    <span class="text-muted small">Bukan giliran tugas</span>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-shield-halved"></i></div>
                            <h6 class="empty-state-title">Tidak Ada Jadwal</h6>
                            <p class="empty-state-desc">{{ $emptyMessage ?? 'Belum ada jadwal ronda.' }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
