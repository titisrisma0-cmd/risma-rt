<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartrw:demo-accounts {--disable : Nonaktifkan akun demo dan acak kata sandinya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tampilkan atau nonaktifkan semua akun demo Smart RW (email berakhiran @rw-smart.test)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $demoUsers = User::where('email', 'like', '%@rw-smart.test')
            ->with(['profile', 'role'])
            ->orderBy('id')
            ->get();

        $count = $demoUsers->count();

        if ($count === 0) {
            $this->info('Tidak ditemukan akun demo dengan domain @rw-smart.test di database.');
            return 0;
        }

        $this->info("Menemukan {$count} akun demo (@rw-smart.test):");

        $rows = [];
        foreach ($demoUsers as $user) {
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                optional($user->role)->nama_role ?? '-',
                optional($user->profile)->status ?? 'tidak ada',
            ];
        }

        $this->table(['ID', 'Nama Lengkap', 'Email', 'Role', 'Status Profil'], $rows);

        // Jika opsi --disable TIDAK diberikan (dry-run default)
        if (! $this->option('disable')) {
            $this->newLine();
            $this->line('<fg=yellow>Mode Simulasi (Dry-run):</> Tidak ada perubahan pada akun di atas.');
            $this->line('Jalankan dengan opsi <fg=cyan>--disable</> untuk menonaktifkan akun demo dan mengacak kata sandinya.');
            return 0;
        }

        // Jika opsi --disable diberikan, nonaktifkan dan acak kata sandi
        $disabledCount = 0;
        foreach ($demoUsers as $user) {
            // Pengaman ketat: hanya sentuh akun yang berakhiran @rw-smart.test
            $email = strtolower(trim((string) $user->email));
            if (! str_ends_with($email, '@rw-smart.test')) {
                continue;
            }

            // Acak kata sandi dengan string acak 40 karakter
            $user->password = Hash::make(Str::random(40));
            $user->remember_token = null;
            $user->save();

            // Ubah status profil menjadi nonaktif
            if ($user->profile) {
                $user->profile->update(['status' => 'nonaktif']);
            }

            $disabledCount++;
        }

        $this->newLine();
        $this->info("✓ Sukses! {$disabledCount} akun demo berhasil dinonaktifkan dan kata sandinya telah diacak.");
        $this->comment('Catatan: Data kependudukan / warga tetap utuh dan aman (tidak ada data warga yang dihapus).');

        return 0;
    }
}
