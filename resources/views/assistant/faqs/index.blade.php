@extends('layouts.app')

@section('title', 'Kelola FAQ Asisten Smart RW')

@section('content')
<div class="content-container">
    {{-- Header --}}
    <div class="page-head page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-head-icon" style="background: rgba(14, 122, 75, 0.15); color: #0e7a4b; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="page-title mb-0" style="color: var(--ink);">Kelola FAQ Asisten Smart RW</h1>
                    <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-2.5 py-1 small">Super Admin & RW</span>
                </div>
                <p class="page-subtitle mb-0" style="color: var(--ink-soft);">Basis pengetahuan bot otomatis 24 jam untuk menjawab pertanyaan warga seputar layanan, iuran, dan kegiatan wilayah.</p>
            </div>
        </div>
        <div class="page-header-actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahFaq">
                <i class="fas fa-plus-circle me-1.5"></i> Tambah FAQ Baru
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-exclamation fs-5 text-danger"></i>
                <div class="fw-semibold">{{ $errors->first() }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Nav Tabs --}}
    <ul class="nav nav-pills mb-4 gap-2" id="faqTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold px-3 py-2 rounded-3" id="tab-faq-list" data-bs-toggle="pill" data-bs-target="#panel-faq-list" type="button" role="tab">
                <i class="fas fa-list-check me-1.5"></i> Daftar FAQ Aktif ({{ $faqs->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-3 py-2 rounded-3" id="tab-unanswered" data-bs-toggle="pill" data-bs-target="#panel-unanswered" type="button" role="tab">
                <i class="fas fa-circle-question me-1.5"></i> Pertanyaan Belum Terjawab ({{ $unansweredQuestions->total() }})
            </button>
        </li>
    </ul>

    <div class="tab-content" id="faqTabsContent">
        {{-- TAB 1: DAFTAR FAQ --}}
        <div class="tab-pane fade show active" id="panel-faq-list" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: var(--surface);">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead style="background: var(--surface-2); border-bottom: 2px solid var(--border);">
                            <tr class="small text-uppercase text-secondary fw-bold">
                                <th style="width: 60px;" class="text-center">Urutan</th>
                                <th style="min-width: 220px;">Pertanyaan & Kategori</th>
                                <th style="min-width: 200px;">Kata Kunci Pencocokan</th>
                                <th style="min-width: 260px;">Jawaban Asisten</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th style="width: 120px;" class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td class="text-center fw-bold text-secondary">
                                        {{ $faq->urutan }}
                                    </td>
                                    <td>
                                        <div class="fw-bold mb-1" style="color: var(--ink);">{{ $faq->pertanyaan }}</div>
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.72rem;">
                                                <i class="fas fa-tag me-1"></i>{{ $faq->kategori ?: 'Umum' }}
                                            </span>
                                            @if(!$faq->is_active)
                                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.72rem;">
                                                    <i class="fas fa-file-lines me-1"></i>Draft
                                                </span>
                                            @endif
                                            @if($faq->perlu_tinjauan)
                                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.72rem;" title="{{ $faq->catatan_tinjauan ?: 'Perlu ditinjau pengurus' }}">
                                                    <i class="fas fa-triangle-exclamation me-1"></i>Perlu Ditinjau Pengurus
                                                </span>
                                            @endif
                                        </div>
                                        @if($faq->perlu_tinjauan && $faq->catatan_tinjauan)
                                            <div class="small text-muted mt-1 fst-italic" style="font-size: 0.75rem;">
                                                <i class="fas fa-circle-info me-1 text-warning"></i>{{ $faq->catatan_tinjauan }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted" style="line-height: 1.4;">
                                            @if($faq->kata_kunci)
                                                @foreach(array_slice(explode(',', $faq->kata_kunci), 0, 4) as $kw)
                                                    <span class="badge bg-secondary-subtle text-secondary me-1 mb-1 font-monospace">{{ trim($kw) }}</span>
                                                @endforeach
                                                @if(count(explode(',', $faq->kata_kunci)) > 4)
                                                    <span class="small text-secondary">+{{ count(explode(',', $faq->kata_kunci)) - 4 }} lagi</span>
                                                @endif
                                            @else
                                                <span class="text-muted fst-italic">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small" style="color: var(--ink-soft); max-height: 90px; overflow-y: auto; white-space: pre-line;">{{ Str::limit($faq->jawaban, 150) }}</div>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('assistant.faqs.toggle', $faq) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $faq->is_active ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-2.5 py-0.5" title="Klik untuk ubah status">
                                                {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 me-1 btn-edit-faq"
                                            data-id="{{ $faq->id }}"
                                            data-pertanyaan="{{ $faq->pertanyaan }}"
                                            data-kategori="{{ $faq->kategori }}"
                                            data-kata-kunci="{{ $faq->kata_kunci }}"
                                            data-jawaban="{{ $faq->jawaban }}"
                                            data-urutan="{{ $faq->urutan }}"
                                            data-is-active="{{ $faq->is_active ? '1' : '0' }}"
                                            data-perlu-tinjauan="{{ $faq->perlu_tinjauan ? '1' : '0' }}"
                                            data-catatan-tinjauan="{{ $faq->catatan_tinjauan ?? '' }}"
                                            data-field-pengurus="{{ is_array($faq->field_pengurus) ? json_encode($faq->field_pengurus) : ($faq->field_pengurus ?? '') }}"
                                            data-action="{{ route('assistant.faqs.update', $faq) }}"
                                            title="Edit FAQ">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        <form method="POST" action="{{ route('assistant.faqs.destroy', $faq) }}" class="d-inline" onsubmit="return confirm('Hapus FAQ ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Hapus">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <i class="fas fa-robot fs-1 mb-2 opacity-50"></i>
                                        <p class="mb-0">Belum ada data FAQ Asisten yang tersimpan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: PERTANYAAN BELUM TERJAWAB --}}
        <div class="tab-pane fade" id="panel-unanswered" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: var(--surface);">
                <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--ink);">Pertanyaan yang Belum Ditemukan Jawabannya</h6>
                        <span class="small text-secondary">Otomatis dicatat saat bot membalas pesan default tidak tahu</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead style="background: var(--surface-2); border-bottom: 2px solid var(--border);">
                            <tr class="small text-uppercase text-secondary fw-bold">
                                <th>Pertanyaan Pengguna</th>
                                <th>Alamat IP</th>
                                <th>Pengguna</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unansweredQuestions as $uq)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td class="fw-semibold" style="color: var(--ink);">
                                        {{ $uq->pertanyaan }}
                                    </td>
                                    <td class="small font-monospace text-secondary">
                                        {{ $uq->ip_address ?: '-' }}
                                    </td>
                                    <td>
                                        @if($uq->user)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $uq->user->name }}</span>
                                        @else
                                            <span class="text-muted small">Tamu (Publik)</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary">
                                        {{ $uq->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td>
                                        @if($uq->status === 'dijawab')
                                            <span class="badge bg-success-subtle text-success">Sudah Dijadikan FAQ</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">Belum Ada Jawaban</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-3 me-1 btn-convert-faq"
                                            data-pertanyaan="{{ $uq->pertanyaan }}"
                                            data-unanswered-id="{{ $uq->id }}"
                                            title="Jadikan FAQ Baru">
                                            <i class="fas fa-plus-circle me-1"></i> Buat Jawaban
                                        </button>
                                        <form method="POST" action="{{ route('assistant.unanswered.destroy', $uq) }}" class="d-inline" onsubmit="return confirm('Hapus catatan pertanyaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Hapus">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <i class="fas fa-circle-check fs-1 mb-2 text-success opacity-75"></i>
                                        <p class="mb-0">Semua pertanyaan pengguna berhasil dijawab oleh bot FAQ.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($unansweredQuestions->hasPages())
                    <div class="card-footer bg-transparent border-0 py-3">
                        {{ $unansweredQuestions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / JADIKAN FAQ --}}
<div class="modal fade" id="modalTambahFaq" tabindex="-1" aria-labelledby="modalTambahFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="{{ route('assistant.faqs.store') }}" id="formTambahFaq">
                @csrf
                <input type="hidden" name="unanswered_id" id="tambahUnansweredId" value="">

                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold" id="modalTambahFaqLabel" style="color: var(--ink);">
                        <i class="fas fa-robot text-primary me-2"></i>Tambah FAQ Asisten Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Pertanyaan <span class="text-danger">*</span></label>
                        <input type="text" name="pertanyaan" id="tambahPertanyaan" class="form-control rounded-3" required placeholder="Contoh: Bagaimana cara membuat surat pengantar?">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Kategori</label>
                            <input type="text" name="kategori" id="tambahKategori" class="form-control rounded-3" placeholder="Layanan / Keuangan / Keamanan / Umum" value="Umum">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Urutan Tampil</label>
                            <input type="number" name="urutan" id="tambahUrutan" class="form-control rounded-3" value="0" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Kata Kunci Pencocokan (Pisahkan dengan tanda koma)</label>
                        <input type="text" name="kata_kunci" id="tambahKataKunci" class="form-control rounded-3" placeholder="surat pengantar, skck, bikin surat, pengantar rw">
                        <div class="form-text small">Bot akan mencocokkan frasa dan kata kunci ini ketika warga bertanya dengan variasi kalimat berbeda.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Jawaban Bot Asisten <span class="text-danger">*</span></label>
                        <textarea name="jawaban" id="tambahJawaban" rows="5" class="form-control rounded-3" required placeholder="Tuliskan jawaban yang ramah, informatif, dan jelas..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Field Isian Khusus RW (JSON/Format Pengurus - Opsional)</label>
                        <textarea name="field_pengurus" id="tambahFieldPengurus" rows="3" class="form-control font-monospace small rounded-3" placeholder='{"jadwal": "", "lokasi": "", "kader": ""}'></textarea>
                        <div class="form-text small">Untuk FAQ Layanan Masyarakat seperti Posyandu atau Bank Sampah yang memerlukan isian khusus pengurus.</div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="tambahIsActive" value="1" checked>
                        <label class="form-check-label fw-semibold" for="tambahIsActive">Aktifkan FAQ ini langsung di Live Chat</label>
                    </div>

                    <div class="border rounded-3 p-3 mt-3 bg-light-subtle">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="perlu_tinjauan" id="tambahPerluTinjauan" value="1">
                            <label class="form-check-label fw-semibold text-warning-emphasis" for="tambahPerluTinjauan">
                                <i class="fas fa-triangle-exclamation me-1"></i>Tandai Perlu Ditinjau Pengurus
                            </label>
                        </div>
                        <div>
                            <label class="form-label small text-secondary mb-1">Catatan Tinjauan (Alasan/fakta yang perlu divalidasi)</label>
                            <input type="text" name="catatan_tinjauan" id="tambahCatatanTinjauan" class="form-control form-control-sm rounded-2" placeholder="Contoh: Jam ronda perlu dicek ulang dengan Sie Keamanan">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        <i class="fas fa-save me-1.5"></i> Simpan FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT FAQ --}}
<div class="modal fade" id="modalEditFaq" tabindex="-1" aria-labelledby="modalEditFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="" id="formEditFaq">
                @csrf
                @method('PUT')

                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold" id="modalEditFaqLabel" style="color: var(--ink);">
                        <i class="fas fa-pen-to-square text-primary me-2"></i>Edit FAQ Asisten
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Pertanyaan <span class="text-danger">*</span></label>
                        <input type="text" name="pertanyaan" id="editPertanyaan" class="form-control rounded-3" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Kategori</label>
                            <input type="text" name="kategori" id="editKategori" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Urutan Tampil</label>
                            <input type="number" name="urutan" id="editUrutan" class="form-control rounded-3" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Kata Kunci Pencocokan (Pisahkan dengan tanda koma)</label>
                        <input type="text" name="kata_kunci" id="editKataKunci" class="form-control rounded-3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Jawaban Bot Asisten <span class="text-danger">*</span></label>
                        <textarea name="jawaban" id="editJawaban" rows="5" class="form-control rounded-3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Field Isian Khusus RW (JSON/Format Pengurus - Opsional)</label>
                        <textarea name="field_pengurus" id="editFieldPengurus" rows="3" class="form-control font-monospace small rounded-3" placeholder='{"jadwal": "", "lokasi": "", "kader": ""}'></textarea>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                        <label class="form-check-label fw-semibold" for="editIsActive">Status Aktif</label>
                    </div>

                    <div class="border rounded-3 p-3 mt-3 bg-light-subtle">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="perlu_tinjauan" id="editPerluTinjauan" value="1">
                            <label class="form-check-label fw-semibold text-warning-emphasis" for="editPerluTinjauan">
                                <i class="fas fa-triangle-exclamation me-1"></i>Tandai Perlu Ditinjau Pengurus
                            </label>
                        </div>
                        <div>
                            <label class="form-label small text-secondary mb-1">Catatan Tinjauan (Alasan/fakta yang perlu divalidasi)</label>
                            <input type="text" name="catatan_tinjauan" id="editCatatanTinjauan" class="form-control form-control-sm rounded-2" placeholder="Contoh: Jam ronda perlu dicek ulang">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        <i class="fas fa-save me-1.5"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tombol Edit FAQ
    const editButtons = document.querySelectorAll('.btn-edit-faq');
    const modalEditFaq = new bootstrap.Modal(document.getElementById('modalEditFaq'));
    const formEditFaq = document.getElementById('formEditFaq');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            formEditFaq.action = this.dataset.action;
            document.getElementById('editPertanyaan').value = this.dataset.pertanyaan || '';
            document.getElementById('editKategori').value = this.dataset.kategori || 'Umum';
            document.getElementById('editKataKunci').value = this.dataset.kataKunci || '';
            document.getElementById('editJawaban').value = this.dataset.jawaban || '';
            document.getElementById('editUrutan').value = this.dataset.urutan || '0';
            document.getElementById('editIsActive').checked = this.dataset.isActive === '1';
            document.getElementById('editPerluTinjauan').checked = this.dataset.perluTinjauan === '1';
            document.getElementById('editCatatanTinjauan').value = this.dataset.catatanTinjauan || '';
            document.getElementById('editFieldPengurus').value = this.dataset.fieldPengurus || '';
            modalEditFaq.show();
        });
    });

    // Tombol Jadikan FAQ dari Pertanyaan Belum Terjawab
    const convertButtons = document.querySelectorAll('.btn-convert-faq');
    const modalTambahFaq = new bootstrap.Modal(document.getElementById('modalTambahFaq'));
    convertButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('tambahPertanyaan').value = this.dataset.pertanyaan || '';
            document.getElementById('tambahKataKunci').value = (this.dataset.pertanyaan || '').toLowerCase();
            document.getElementById('tambahUnansweredId').value = this.dataset.unansweredId || '';
            modalTambahFaq.show();
        });
    });
});
</script>
@endpush
