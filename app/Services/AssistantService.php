<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\AssistantFaq;
use App\Models\AssistantUnansweredQuestion;
use App\Models\PengajuanSurat;
use App\Models\Pembayaran;
use App\Models\SaldoSampah;
use App\Models\User;

class AssistantService
{
    /**
     * Pesan default saat pertanyaan tidak menemukan jawaban di FAQ
     */
    public const FALLBACK_MESSAGE = 'Maaf, saya belum menemukan jawabannya. Coba pilih salah satu pertanyaan di bawah ini, atau tekan tombol CS di atas untuk menghubungi pengurus.';

    /**
     * Pesan baku saat FAQ yang ditanyakan masih berstatus DRAFT (belum diisi pengurus)
     */
    public const DRAFT_MESSAGE = 'Informasi ini belum diisi pengurus. Silakan tekan tombol CS di atas untuk bertanya langsung ke pengurus.';

    /**
     * Pesan saat user mencoba mengakses data orang lain
     */
    public const OTHER_USER_DATA_MESSAGE = 'Maaf, saya hanya bisa menampilkan data akun Anda sendiri.';

    /**
     * Catatan disclaimer otomatis
     */
    public const DISCLAIMER = 'Asisten otomatis, jawaban bisa kurang tepat.';

    /**
     * Saran pertanyaan cepat bawaan umum
     */
    public const QUICK_SUGGESTIONS = [
        'Cara mendaftar akun warga',
        'Lupa kata sandi',
        'Cara bayar iuran',
        'Syarat surat pengantar',
        'Jadwal posyandu',
    ];

    /**
     * Kamus normalisasi bentuk gaul / singkatan bahasa Indonesia
     */
    protected const SLANG_MAP = [
        'gimana' => 'bagaimana',
        'gmn' => 'bagaimana',
        'gmna' => 'bagaimana',
        'bgmn' => 'bagaimana',
        'bgmna' => 'bagaimana',
        'yg' => 'yang',
        'gak' => 'tidak',
        'ga' => 'tidak',
        'gk' => 'tidak',
        'tdk' => 'tidak',
        'tak' => 'tidak',
        'nggak' => 'tidak',
        'enggak' => 'tidak',
        'tau' => 'tahu',
        'tw' => 'tahu',
        'dftar' => 'daftar',
        'daftr' => 'daftar',
        'dftr' => 'daftar',
        'pass' => 'sandi',
        'pw' => 'sandi',
        'pwd' => 'sandi',
        'pasword' => 'sandi',
        'passwd' => 'sandi',
        'bwt' => 'buat',
        'bkin' => 'buat',
        'bikin' => 'buat',
        'sdh' => 'sudah',
        'udh' => 'sudah',
        'udah' => 'sudah',
        'blm' => 'belum',
        'blom' => 'belum',
        'sy' => 'saya',
        'aq' => 'saya',
        'ak' => 'saya',
        'klo' => 'kalau',
        'kl' => 'kalau',
        'klu' => 'kalau',
        'sm' => 'sama',
        'sma' => 'sama',
        'utk' => 'untuk',
        'dgn' => 'dengan',
        'dg' => 'dengan',
        'dr' => 'dari',
        'dri' => 'dari',
        'syrt' => 'syarat',
        'sarat' => 'syarat',
        'syt' => 'syarat',
        'brp' => 'berapa',
        'brapa' => 'berapa',
        'tny' => 'tanya',
        'tnya' => 'tanya',
        'tlg' => 'tolong',
        'tlong' => 'tolong',
        'mhon' => 'mohon',
        'ktp' => 'ktp',
        'e-ktp' => 'ktp',
        'ektp' => 'ktp',
        'e ktp' => 'ktp',
        'kk' => 'kk',
        'nik' => 'nik',
    ];

    /**
     * Daftar stopword yang TIDAK menentukan topik
     */
    protected const STOPWORDS = [
        'cara', 'bagaimana', 'apa', 'yang', 'untuk', 'dong', 'ya', 'nih', 'mau',
        'tanya', 'bisa', 'syarat', 'dan', 'atau', 'di', 'ke', 'dari', 'adalah',
        'ini', 'itu', 'halo', 'min', 'pak', 'bu', 'gan', 'kak', 'permisi', 'mohon',
        'tolong', 'terima', 'kasih', 'terimakasih', 'trims', 'info', 'informasi',
        'ada', 'apakah', 'kenapa', 'mengapa', 'seputar', 'mengenai', 'tentang',
        'kah', 'pun', 'lah', 'deh', 'lur', 'mas', 'mbak', 'agan', 'bro', 'sis',
        'sob', 'guys', 'minn', 'hai', 'hey', 'helo', 'bos', 'kalo', 'kalau',
        'gitu', 'begini', 'begitu', 'nya', 'saja', 'aja', 'dulu', 'kok', 'sih',
        'kan', 'dongg', 'dongya', 'bantu', 'bantuan', 'saya', 'kamu', 'anda',
        'kami', 'kita', 'dia', 'mereka', 'harus', 'perlu', 'ingin', 'dapat',
        'oleh', 'atas', 'bawah', 'dalam', 'luar', 'akan', 'sudah', 'belum',
        'sedang', 'telah', 'tidak', 'bukan'
    ];

    /**
     * Peta sinonim topik bawaan
     */
    protected const TOPIC_SYNONYMS = [
        'daftar' => ['daftar', 'mendaftar', 'registrasi', 'pendaftaran', 'buat akun', 'bikin akun', 'sign up'],
        'warga' => ['warga', 'kependudukan', 'penduduk', 'domisili'],
        'jadi warga' => ['jadi warga', 'warga baru', 'pindah', 'domisili rw 01', 'menjadi warga'],
        'iuran' => ['iuran', 'bayar', 'tagihan', 'kas', 'pembayaran', 'qris', 'transfer', 'uang kas'],
        'surat' => ['surat', 'pengantar', 'skck', 'dokumen', 'surat pengantar', 'pengantar rt', 'pengantar rw'],
        'ronda' => ['ronda', 'siskamling', 'pos ronda', 'jaga malam', 'patroli', 'giliran'],
        'sandi' => ['sandi', 'password', 'kata sandi', 'lupa sandi', 'reset sandi', 'ganti sandi', 'ubah sandi', 'lupa password', 'otp'],
        'posyandu' => ['posyandu', 'pos pelayanan terpadu', 'imunisasi', 'timbang balita', 'kia', 'kms', 'balita', 'posyandu lansia', 'posyandu balita'],
        'bank sampah' => ['bank sampah', 'setor sampah', 'tabungan sampah', 'tukar sampah', 'timbang sampah', 'daur ulang', 'limbah'],
        'kerja bakti' => ['kerja bakti', 'gotong royong', 'bersih lingkungan', 'bakti warga'],
        'pengaduan' => ['pengaduan', 'pengaduan lingkungan', 'aspirasi', 'keluhan', 'lapor keluhan', 'aduan warga'],
        'kontak darurat' => ['kontak darurat', 'nomor darurat', 'darurat', 'telepon darurat', 'ambulans', 'pemadam', 'polsek', 'bhabinkamtibmas', 'babinsa'],
    ];

    /**
     * Dapatkan jawaban atas pertanyaan pengguna.
     *
     * @param string $question Pertanyaan dari pengguna
     * @param string|null $ipAddress Alamat IP pengguna
     * @param int|null $userId ID pengguna jika sudah login
     * @return array{success: bool, reply: string, matched: bool, faq_id: int|null, related_chips: array, choices?: array, quick_replies: array, requires_login?: bool}
     */
    public function ask(string $question, ?string $ipAddress = null, ?int $userId = null): array
    {
        $cleanedQuestion = trim($question);

        $user = null;
        if ($userId) {
            $user = User::with(['profile.wilayahRt', 'profile.kartuKeluarga', 'role'])->find($userId);
        } elseif (auth()->check()) {
            $user = auth()->user();
            if ($user) {
                $user->loadMissing(['profile.wilayahRt', 'profile.kartuKeluarga', 'role']);
            }
        }

        $defaultChips = self::getSuggestionsForRole($user);

        if ($cleanedQuestion === '') {
            return [
                'success' => false,
                'reply' => 'Silakan ketik pertanyaan Anda terlebih dahulu.',
                'matched' => false,
                'faq_id' => null,
                'related_chips' => $defaultChips,
                'quick_replies' => $defaultChips,
            ];
        }

        $normQuery = $this->normalizeText($cleanedQuestion);

        // 1. CEK PERLINDUNGAN DATA WARGA LAIN (Anti-Leak) (Poin 2)
        // Bot tidak boleh menampilkan data warga lain, walau diminta dengan nama atau NIK
        if ($this->isRequestingOtherUserData($cleanedQuestion, $normQuery, $user)) {
            return [
                'success' => true,
                'reply' => self::OTHER_USER_DATA_MESSAGE,
                'matched' => true,
                'faq_id' => null,
                'related_chips' => $defaultChips,
                'quick_replies' => $defaultChips,
            ];
        }

        // 2. CEK PERTANYAAN IDENTITAS ("kamu tahu namaku?", "siapa aku", "aku siapa", "nama saya") (Poin 1)
        if ($this->isIdentityQuestion($normQuery)) {
            if ($user) {
                $namaLengkap = e(optional($user->profile)->nama_lengkap ?? $user->name);
                $roleLabel = self::getRoleLabel($user);
                $rtLabel = self::getRtLabel($user);

                $reply = "Anda adalah {$namaLengkap}, terdaftar sebagai {$roleLabel} di {$rtLabel} RW 01.";
                return [
                    'success' => true,
                    'reply' => $reply,
                    'matched' => true,
                    'faq_id' => null,
                    'related_chips' => $defaultChips,
                    'quick_replies' => $defaultChips,
                ];
            } else {
                return [
                    'success' => true,
                    'reply' => 'Anda belum masuk, jadi saya belum tahu siapa Anda. Silakan masuk dulu.',
                    'matched' => true,
                    'faq_id' => null,
                    'requires_login' => true,
                    'related_chips' => $defaultChips,
                    'quick_replies' => $defaultChips,
                ];
            }
        }

        // 3. CEK PERTANYAAN DATA PRIBADI AKUN SENDIRI (Surat, Iuran, Bank Sampah, Profil) (Poin 2)
        $personalDataReply = $this->handlePersonalDataQuestion($normQuery, $user);
        if ($personalDataReply !== null) {
            return [
                'success' => true,
                'reply' => $personalDataReply,
                'matched' => true,
                'faq_id' => null,
                'related_chips' => $defaultChips,
                'quick_replies' => $defaultChips,
            ];
        }

        // 4. PENCOCOKAN DENGAN TABEL FAQ
        $matchResult = $this->evaluateQuestion($cleanedQuestion);

        // KASUS A: Dua kandidat berdekatan -> Tanya "Maksud Anda: A atau B?"
        if ($matchResult['has_disambiguation']) {
            $cand1 = $matchResult['candidates'][0]['faq'];
            $cand2 = $matchResult['candidates'][1]['faq'];
            $choices = [$cand1->pertanyaan, $cand2->pertanyaan];

            return [
                'success' => true,
                'reply' => "Maksud Anda: {$cand1->pertanyaan} atau {$cand2->pertanyaan}?",
                'matched' => true,
                'faq_id' => null,
                'choices' => $choices,
                'related_chips' => $choices,
                'quick_replies' => $choices,
            ];
        }

        // KASUS B: Ada pemenang dengan skor tinggi (>= 60%)
        if ($matchResult['best_match'] && $matchResult['highest_score'] >= 60) {
            $faq = $matchResult['best_match'];

            // Jika FAQ berstatus DRAFT / Tidak Aktif (Poin 3)
            // "Jika FAQ masih DRAFT dan ditanya: 'Informasi ini belum diisi pengurus. Silakan tekan tombol CS di atas untuk bertanya langsung ke pengurus.' Jangan menjawab dengan FAQ lain yang tidak relevan."
            if (! $faq->is_active) {
                $relatedChips = $this->getRelatedChipsForFaq($faq, 3, $user);
                return [
                    'success' => true,
                    'reply' => self::DRAFT_MESSAGE,
                    'matched' => false,
                    'faq_id' => $faq->id,
                    'related_chips' => $relatedChips,
                    'quick_replies' => $relatedChips,
                ];
            }

            // FAQ Aktif -> Isi placeholder data pengguna jika ada, lalu kirim jawaban
            $parsedAnswer = $this->replacePlaceholders($faq->jawaban, $user);
            $relatedChips = $this->getRelatedChipsForFaq($faq, 3, $user);

            return [
                'success' => true,
                'reply' => $parsedAnswer,
                'matched' => true,
                'faq_id' => $faq->id,
                'related_chips' => $relatedChips,
                'quick_replies' => $relatedChips,
            ];
        }

        // KASUS C: Skor rendah (< 40%) atau tidak ada yang cocok
        // Catat ke tabel pertanyaan belum terjawab
        AssistantUnansweredQuestion::create([
            'pertanyaan' => $cleanedQuestion,
            'ip_address' => $ipAddress,
            'user_id' => $user ? $user->id : null,
            'status' => 'belum_dijawab',
        ]);

        $suggestionChips = $this->getFallbackSuggestions($matchResult['candidates'], 3, $user);

        return [
            'success' => true,
            'reply' => self::FALLBACK_MESSAGE,
            'matched' => false,
            'faq_id' => null,
            'related_chips' => $suggestionChips,
            'suggestion_chips' => $suggestionChips,
            'quick_replies' => $suggestionChips,
        ];
    }

    /**
     * Deteksi apakah query meminta data orang lain (Anti-Leak)
     */
    protected function isRequestingOtherUserData(string $rawQuery, string $normQuery, ?User $currentUser): bool
    {
        // 1. Cek jika query menyertakan 16 digit NIK
        if (preg_match('/\b\d{16}\b/', $rawQuery, $matches)) {
            $requestedNik = $matches[0];
            $myNik = optional(optional($currentUser)->profile)->nik;
            // Jika ada NIK 16 digit dan bukan NIK milik pengguna yang login -> tolak!
            if (!$currentUser || !$myNik || $requestedNik !== $myNik) {
                return true;
            }
        }

        // 2. Cek frasa permintaan data orang/warga lain
        $otherDataKeywords = [
            'warga lain', 'orang lain', 'data tetangga', 'data orang',
            'saldo warga lain', 'saldo orang lain', 'surat orang lain',
            'tagihan orang lain', 'nik orang lain', 'nik warga lain',
            'kk orang lain', 'data warga dengan nik', 'cek nik orang'
        ];

        foreach ($otherDataKeywords as $phrase) {
            if (str_contains($normQuery, $phrase)) {
                return true;
            }
        }

        // 3. Cek pola kueri data seseorang (misal: "saldo bank sampah siti", "tagihan pak joko", "surat atas nama budi")
        if (preg_match('/(?:saldo|tagihan|surat|nik|kk|data)\s+(?:bank\s+sampah\s+|iuran\s+)?(?:milik|punya|atas\s+nama|pak|bu|bapak|ibu|mas|mbak|sdr|sdri)?\s*([a-z]{3,})/i', $normQuery, $m)) {
            $targetName = $m[1];
            $systemKeywords = [
                'saya', 'aku', 'ku', 'kita', 'anda', 'kamu', 'sendiri',
                'bank', 'sampah', 'iuran', 'surat', 'posyandu', 'ronda',
                'rw', 'rt', 'warga', 'pengantar', 'terakhir', 'bagaimana',
                'cara', 'syarat', 'kapan', 'dimana', 'berapa'
            ];

            if (!in_array($targetName, $systemKeywords, true)) {
                if ($currentUser) {
                    $myName = mb_strtolower(optional($currentUser->profile)->nama_lengkap ?? $currentUser->name, 'UTF-8');
                    $myFirstName = mb_strtolower(self::getFirstName(optional($currentUser->profile)->nama_lengkap ?? $currentUser->name), 'UTF-8');
                    if (!str_contains($myName, $targetName) && $targetName !== $myFirstName) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Deteksi apakah query merupakan pertanyaan tentang identitas diri
     */
    protected function isIdentityQuestion(string $normQuery): bool
    {
        $patterns = [
            'kamu tahu namaku',
            'kamu tau namaku',
            'kamu tahu nama saya',
            'kamu tau nama saya',
            'siapa aku',
            'siapa saya',
            'aku siapa',
            'nama saya',
            'nama ku',
            'namaku siapa',
            'siapakah saya',
            'apakah kamu tahu nama saya',
            'tahu nama saya',
            'tau nama saya',
            'apakah kamu kenal saya',
            'kamu kenal saya',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normQuery, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Penanganan kueri data pribadi milik pengguna yang login (Poin 2)
     */
    protected function handlePersonalDataQuestion(string $normQuery, ?User $user): ?string
    {
        // 0. Handler Khusus Pengurus / Administrasi (Rekap Warga, Verifikasi, Kas, Audit)
        if (
            str_contains($normQuery, 'rekap warga') ||
            str_contains($normQuery, 'jumlah warga') ||
            str_contains($normQuery, 'total warga') ||
            str_contains($normQuery, 'rekap kependudukan')
        ) {
            try {
                $totalWarga = \App\Models\UserProfile::where('status', 'aktif')->count();
                if ($user && $user->isAdminRt()) {
                    $rtId = $user->managedRtId() ?? $user->rt_id ?? optional($user->profile)->rt_id;
                    $rtKode = optional(optional($user->profile)->wilayahRt)->kode_rt ?? 'RT';
                    $countRt = \App\Models\UserProfile::where('status', 'aktif')->where('rt_id', $rtId)->count();
                    return "Saat ini terdapat {$countRt} warga aktif terdaftar di RT {$rtKode} RW 01 (total seluruh RW 01: {$totalWarga} warga). Rincian data kependudukan dapat diakses pada menu Data Warga.";
                }

                return "Saat ini terdapat {$totalWarga} warga aktif terdaftar di lingkungan RW 01 yang tersebar di wilayah RT 01 s/d RT 08. Rincian data kependudukan per RT dapat dilihat pada menu Data Warga.";
            } catch (\Throwable $e) {
                return 'Data rekapitulasi kependudukan warga RW 01 dapat dilihat langsung pada menu Data Warga.';
            }
        }

        if (
            str_contains($normQuery, 'verifikasi warga') ||
            str_contains($normQuery, 'verifikasi pendaftaran') ||
            str_contains($normQuery, 'acc warga')
        ) {
            if ($user && ($user->isAdminRt() || $user->isAdminRw())) {
                return 'Pengurus dapat memverifikasi permohonan pendaftaran warga baru melalui menu Warga > Verifikasi Warga. Anda dapat memeriksa NIK, KK, dan data pendukung sebelum menyetujui akun warga.';
            }
            return 'Pendaftaran akun warga baru akan diverifikasi oleh Ketua RT setempat sebelum akun aktif. Silakan hubungi Ketua RT wilayah Anda jika verifikasi membutuhkan konfirmasi cepat.';
        }

        if (
            str_contains($normQuery, 'pengajuan surat warga') ||
            str_contains($normQuery, 'permohonan surat warga') ||
            str_contains($normQuery, 'verifikasi surat')
        ) {
            if ($user && ($user->isAdminRt() || $user->isAdminRw())) {
                return 'Pengurus RT/RW dapat meninjau, memverifikasi, dan menyetujui pengajuan surat pengantar warga melalui menu Layanan > Permohonan Surat.';
            }
        }

        if (str_contains($normQuery, 'laporan kas') || str_contains($normQuery, 'kas rt') || str_contains($normQuery, 'buku kas')) {
            if ($user && $user->isAdminRt()) {
                return 'Pencatatan kas dan laporan mutasi keuangan kas RT dapat dikelola langsung melalui menu Keuangan > Buku Kas RT.';
            }
            return 'Informasi kas lingkungan RT dan RW dikelola secara transparan oleh bendahara wilayah dan dapat dipantau pada menu Keuangan.';
        }

        if (str_contains($normQuery, 'audit keuangan') || str_contains($normQuery, 'audit kas')) {
            return 'Catatan rekam jejak audit dan riwayat transaksi keuangan wilayah dapat ditinjau melalui menu Keuangan > Audit Trail.';
        }

        if (str_contains($normQuery, 'perangkat rfid') || str_contains($normQuery, 'status rfid')) {
            return 'Status dan konfigurasi perangkat scanner RFID (gerbang, bank sampah, dan presensi) dapat dikelola melalui menu Super Admin > Perangkat RFID.';
        }

        if (str_contains($normQuery, 'kelola faq') || str_contains($normQuery, 'kelola asisten')) {
            return 'Pengelolaan pertanyaan dan jawaban FAQ Asisten Smart RW dapat diatur langsung melalui menu Super Admin > Kelola FAQ Asisten.';
        }

        // Handler Jadwal Ronda
        if (
            str_contains($normQuery, 'jadwal ronda') ||
            str_contains($normQuery, 'ronda malam') ||
            str_contains($normQuery, 'pos ronda') ||
            str_contains($normQuery, 'giliran ronda') ||
            $normQuery === 'ronda'
        ) {
            return "Jadwal ronda malam dapat dilihat pada dashboard akun warga Anda di kartu 'Jadwal Ronda Saya' atau menu 'Keamanan / Ronda'.\n\nRonda dimulai pukul 22.00 WIB di Pos Ronda RW 01. Jika Anda berhalangan hadir, silakan ajukan 'Tukar Jadwal' kepada rekan ronda sebelum giliran jaga.";
        }

        // 1. Status pengajuan surat terakhir akun sendiri
        if (
            str_contains($normQuery, 'status surat saya') ||
            str_contains($normQuery, 'surat saya') ||
            str_contains($normQuery, 'surat pengantar saya') ||
            $normQuery === 'status surat' ||
            $normQuery === 'pengajuan surat'
        ) {
            if (!$user) {
                return 'Anda belum masuk, jadi saya belum tahu data pengajuan surat Anda. Silakan masuk dulu.';
            }

            try {
                $surat = PengajuanSurat::where('user_id', $user->id)
                    ->with('jenisSurat')
                    ->latest()
                    ->first();

                if ($surat) {
                    $namaSurat = $surat->jenisSurat ? $surat->jenisSurat->nama_surat : 'Pengantar';
                    $nomor = $surat->nomor_pengajuan ?: ($surat->nomor_surat ?: '-');
                    $status = strtoupper($surat->status);
                    return "Pengajuan surat terakhir Anda adalah Surat " . e($namaSurat) . " dengan nomor pengajuan {$nomor} dan status saat ini: {$status}.";
                }

                return 'Anda belum memiliki riwayat pengajuan surat di sistem Smart RW.';
            } catch (\Throwable $e) {
                return 'Informasi status pengajuan surat Anda dapat dilihat langsung pada menu Layanan Surat.';
            }
        }

        // 2. Tagihan iuran sendiri
        if (
            str_contains($normQuery, 'tagihan iuran') ||
            str_contains($normQuery, 'tagihan saya') ||
            str_contains($normQuery, 'cek tagihan') ||
            str_contains($normQuery, 'iuran saya')
        ) {
            if (!$user) {
                return 'Anda belum masuk, jadi saya belum tahu tagihan iuran Anda. Silakan masuk dulu.';
            }

            try {
                $pendingCount = Pembayaran::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count();

                if ($pendingCount > 0) {
                    return "Anda memiliki {$pendingCount} tagihan iuran yang menunggu pembayaran atau verifikasi pengurus. Silakan cek menu Keuangan > Pembayaran Iuran untuk menyelesaikan pembayaran.";
                }

                return 'Saat ini tidak ada tagihan iuran yang tertunggak untuk akun Anda. Terima kasih telah tertib membayar iuran warga!';
            } catch (\Throwable $e) {
                return 'Rincian tagihan iuran Anda dapat dilihat langsung pada menu Keuangan > Pembayaran Iuran.';
            }
        }

        // 3. Saldo bank sampah sendiri
        if (
            str_contains($normQuery, 'saldo bank sampah') ||
            str_contains($normQuery, 'saldo sampah') ||
            str_contains($normQuery, 'tabungan sampah') ||
            str_contains($normQuery, 'cek saldo sampah')
        ) {
            if (!$user) {
                return 'Anda belum masuk, jadi saya belum tahu saldo bank sampah Anda. Silakan masuk dulu.';
            }

            try {
                $saldo = SaldoSampah::where('user_id', $user->id)->first();
                if ($saldo && ($saldo->saldo > 0 || $saldo->total_berat_kg > 0)) {
                    $rupiah = 'Rp ' . number_format($saldo->saldo, 0, ',', '.');
                    $berat = number_format($saldo->total_berat_kg, 1, ',', '.');
                    return "Saldo tabungan Bank Sampah Anda saat ini adalah {$rupiah} dengan akumulasi sampah terpilah sebanyak {$berat} kg.";
                }

                return 'Anda belum memiliki saldo tabungan Bank Sampah. Silakan setorkan sampah anorganik terpilah Anda pada jadwal penimbangan di wilayah.';
            } catch (\Throwable $e) {
                return 'Saldo tabungan Bank Sampah Anda dapat dilihat langsung pada menu Bank Sampah.';
            }
        }

        // 4. Data ringkas akun / NIK / KK sendiri
        if (
            (str_contains($normQuery, 'nik saya') || str_contains($normQuery, 'kk saya') || str_contains($normQuery, 'data saya') || str_contains($normQuery, 'profil saya')) &&
            !str_contains($normQuery, 'cara')
        ) {
            if (!$user) {
                return 'Anda belum masuk, jadi saya belum tahu data akun Anda. Silakan masuk dulu.';
            }

            $nama = e(optional($user->profile)->nama_lengkap ?? $user->name);
            $role = self::getRoleLabel($user);
            $rt = self::getRtLabel($user);
            $nik = self::maskIdentifier(optional($user->profile)->nik);
            $noKk = self::maskIdentifier(optional(optional($user->profile)->kartuKeluarga)->no_kk);

            return "Berikut data akun Anda:\n• Nama: {$nama}\n• Peran: {$role}\n• Wilayah: {$rt} RW 01\n• NIK: {$nik}\n• No. KK: {$noKk}";
        }

        return null;
    }

    /**
     * Ganti placeholder {nama}, {rt}, {saldo_bank_sampah}, {tagihan_iuran} pada teks FAQ (Poin 2)
     */
    public function replacePlaceholders(string $text, ?User $user): string
    {
        if ($user) {
            $nama = e(self::getFirstName(optional($user->profile)->nama_lengkap ?? $user->name));
            $rt = self::getRtLabel($user);

            // Saldo bank sampah
            try {
                $saldoSampahVal = SaldoSampah::where('user_id', $user->id)->value('saldo');
                $saldoStr = ($saldoSampahVal !== null) ? 'Rp ' . number_format($saldoSampahVal, 0, ',', '.') : 'Rp 0';
            } catch (\Throwable $e) {
                $saldoStr = 'silakan cek di menu terkait';
            }

            // Tagihan iuran
            try {
                $pending = Pembayaran::where('user_id', $user->id)->where('status', 'pending')->count();
                $tagihanStr = $pending > 0 ? "{$pending} tagihan pending" : 'tidak ada tagihan tertunggak';
            } catch (\Throwable $e) {
                $tagihanStr = 'silakan cek di menu terkait';
            }
        } else {
            $nama = 'silakan cek di menu terkait';
            $rt = 'silakan cek di menu terkait';
            $saldoStr = 'silakan cek di menu terkait';
            $tagihanStr = 'silakan cek di menu terkait';
        }

        $search = ['{nama}', '{rt}', '{saldo_bank_sampah}', '{tagihan_iuran}'];
        $replace = [$nama, $rt, $saldoStr, $tagihanStr];

        return str_replace($search, $replace, $text);
    }

    /**
     * Evaluasi pertanyaan pengguna terhadap seluruh FAQ (Aktif maupun Draft)
     */
    protected function evaluateQuestion(string $rawQuery): array
    {
        $faqs = AssistantFaq::orderBy('urutan', 'asc')->get();

        if ($faqs->isEmpty()) {
            return [
                'best_match' => null,
                'highest_score' => 0,
                'has_disambiguation' => false,
                'candidates' => [],
            ];
        }

        $normQuery = $this->normalizeText($rawQuery);
        $spacelessQuery = str_replace(' ', '', $normQuery);
        $queryContentWords = $this->extractContentWords($normQuery);

        $scoredCandidates = [];

        foreach ($faqs as $faq) {
            $normFaqQ = $this->normalizeText($faq->pertanyaan);
            $spacelessFaqQ = str_replace(' ', '', $normFaqQ);
            $faqContentWords = $this->extractContentWords($normFaqQ);

            // Ekstrak keyword FAQ
            $keywords = [];
            if (!empty($faq->kata_kunci)) {
                $rawKws = array_map('trim', explode(',', (string) $faq->kata_kunci));
                foreach ($rawKws as $kw) {
                    if ($kw !== '') {
                        $keywords[] = $this->normalizeText($kw);
                    }
                }
            }

            $score = 0;

            // 1. Spaceless Exact Match pada Pertanyaan FAQ
            if ($spacelessQuery === $spacelessFaqQ) {
                $score = 100;
            } elseif (str_contains($spacelessQuery, $spacelessFaqQ) || str_contains($spacelessFaqQ, $spacelessQuery)) {
                $score = max($score, 85);
            }

            // 2. Cek Spaceless Match pada Kata Kunci FAQ
            foreach ($keywords as $kw) {
                $spacelessKw = str_replace(' ', '', $kw);
                if ($spacelessKw === '') continue;

                if ($spacelessQuery === $spacelessKw) {
                    $score = max($score, 95);
                } elseif (str_contains($spacelessQuery, $spacelessKw)) {
                    $score = max($score, 85);
                } elseif (str_contains($spacelessKw, $spacelessQuery) && mb_strlen($spacelessQuery) >= 4) {
                    $score = max($score, 75);
                }
            }

            // 3. Evaluasi Kata Isi (Content Words)
            if (!empty($queryContentWords)) {
                $matchedContentCount = 0;
                $allFaqContext = $normFaqQ . ' ' . implode(' ', $keywords);

                foreach ($queryContentWords as $qWord) {
                    if ($this->wordMatchesInContext($qWord, $allFaqContext, $faqContentWords)) {
                        $matchedContentCount++;
                    }
                }

                $matchRatio = $matchedContentCount / count($queryContentWords);

                if ($matchRatio >= 0.75) {
                    $score = max($score, (int) round(70 + ($matchRatio * 25)));
                } elseif ($matchRatio >= 0.49) {
                    $score = max($score, (int) round(45 + ($matchRatio * 20)));
                } elseif ($matchedContentCount > 0) {
                    $score = max($score, 25);
                } else {
                    if ($score < 80) {
                        $score = 0;
                    }
                }
            }

            // 4. Periksa Phrasal Matching Khusus
            if (str_contains($normQuery, 'jadi warga') || str_contains($normQuery, 'warga baru') || str_contains($spacelessQuery, 'jadiwarga')) {
                if (str_contains($normFaqQ, 'warga rw 01') || str_contains($normFaqQ, 'menjadi warga') || str_contains(implode(' ', $keywords), 'jadi warga')) {
                    $score = max($score, 90);
                }
            }

            if (str_contains($normQuery, 'posyandu') || str_contains($normQuery, 'imunisasi') || str_contains($normQuery, 'timbang balita')) {
                if (str_contains($normFaqQ, 'posyandu')) {
                    $score = max($score, 95);
                }
            }

            if (str_contains($normQuery, 'bank sampah') || str_contains($normQuery, 'setor sampah') || str_contains($normQuery, 'tabungan sampah')) {
                if (str_contains($normFaqQ, 'bank sampah')) {
                    $score = max($score, 95);
                }
            }

            if (str_contains($normQuery, 'kerja bakti') || str_contains($normQuery, 'gotong royong')) {
                if (str_contains($normFaqQ, 'kerja bakti')) {
                    $score = max($score, 95);
                }
            }

            if (str_contains($normQuery, 'lupa') && (str_contains($normQuery, 'sandi') || str_contains($normQuery, 'password'))) {
                if (str_contains($normFaqQ, 'lupa kata sandi') || str_contains($normFaqQ, 'lupa sandi')) {
                    $score = max($score, 95);
                }
            }

            if (str_contains($normQuery, 'daftar') && (str_contains($normQuery, 'akun') || str_contains($normQuery, 'warga') || str_contains($normQuery, 'registrasi'))) {
                if (str_contains($normFaqQ, 'mendaftar akun warga') || str_contains($normFaqQ, 'daftar akun')) {
                    $score = max($score, 95);
                }
            }

            $scoredCandidates[] = [
                'faq' => $faq,
                'score' => $score,
            ];
        }

        // Urutkan kandidat dari skor tertinggi
        usort($scoredCandidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        $top1 = $scoredCandidates[0] ?? null;
        $top2 = $scoredCandidates[1] ?? null;

        $hasDisambiguation = false;

        // Cek dua kandidat berdekatan:
        // Disambiguasi hanya jika BUKAN exact match (skor < 100), skor keduanya >= 45, dan selisihnya <= 10
        if (
            $top1 && $top2 &&
            $top1['score'] < 100 &&
            $top1['score'] >= 45 && $top2['score'] >= 45 &&
            abs($top1['score'] - $top2['score']) <= 10 &&
            $top1['faq']->id !== $top2['faq']->id
        ) {
            $hasDisambiguation = true;
        }

        return [
            'best_match' => $top1['faq'] ?? null,
            'highest_score' => $top1['score'] ?? 0,
            'has_disambiguation' => $hasDisambiguation,
            'candidates' => $scoredCandidates,
        ];
    }

    /**
     * Periksa apakah suatu kata isi cocok dengan konteks FAQ
     */
    protected function wordMatchesInContext(string $qWord, string $faqContext, array $faqContentWords): bool
    {
        // 1. Substring batas kata (whole word) dalam teks konteks
        if (preg_match('/\b' . preg_quote($qWord, '/') . '\b/i', $faqContext)) {
            return true;
        }

        // 2. Cek Peta Sinonim Topik (hindari pencocokan parsial seperti 'kas' pada 'lokasi')
        foreach (self::TOPIC_SYNONYMS as $topicKey => $synonyms) {
            if ($qWord === $topicKey || in_array($qWord, $synonyms, true)) {
                foreach ($synonyms as $syn) {
                    if (str_contains($syn, ' ')) {
                        if (str_contains($faqContext, $syn)) {
                            return true;
                        }
                    } else {
                        if (preg_match('/\b' . preg_quote($syn, '/') . '\b/i', $faqContext)) {
                            return true;
                        }
                    }
                }
            }
        }

        // 3. Toleransi Typo / Salah Ketik (Levenshtein atau similarity >= 80%)
        if (mb_strlen($qWord) >= 4) {
            $contextTokens = array_filter(explode(' ', $faqContext));
            foreach ($contextTokens as $cToken) {
                if (mb_strlen($cToken) < 4) continue;

                if (levenshtein($qWord, $cToken) <= 1) {
                    return true;
                }

                similar_text($qWord, $cToken, $percent);
                if ($percent >= 80.0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Normalisasi teks: lowercase, slang replacement, pemisahan kata majemuk tertentu, tanda baca dibersihkan
     */
    public function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        // Ganti variasi sambung khusus sebelum pemisahan
        $text = str_replace(
            ['jadiwarga', 'daftarakun', 'lupasandi', 'lupapassword', 'banksampah', 'kerjabakti'],
            ['jadi warga', 'daftar akun', 'lupa sandi', 'lupa sandi', 'bank sampah', 'kerja bakti'],
            $text
        );

        // Hapus karakter non huruf dan non angka
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // Rapikan spasi
        $text = preg_replace('/\s+/', ' ', $text);
        $tokens = array_filter(explode(' ', trim($text)));

        // Ganti kata-kata slang / gaul dengan bentuk baku
        $normalizedTokens = array_map(function ($token) {
            return self::SLANG_MAP[$token] ?? $token;
        }, $tokens);

        return implode(' ', $normalizedTokens);
    }

    /**
     * Ekstraksi kata isi (Content Words) dengan membuang stopwords
     */
    public function extractContentWords(string $normalizedText): array
    {
        $tokens = array_filter(explode(' ', $normalizedText));
        $contentWords = [];

        foreach ($tokens as $token) {
            if (!in_array($token, self::STOPWORDS, true) && mb_strlen($token) >= 2) {
                $contentWords[] = $token;
            }
        }

        return array_values($contentWords);
    }

    /**
     * Ambil 2 sampai 3 chip pertanyaan terkait untuk FAQ tertentu
     */
    public function getRelatedChipsForFaq(AssistantFaq $faq, int $limit = 3, ?User $user = null): array
    {
        $chips = AssistantFaq::where('is_active', true)
            ->where('id', '!=', $faq->id)
            ->orderByRaw("CASE WHEN kategori = ? THEN 0 ELSE 1 END", [$faq->kategori])
            ->orderBy('urutan', 'asc')
            ->take($limit)
            ->pluck('pertanyaan')
            ->toArray();

        if (count($chips) < $limit) {
            $defaults = array_diff(self::getSuggestionsForRole($user), array_merge([$faq->pertanyaan], $chips));
            $chips = array_merge($chips, array_slice($defaults, 0, $limit - count($chips)));
        }

        return array_values(array_slice($chips, 0, $limit));
    }

    /**
     * Ambil 3 saran pertanyaan terdekat untuk pertanyaan yang tidak cocok
     */
    public function getFallbackSuggestions(array $scoredCandidates, int $limit = 3, ?User $user = null): array
    {
        $suggestions = [];

        foreach ($scoredCandidates as $cand) {
            if ($cand['score'] > 0 && $cand['faq']->is_active) {
                $suggestions[] = $cand['faq']->pertanyaan;
                if (count($suggestions) >= $limit) break;
            }
        }

        if (count($suggestions) < $limit) {
            $activeFaqs = AssistantFaq::where('is_active', true)
                ->orderBy('urutan', 'asc')
                ->take($limit)
                ->pluck('pertanyaan')
                ->toArray();

            foreach ($activeFaqs as $af) {
                if (!in_array($af, $suggestions, true)) {
                    $suggestions[] = $af;
                    if (count($suggestions) >= $limit) break;
                }
            }
        }

        if (count($suggestions) < $limit) {
            foreach (self::getSuggestionsForRole($user) as $qs) {
                if (!in_array($qs, $suggestions, true)) {
                    $suggestions[] = $qs;
                    if (count($suggestions) >= $limit) break;
                }
            }
        }

        return array_values(array_slice($suggestions, 0, $limit));
    }

    /**
     * Dapatkan saran pertanyaan cepat (chips) berdasarkan peran pengguna (Poin 1)
     */
    public static function getSuggestionsForRole(?User $user): array
    {
        if (!$user) {
            return [
                'Cara mendaftar akun warga',
                'Lupa kata sandi',
                'Syarat surat pengantar',
                'Cara bayar iuran',
                'Jadwal ronda',
            ];
        }

        if ($user->isSuperAdmin()) {
            return [
                'Rekap warga RW 01',
                'Syarat surat pengantar',
                'Cara bayar iuran',
                'Jadwal ronda',
                'Cara mendaftar akun warga',
            ];
        }

        if ($user->isAdminRw()) {
            return [
                'Rekap warga RW 01',
                'Syarat surat pengantar',
                'Cara bayar iuran',
                'Jadwal ronda',
                'Cara mendaftar akun warga',
            ];
        }

        if ($user->isAdminRt()) {
            return [
                'Verifikasi warga baru',
                'Syarat surat pengantar',
                'Cara bayar iuran',
                'Jadwal ronda',
                'Cara mendaftar akun warga',
            ];
        }

        // Default: Warga
        return [
            'Status surat saya',
            'Cek tagihan iuran',
            'Cek saldo bank sampah',
            'Cara bayar iuran',
            'Jadwal ronda',
        ];
    }

    /**
     * Dapatkan saran pertanyaan cepat umum
     */
    public function getQuickSuggestions(?User $user = null): array
    {
        return self::getSuggestionsForRole($user);
    }

    /**
     * Format nama depan dengan huruf awal kapital dan aman dari XSS
     */
    public static function getFirstName(?string $fullName): string
    {
        if (!$fullName) return 'Warga';
        // Hapus blok script, style, iframe beserta isinya
        $cleanText = preg_replace('/<(script|style|iframe)\b[^>]*>(.*?)<\/\1>/is', '', (string) $fullName);
        // Hapus sisa tag HTML lainnya
        $cleanText = trim(strip_tags($cleanText));
        if ($cleanText === '') {
            $cleanText = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', (string) $fullName));
        }
        if ($cleanText === '') {
            return 'Warga';
        }
        $parts = preg_split('/\s+/', $cleanText);
        $first = $parts[0] ?? 'Warga';
        $formatted = mb_convert_case(mb_strtolower($first, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        return htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Ambil label peran pengguna
     */
    public static function getRoleLabel(?User $user): string
    {
        if (!$user) return 'Tamu';
        if ($user->isSuperAdmin()) return 'Super Admin';
        if ($user->isAdminRw()) return 'Ketua RW';
        if ($user->isAdminRt()) return 'Ketua RT';
        return 'Warga';
    }

    /**
     * Ambil label RT pengguna
     */
    public static function getRtLabel(?User $user): string
    {
        if (!$user) return 'RT 01';
        $rtKode = $user->getRtKode() ?? optional(optional($user->profile)->wilayahRt)->kode_rt ?? ($user->rt_id ? sprintf('%02d', $user->rt_id) : '01');
        return 'RT ' . $rtKode;
    }

    /**
     * Samarkan NIK atau No KK: contoh 3374********0013 (Poin 2)
     */
    public static function maskIdentifier(?string $id): string
    {
        if (!$id) return '-';
        $clean = preg_replace('/\D+/', '', (string) $id);
        $len = strlen($clean);
        if ($len >= 12) {
            return substr($clean, 0, 4) . '********' . substr($clean, -4);
        }
        if ($len >= 6) {
            return substr($clean, 0, 2) . str_repeat('*', max(2, $len - 4)) . substr($clean, -2);
        }
        return str_repeat('*', $len);
    }

    /**
     * Ambil nomor WhatsApp pengurus dari AppSetting atau config
     */
    public static function getWhatsAppNumber(): string
    {
        try {
            $rawNumber = AppSetting::get('whatsapp_number', config('rw-smart.whatsapp_number', '6285183110392'));
        } catch (\Throwable $e) {
            $rawNumber = config('rw-smart.whatsapp_number', '6285183110392');
        }
        $cleanNumber = preg_replace('/\D+/', '', (string) $rawNumber);

        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        }

        return $cleanNumber ?: '6285183110392';
    }

    /**
     * Buat URL langsung ke WhatsApp pengurus
     */
    public static function getWhatsAppUrl(): string
    {
        return 'https://wa.me/' . self::getWhatsAppNumber();
    }
}
