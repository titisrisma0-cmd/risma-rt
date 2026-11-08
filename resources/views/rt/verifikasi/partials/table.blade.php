<div class="saas-card overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-inbox text-emerald me-2"></i>{{ $title ?? 'Daftar Permohonan' }}
        </h6>
        <span class="badge-soft-{{ $badgeColor === 'primary' ? 'emerald' : $badgeColor }}">{{ $items->count() }} Data</span>
    </div>
    <div class="table-responsive">
        <table class="table-saas">
            <thead>
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
                        <td class="font-monospace fw-bold text-emerald">{{ $p->nomor_pengajuan }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ optional($p->user)->name ?? 'Warga' }}</div>
                            <small class="text-secondary">NIK: {{ optional($p->user->profile)->nik ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge-soft-info">{{ optional($p->jenisSurat)->nama ?? 'Surat' }}</span>
                        </td>
                        <td class="small text-secondary" style="max-width: 250px;">{{ $p->keperluan }}</td>
                        <td>
                            @if(in_array($p->status, ['disetujui', 'selesai', 'siap_diambil']))
                                <span class="badge-soft-emerald">{{ $p->status_label }}</span>
                            @elseif($p->status === 'ditolak')
                                <span class="badge-soft-danger">{{ $p->status_label }}</span>
                            @else
                                <span class="badge-soft-warning">{{ $p->status_label }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                {{-- Verifikasi / Setujui Langsung Tingkat RT --}}
                                @if(in_array($p->status, ['pending', 'menunggu_rt', 'diproses', 'diproses_rt']))
                                    <form method="POST" action="{{ route('layanan.surat.update', $p) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="siap_diambil">
                                        <button type="submit" class="btn btn-sm btn-emerald text-white" title="Setujui dan Terbitkan Surat RT" onclick="return confirm('Setujui langsung permohonan surat ini tingkat RT?');">
                                            <i class="fas fa-check me-1"></i>Setujui RT
                                        </button>
                                    </form>

                                    {{-- Teruskan ke RW --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalTeruskan{{ $p->id }}" title="Teruskan ke RW">
                                        <i class="fas fa-paper-plane me-1 text-primary"></i>Ke RW
                                    </button>

                                    {{-- Tolak Permohonan --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalTolak{{ $p->id }}" title="Tolak Permohonan">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                                @if($p->nomor_surat)
                                    <a href="{{ route('layanan.surat.cetak', $p) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-print me-1 text-emerald"></i>Cetak
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Teruskan ke RW --}}
                    <div class="modal fade" id="modalTeruskan{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                <form method="POST" action="{{ route('rt.verifikasi.teruskan-rw', $p) }}">
                                    @csrf
                                    <div class="modal-header border-bottom bg-white">
                                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-paper-plane text-emerald me-2"></i>Teruskan Permohonan ke Tingkat RW</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start p-4">
                                        <p class="small text-secondary mb-3">Permohonan <strong class="text-dark">{{ $p->nomor_pengajuan }}</strong> oleh <strong class="text-dark">{{ $p->user->name ?? 'Warga' }}</strong> akan diteruskan ke Ketua/Pengurus RW untuk verifikasi lebih lanjut.</p>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Catatan Pengantar dari Ketua RT <span class="text-danger">*</span></label>
                                            <textarea name="catatan_rt" rows="3" class="form-control form-control-saas" placeholder="Contoh: Warga benar berdomisili di RT kami dan berkas lampiran telah diverifikasi lengkap. Mohon persetujuan RW." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-light">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-emerald text-white px-4"><i class="fas fa-paper-plane me-1"></i>Kirim ke RW</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Tolak Permohonan --}}
                    <div class="modal fade" id="modalTolak{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                <form method="POST" action="{{ route('layanan.surat.update', $p) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ditolak">
                                    <div class="modal-header border-bottom bg-white">
                                        <h5 class="modal-title fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Tolak Permohonan Surat</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start p-4">
                                        <p class="small text-secondary mb-3">Anda akan menolak permohonan <strong class="text-dark">{{ $p->nomor_pengajuan }}</strong>.</p>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-secondary">Alasan / Catatan Penolakan <span class="text-danger">*</span></label>
                                            <textarea name="catatan" rows="3" class="form-control form-control-saas" placeholder="Tuliskan alasan penolakan agar warga dapat memperbaikinya..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-light">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger px-4"><i class="fas fa-ban me-1"></i>Tolak Permohonan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-secondary">Tidak ada permohonan dalam kategori ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
