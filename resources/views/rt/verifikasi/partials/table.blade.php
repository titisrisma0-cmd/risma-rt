<div class="card border">
    <div class="card-header bg-card d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-{{ $badgeColor ?? 'primary' }}">
            <i class="fas fa-inbox me-2"></i>{{ $title ?? 'Daftar Permohonan' }}
        </h6>
        <span class="badge bg-{{ $badgeColor ?? 'primary' }}">{{ $items->count() }} Data</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Pengajuan</th>
                    <th>Pemohon</th>
                    <th>Jenis Layanan</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 240px;">Aksi Keputusan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $p)
                    <tr>
                        <td class="font-monospace fw-semibold text-success">{{ $p->nomor_pengajuan }}</td>
                        <td>
                            <div class="fw-semibold">{{ optional($p->user)->name ?? 'Warga' }}</div>
                            <small class="text-muted">NIK: {{ optional($p->user->profile)->nik ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">{{ optional($p->jenisSurat)->nama ?? 'Surat' }}</span>
                        </td>
                        <td class="small text-muted" style="max-width: 250px;">{{ $p->keperluan }}</td>
                        <td>
                            <span class="badge bg-{{ $p->status === 'disetujui' || $p->status === 'selesai' || $p->status === 'siap_diambil' ? 'success' : ($p->status === 'ditolak' ? 'danger' : 'warning') }}">
                                {{ $p->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                {{-- Verifikasi / Setujui Langsung Tingkat RT --}}
                                @if(in_array($p->status, ['pending', 'menunggu_rt', 'diproses', 'diproses_rt']))
                                    <form method="POST" action="{{ route('layanan.surat.update', $p) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="siap_diambil">
                                        <button type="submit" class="btn btn-sm btn-success" title="Setujui dan Terbitkan Surat RT" onclick="return confirm('Setujui langsung permohonan surat ini tingkat RT?');">
                                            <i class="fas fa-check me-1"></i>Setujui RT
                                        </button>
                                    </form>

                                    {{-- Teruskan ke RW --}}
                                    <button type="button" class="btn btn-sm btn-outline-purple text-purple" style="border-color: #a855f7; color: #a855f7;" data-bs-toggle="modal" data-bs-target="#modalTeruskan{{ $p->id }}" title="Teruskan ke RW">
                                        <i class="fas fa-paper-plane me-1"></i>Ke RW
                                    </button>

                                    {{-- Tolak Permohonan --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalTolak{{ $p->id }}" title="Tolak Permohonan">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                                @if($p->nomor_surat)
                                    <a href="{{ route('layanan.surat.cetak', $p) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-print me-1"></i>Cetak
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Teruskan ke RW --}}
                    <div class="modal fade" id="modalTeruskan{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('rt.verifikasi.teruskan-rw', $p) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" style="color: #a855f7;"><i class="fas fa-paper-plane me-2"></i>Teruskan Permohonan ke Tingkat RW</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <p class="small text-muted mb-2">Permohonan <strong>{{ $p->nomor_pengajuan }}</strong> oleh <strong>{{ $p->user->name ?? 'Warga' }}</strong> akan diteruskan ke Ketua/Pengurus RW untuk verifikasi lebih lanjut.</p>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Catatan Pengantar dari Ketua RT <span class="text-danger">*</span></label>
                                            <textarea name="catatan_rt" rows="3" class="form-control" placeholder="Contoh: Warga benar berdomisili di RT kami dan berkas lampiran telah diverifikasi lengkap. Mohon persetujuan RW." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm text-white" style="background: #a855f7;"><i class="fas fa-paper-plane me-1"></i>Kirim ke RW</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Tolak Permohonan --}}
                    <div class="modal fade" id="modalTolak{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('layanan.surat.update', $p) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ditolak">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Tolak Permohonan Surat</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <p class="small text-muted mb-2">Anda akan menolak permohonan <strong>{{ $p->nomor_pengajuan }}</strong>.</p>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Alasan / Catatan Penolakan <span class="text-danger">*</span></label>
                                            <textarea name="catatan" rows="3" class="form-control" placeholder="Tuliskan alasan penolakan agar warga dapat memperbaikinya..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-ban me-1"></i>Tolak Permohonan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada permohonan dalam kategori ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
