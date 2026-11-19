<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartrw:test-mail {email : Alamat email penerima uji coba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim satu email percobaan untuk menguji konfigurasi email / SMTP Smart RW';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Format alamat email tidak valid: {$email}");
            return 1;
        }

        $mailer = config('mail.default', 'smtp');
        $fromAddress = config('mail.from.address', 'noreply@smartrw.id');
        $fromName = config('mail.from.name', 'Smart RW');
        $host = config("mail.mailers.{$mailer}.host");
        $port = config("mail.mailers.{$mailer}.port");
        $encryption = config("mail.mailers.{$mailer}.encryption") ?: config("mail.mailers.{$mailer}.scheme");

        $this->info("Menguji pengiriman email ke: {$email}");
        $this->line("• Driver Mailer : {$mailer}");
        if ($host) {
            $this->line("• Host SMTP     : {$host}:{$port}");
        }
        if ($encryption) {
            $this->line("• Enkripsi      : {$encryption}");
        }
        $this->line("• Pengirim      : {$fromName} <{$fromAddress}>");

        try {
            Mail::raw("Halo,\n\nIni adalah email uji coba dari Smart RW ke {$email}.\nKonfigurasi pengiriman email berhasil dan berjalan normal pada " . now()->format('d/m/Y H:i:s') . ".\n\nSalam,\nTim Smart RW", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Uji Coba Pengiriman Email - Smart RW');
            });

            $this->newLine();
            $this->info("✓ Sukses! Email percobaan berhasil dikirim ke: {$email}");
            $this->line("Silakan periksa kotak masuk (inbox) atau folder Spam pada akun email penerima.");
            return 0;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("✗ Gagal mengirim email percobaan ke {$email}:");

            // Sanitasi pesan error agar tidak menampilkan password/kunci rahasia
            $rawMsg = $e->getMessage();
            $smtpPass = config('mail.mailers.smtp.password');
            if (!empty($smtpPass) && is_string($smtpPass)) {
                $rawMsg = str_replace($smtpPass, '******', $rawMsg);
            }

            $this->line("  Error: " . $rawMsg);
            return 1;
        }
    }
}
