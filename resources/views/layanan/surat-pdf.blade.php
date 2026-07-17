<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $surat->jenisSurat->nama ?? 'Surat Pengantar' }}</title>
    <style>
        @page {
            margin: 2.5cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        .page-footer {
            position: fixed;
            right: 0;
            bottom: -1.5cm;
            left: 0;
            color: #555;
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
            text-align: center;
        }

        .page-number::after {
            content: counter(page);
        }

        .letterhead {
            width: 100%;
            border-bottom: 3px double #111;
            margin-bottom: 22px;
            padding-bottom: 10px;
        }

        .letterhead td {
            padding: 0;
            vertical-align: middle;
        }

        .logo-cell {
            width: 92px;
            text-align: left;
        }

        .logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
        }

        .institution {
            padding-right: 92px !important;
            text-align: center;
        }

        .institution .government {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .institution .name {
            margin: 2px 0;
            font-size: 17pt;
            font-weight: bold;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .institution .address {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
        }

        .title {
            margin-bottom: 22px;
            text-align: center;
        }

        .title h1 {
            display: inline-block;
            margin: 0 0 3px;
            border-bottom: 1px solid #111;
            font-size: 14pt;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .document-meta,
        .identity,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .document-meta {
            margin-bottom: 18px;
        }

        .document-meta td,
        .identity td {
            padding: 2px 0;
            vertical-align: top;
        }

        .label {
            width: 105px;
        }

        .separator {
            width: 15px;
        }

        p {
            margin: 0 0 14px;
            text-align: justify;
            text-indent: 36px;
        }

        .identity {
            margin: 4px 0 18px 36px;
            width: calc(100% - 36px);
        }

        .identity .label {
            width: 145px;
        }

        .note {
            margin-top: 8px;
            padding: 8px 10px;
            border: 1px solid #777;
            font-size: 11pt;
        }

        .signature-table {
            margin-top: 34px;
            page-break-inside: avoid;
        }

        .signature-space {
            height: 76px;
        }

        .signature-cell {
            width: 260px;
            text-align: center;
        }

        .signer-name {
            display: inline-block;
            min-width: 190px;
            border-bottom: 1px solid #111;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-footer">
        Dokumen resmi RW Smart &mdash; Halaman <span class="page-number"></span>
    </div>

    <table class="letterhead">
        <tr>
            <td class="logo-cell">
                @if($logoData)
                    <img class="logo" src="{{ $logoData }}" alt="Logo RW">
                @endif
            </td>
            <td class="institution">
                <div class="government">Pemerintah Lingkungan Rukun Warga</div>
                <div class="name">Sekretariat RW</div>
                <div class="address">
                    Sistem Informasi RW Smart<br>
                    Sekretariat Rukun Warga setempat
                </div>
            </td>
        </tr>
    </table>

    <div class="title">
        <h1>{{ $surat->jenisSurat->nama ?? 'Surat Pengantar' }}</h1>
    </div>

    <table class="document-meta">
        <tr>
            <td class="label">Nomor</td><td class="separator">:</td>
            <td>{{ $surat->nomor_surat ?: $surat->nomor_pengajuan }}</td>
        </tr>
        <tr>
            <td class="label">Perihal</td><td class="separator">:</td>
            <td>{{ $surat->jenisSurat->nama ?? 'Surat Pengantar' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td><td class="separator">:</td>
            <td>{{ optional($surat->approved_at)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <p>
        Yang bertanda tangan di bawah ini, Ketua Rukun Warga setempat, menerangkan bahwa warga dengan identitas berikut benar tercatat sebagai warga di lingkungan kami:
    </p>

    <table class="identity">
        <tr><td class="label">Nama Lengkap</td><td class="separator">:</td><td>{{ $surat->user->name ?? '-' }}</td></tr>
        <tr><td class="label">NIK</td><td class="separator">:</td><td>{{ $surat->user->profile->nik ?? '-' }}</td></tr>
        <tr><td class="label">Nomor KK</td><td class="separator">:</td><td>{{ $surat->user->profile->no_kk ?? '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td class="separator">:</td><td>{{ $surat->user->profile->alamat ?? '-' }}</td></tr>
        <tr><td class="label">Keperluan</td><td class="separator">:</td><td>{{ $surat->keperluan }}</td></tr>
    </table>

    <p>
        Surat ini dibuat berdasarkan pengajuan warga untuk dipergunakan sebagaimana mestinya sesuai dengan keperluan tersebut di atas.
    </p>

    @if($surat->catatan)
        <div class="note"><strong>Catatan:</strong> {{ $surat->catatan }}</div>
    @endif

    <table class="signature-table">
        <tr>
            <td></td>
            <td class="signature-cell">
                Ditetapkan pada {{ optional($surat->approved_at)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}<br>
                Ketua RW
                <div class="signature-space"></div>
                <span class="signer-name">{{ $surat->approver->name ?? 'Ketua RW' }}</span><br>
                <span>Ketua Rukun Warga</span>
            </td>
        </tr>
    </table>
</body>
</html>
