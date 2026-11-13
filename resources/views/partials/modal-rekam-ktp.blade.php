<!-- MODAL REKAM E-KTP (RFID RC522 + NodeMCU ESP-32S) -->
<div class="modal fade" id="modalRekamKtp" tabindex="-1" aria-labelledby="modalRekamKtpLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header bg-emerald text-white py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-id-card fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalRekamKtpLabel">Perekaman E-KTP Warga</h5>
                        <small class="text-white text-opacity-75">Integrasi NodeMCU ESP-32S & RFID RC522</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Info Warga Terpilih -->
                <div class="card bg-light border-0 mb-3 p-3 rounded-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-emerald bg-opacity-10 text-emerald d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 48px; height: 48px;" id="modalAvatar">
                            W
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1 text-dark fs-6" id="modalNamaWarga">-</h6>
                            <div class="d-flex flex-wrap gap-2 small text-secondary">
                                <span>NIK: <strong class="text-dark font-monospace" id="modalNikWarga">-</strong></span>
                                <span>&bull;</span>
                                <span>Wilayah: <strong class="text-dark" id="modalRtWarga">RT -</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-secondary">Status E-KTP Saat Ini:</span>
                        <span id="modalCurrentStatus" class="badge bg-white text-secondary border font-monospace">Belum Ada</span>
                    </div>
                </div>

                <!-- Box Scanner Radar & Deteksi -->
                <div class="text-center p-4 rounded-4 mb-3 position-relative" id="scannerBox" style="background: var(--surface-2); border: 2px dashed var(--primary);">
                    <div class="mb-3 position-relative d-inline-block">
                        <div id="scannerIconPulse" class="rounded-circle d-flex align-items-center justify-content-center text-emerald mx-auto pulse-ring" style="width: 76px; height: 76px; background: rgba(16, 185, 129, 0.15);">
                            <i class="fas fa-satellite-dish fa-2x" id="scannerIcon"></i>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold text-dark mb-1" id="scannerTitle">Menunggu Kartu E-KTP...</h6>
                    <p class="small text-muted mb-3" id="scannerSubtitle">Silakan tempelkan fisik kartu E-KTP pada sensor RFID RC522</p>

                    <!-- Preview UID Terdeteksi -->
                    <div id="detectedUidWrapper" class="d-none">
                        <div class="small text-secondary mb-1 fw-medium">UID Kartu Terbaca:</div>
                        <span class="badge bg-dark text-white px-3 py-2 fs-5 font-monospace shadow-sm" id="detectedUidBadge">
                            --:--:--:--
                        </span>
                        <div class="small text-muted mt-2" id="scannedAtText">Waktu scan: -</div>
                    </div>

                    <!-- Alert Validasi Kartu -->
                    <div id="modalAlert" class="alert alert-danger py-2 px-3 small mt-3 mb-0 d-none text-start">
                        <i class="fas fa-exclamation-triangle me-1"></i> <span id="modalAlertMessage"></span>
                    </div>
                    <div id="modalSuccessAlert" class="alert alert-success py-2 px-3 small mt-3 mb-0 d-none text-start">
                        <i class="fas fa-check-circle me-1"></i> <span id="modalSuccessMessage">Kartu siap disimpan untuk warga ini.</span>
                    </div>
                </div>

                <!-- Accordion Input / Koreksi Manual -->
                <div class="accordion accordion-flush" id="accManual">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-0 text-secondary small bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManual">
                                <i class="fas fa-keyboard me-2"></i> Masukkan atau Koreksi UID Manual
                            </button>
                        </h2>
                        <div id="collapseManual" class="accordion-collapse collapse" data-bs-parent="#accManual">
                            <div class="accordion-body px-0 pt-2">
                                <label class="form-label small fw-medium text-secondary">Nomor UID Kartu RFID</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-barcode text-muted"></i></span>
                                    <input type="text" class="form-control font-monospace text-uppercase" id="inputManualUid" placeholder="Contoh: 1A2B3C4D">
                                </div>
                                <div class="form-text small">Ketik kode UID heksadesimal jika menggunakan barcode scanner atau reader USB.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm d-none" id="btnLepasKtp">
                        <i class="fas fa-unlink me-1"></i> Lepas E-KTP
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-emerald text-white px-4 fw-semibold" id="btnSimpanKtp" disabled>
                        <i class="fas fa-save me-1"></i> Simpan E-KTP
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Memastikan Modal Rekam KTP memiliki z-index tinggi di luar stacking context */
#modalRekamKtp {
    z-index: 1065 !important;
}
#modalRekamKtp + .modal-backdrop,
.modal-backdrop.show {
    z-index: 1060 !important;
}
.pulse-ring {
    animation: pulseRing 1.6s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulseRing {
    0% { transform: scale(0.96); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    70% { transform: scale(1.04); box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.96); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>

<script>
(function() {
    function initRekamKtpModal() {
        const modalEl = document.getElementById('modalRekamKtp');
        if (!modalEl || modalEl.dataset.initialized === 'true') return;
        modalEl.dataset.initialized = 'true';

        const modalInstance = new bootstrap.Modal(modalEl);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let currentWargaId = null;
        let currentWargaNama = '';
        let currentWargaNik = '';
        let currentRfidUid = '';
        let rfidPollTimer = null;
        let rfidListenStartTime = 0;
        let lastBeepUid = null;

        // Web Audio API Synth Beep saat kartu terbaca
        function playBeepSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1400, ctx.currentTime + 0.1);

                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.13);
            } catch (e) {
                console.log('Audio beep tidak didukung / blocked:', e);
            }
        }

        // Delegated Click Listener agar bekerja pada tombol dinamis maupun yang ada di dalam collapse tabel
        document.addEventListener('click', function(e) {
            const button = e.target.closest('.btn-rekam-ktp');
            if (!button) return;
            e.preventDefault();

            currentWargaId = button.dataset.id;
            currentWargaNama = button.dataset.nama || 'Warga';
            currentWargaNik = button.dataset.nik || '-';
            const rt = button.dataset.rt || '-';
            currentRfidUid = button.dataset.rfid || '';

            // Update info di modal
            document.getElementById('modalNamaWarga').textContent = currentWargaNama;
            document.getElementById('modalNikWarga').textContent = currentWargaNik;
            document.getElementById('modalRtWarga').textContent = 'RT ' + rt;
            document.getElementById('modalAvatar').textContent = currentWargaNama.charAt(0).toUpperCase();

            const statusEl = document.getElementById('modalCurrentStatus');
            const btnLepas = document.getElementById('btnLepasKtp');
            if (currentRfidUid) {
                statusEl.className = 'badge bg-light text-primary border font-monospace';
                statusEl.innerHTML = '<i class="fas fa-check-circle me-1 text-primary"></i> ' + currentRfidUid;
                btnLepas.classList.remove('d-none');
            } else {
                statusEl.className = 'badge bg-white text-secondary border font-monospace';
                statusEl.textContent = 'Belum Ada';
                btnLepas.classList.add('d-none');
            }

            resetScannerBox();
            modalInstance.show();
        });

        function resetScannerBox() {
            document.getElementById('scannerBox').style.borderColor = '#10b981';
            document.getElementById('scannerBox').style.background = 'linear-gradient(180deg, #f8fbf9 0%, #edf7f0 100%)';
            document.getElementById('scannerIconPulse').className = 'rounded-circle d-flex align-items-center justify-content-center text-emerald mx-auto pulse-ring';
            document.getElementById('scannerIcon').className = 'fas fa-satellite-dish fa-2x text-emerald';
            document.getElementById('scannerTitle').textContent = 'Menunggu Kartu E-KTP...';
            document.getElementById('scannerSubtitle').textContent = 'Silakan tempelkan fisik kartu E-KTP pada sensor RFID RC522';
            document.getElementById('detectedUidWrapper').classList.add('d-none');
            document.getElementById('modalAlert').classList.add('d-none');
            document.getElementById('modalSuccessAlert').classList.add('d-none');
            document.getElementById('inputManualUid').value = '';
            document.getElementById('btnSimpanKtp').disabled = true;
            lastBeepUid = null;
        }

        // Event saat modal terbuka
        modalEl.addEventListener('shown.bs.modal', function () {
            rfidListenStartTime = Math.floor(Date.now() / 1000) - 3;
            if (rfidPollTimer) clearInterval(rfidPollTimer);
            rfidPollTimer = setInterval(pollLatestScan, 1200);
            pollLatestScan();
        });

        // Event saat modal ditutup
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (rfidPollTimer) {
                clearInterval(rfidPollTimer);
                rfidPollTimer = null;
            }
        });

        // Polling ke backend untuk cek apakah ada tap kartu baru dari NodeMCU
        function pollLatestScan() {
            if (!currentWargaId) return;

            fetch(`{{ route('warga.rfid.latest') }}?since=${rfidListenStartTime}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'detected' && data.data && data.data.uid) {
                    const detectedUid = data.data.uid;
                    const formattedUid = data.data.formatted_uid || detectedUid;

                    if (lastBeepUid !== detectedUid) {
                        playBeepSound();
                        lastBeepUid = detectedUid;
                    }

                    document.getElementById('detectedUidBadge').textContent = formattedUid;
                    document.getElementById('inputManualUid').value = detectedUid;
                    document.getElementById('detectedUidWrapper').classList.remove('d-none');
                    document.getElementById('scannedAtText').textContent = 'Waktu scan: ' + new Date().toLocaleTimeString();

                    document.getElementById('scannerIconPulse').className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-emerald mx-auto';
                    document.getElementById('scannerIcon').className = 'fas fa-check fa-2x text-white';
                    document.getElementById('scannerTitle').textContent = 'E-KTP Berhasil Terdeteksi!';
                    document.getElementById('scannerSubtitle').textContent = 'Alat RC522 berhasil membaca chip kartu E-KTP.';

                    const alertEl = document.getElementById('modalAlert');
                    const successEl = document.getElementById('modalSuccessAlert');
                    const btnSimpan = document.getElementById('btnSimpanKtp');

                    // Validasi duplikasi
                    if (data.is_used && data.used_by && parseInt(data.used_by.id) !== parseInt(currentWargaId)) {
                        alertEl.classList.remove('d-none');
                        document.getElementById('modalAlertMessage').innerHTML = 
                            `Kartu ini sudah terdaftar atas nama warga: <strong>${data.used_by.nama_lengkap}</strong> (NIK: ${data.used_by.nik}, RT ${data.used_by.rt}). Tidak dapat digunakan ganda!`;
                        successEl.classList.add('d-none');
                        btnSimpan.disabled = true;
                    } else {
                        alertEl.classList.add('d-none');
                        successEl.classList.remove('d-none');
                        btnSimpan.disabled = false;
                    }
                }
            })
            .catch(err => {
                console.error('Error polling RFID scan:', err);
            });
        }

        // Input manual UID fallback
        document.getElementById('inputManualUid').addEventListener('input', function () {
            const val = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            this.value = val;
            const btnSimpan = document.getElementById('btnSimpanKtp');

            if (val.length >= 4) {
                document.getElementById('detectedUidBadge').textContent = val.match(/.{1,2}/g)?.join(':') || val;
                document.getElementById('detectedUidWrapper').classList.remove('d-none');
                document.getElementById('modalAlert').classList.add('d-none');
                document.getElementById('modalSuccessAlert').classList.remove('d-none');
                btnSimpan.disabled = false;
            } else {
                btnSimpan.disabled = true;
            }
        });

        // Simpan E-KTP ke Warga
        document.getElementById('btnSimpanKtp').addEventListener('click', function () {
            const uid = document.getElementById('inputManualUid').value.trim();
            if (!uid || !currentWargaId) return;

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';

            fetch(`/warga/${currentWargaId}/rfid/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ rfid_uid: uid })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    modalInstance.hide();
                    window.location.reload();
                } else {
                    const alertEl = document.getElementById('modalAlert');
                    alertEl.classList.remove('d-none');
                    document.getElementById('modalAlertMessage').textContent = data.message || 'Gagal menyimpan E-KTP.';
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                console.error('Error saving RFID UID:', err);
                const alertEl = document.getElementById('modalAlert');
                alertEl.classList.remove('d-none');
                document.getElementById('modalAlertMessage').textContent = 'Terjadi kesalahan sistem saat menyimpan.';
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });

        // Lepas Tautan E-KTP
        document.getElementById('btnLepasKtp').addEventListener('click', function () {
            if (!currentWargaId) return;
            if (!confirm(`Apakah Anda yakin ingin melepas tautan E-KTP dari warga "${currentWargaNama}"?`)) {
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Melepas...';

            fetch(`/warga/${currentWargaId}/rfid/unlink`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    modalInstance.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal melepas E-KTP');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                console.error('Error unlinking RFID UID:', err);
                alert('Terjadi kesalahan sistem saat melepas tautan.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRekamKtpModal);
    } else {
        initRekamKtpModal();
    }
})();
</script>
