<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Kode verifikasi Smart RW</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            border-collapse: separate;
        }
        td {
            padding: 0;
        }
        @media (prefers-color-scheme: dark) {
            body, .email-wrapper {
                background-color: #0f172a !important;
            }
            .email-card {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }
            .text-heading {
                color: #22c55e !important;
            }
            .text-main {
                color: #f1f5f9 !important;
            }
            .text-sub {
                color: #cbd5e1 !important;
            }
            .text-footer {
                color: #94a3b8 !important;
                border-top-color: #334155 !important;
            }
            .otp-box-table {
                background-color: #064e3b !important;
                border-color: #22c55e !important;
            }
            .otp-code {
                color: #86efac !important;
            }
        }
        @media only screen and (max-width: 480px) {
            .email-wrapper {
                padding: 8px 4px !important;
            }
            .card-content {
                padding: 18px 12px !important;
            }
            .otp-code {
                font-size: 28px !important;
                letter-spacing: 4px !important;
            }
        }
    </style>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; width: 100%;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-wrapper" style="width: 100% !important; margin: 0; padding: 16px 8px; background-color: #f4f6f8; box-sizing: border-box;">
        <tr>
            <td align="center" style="padding: 0;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-card" style="width: 100% !important; max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; border-collapse: separate; box-sizing: border-box;">
                    <tr>
                        <td class="card-content" style="padding: 24px 18px; text-align: left; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; box-sizing: border-box;">
                            <h2 class="text-heading" style="margin-top: 0; margin-bottom: 8px; color: #0e7a4b; font-size: 20px; font-weight: 700; line-height: 1.3;">
                                Kode verifikasi Smart RW
                            </h2>
                            <p class="text-main" style="margin-top: 0; margin-bottom: 20px; color: #475569; font-size: 14px; line-height: 1.5;">
                                Berikut adalah kode verifikasi 6 digit untuk mengatur ulang kata sandi akun Smart RW Anda:
                            </p>

                            {{-- Tabel Kotak OTP Responsif Tanpa Lebar Piksel Tetap --}}
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="otp-box-table" style="width: 100% !important; max-width: 100% !important; margin: 20px 0; background-color: #f0fdf4; border: 2px dashed #0e7a4b; border-radius: 10px; box-sizing: border-box;">
                                <tr>
                                    <td align="center" style="padding: 16px 10px; text-align: center; box-sizing: border-box;">
                                        <div class="otp-code" style="font-family: 'Courier New', Courier, monospace, 'Lucida Console'; font-size: 30px; line-height: 1.2; font-weight: 800; letter-spacing: 5px; color: #0e7a4b; text-align: center; display: block; width: 100%; box-sizing: border-box; word-break: break-all;">
                                            {{ $otp }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p class="text-sub" style="margin: 16px 0 8px 0; font-size: 13px; color: #334155; line-height: 1.6;">
                                • Kode ini <strong>berlaku selama 10 menit</strong> dan hanya dapat digunakan <strong>satu kali</strong>.<br>
                                • Demi keamanan, <strong>jangan berikan kode ini kepada siapa pun</strong>, termasuk pengurus RT atau pihak yang mengaku petugas.
                            </p>

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100% !important; margin-top: 20px; border-top: 1px solid #f1f5f9;">
                                <tr>
                                    <td class="text-footer" style="padding-top: 14px; font-size: 12px; color: #64748b; line-height: 1.5;">
                                        Jika Anda tidak merasa meminta pengaturan ulang kata sandi, abaikan email ini. Akun Anda tetap aman.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
