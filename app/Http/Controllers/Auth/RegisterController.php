<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KartuKeluarga;
use App\Models\Notifikasi;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16'],
            'rt_id' => ['required', 'exists:rts,id'],
            'no_kk' => ['required', 'string', 'digits:16'],
            'no_hp' => ['required', 'string', 'max:20'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'rt_id.exists' => 'Wilayah RT yang dipilih tidak valid.',
            'no_kk.required' => 'Nomor Kartu Keluarga wajib diisi.',
            'no_kk.digits' => 'Nomor Kartu Keluarga harus tepat 16 digit angka.',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ])->after(function ($validator) use ($data) {
            if (isset($data['nik']) && strlen($data['nik']) === 16) {
                $existing = UserProfile::where('nik', $data['nik'])->first();
                if ($existing) {
                    if ($existing->user_id !== null) {
                        $validator->errors()->add('nik', 'NIK sudah memiliki akun terdaftar.');
                    } else {
                        // Check identity match: no_kk and rt_id must match
                        $rtMatch = (int) $existing->rt_id === (int) ($data['rt_id'] ?? 0);
                        $kkMatch = $existing->no_kk === ($data['no_kk'] ?? '');
                        if (!$rtMatch || !$kkMatch) {
                            $validator->errors()->add('nik', 'Data NIK terdaftar pada kependudukan tetapi Nomor KK atau Wilayah RT tidak sesuai. Harap hubungi Ketua RT.');
                        }
                    }
                }
            }
        });
    }

    protected function create(array $data)
    {
        $roleWarga = Role::where('nama_role', 'warga')->first();

        return DB::transaction(function () use ($data, $roleWarga) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $roleWarga ? $roleWarga->id : null,
            ]);

            $existingProfile = UserProfile::where('nik', $data['nik'])->first();

            if ($existingProfile && $existingProfile->user_id === null) {
                // Hapus profil dummy yang mungkin dibuat saat User::create
                UserProfile::where('user_id', $user->id)->where('id', '!=', $existingProfile->id)->delete();

                $updatePayload = [
                    'user_id' => $user->id,
                    'role_id' => $roleWarga ? $roleWarga->id : null,
                    'no_hp' => $data['no_hp'],
                    'status' => 'pending',
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('user_profiles', 'catatan_verifikasi')) {
                    $updatePayload['catatan_verifikasi'] = 'Pengaitan akun mandiri warga terdaftar';
                }

                $existingProfile->update($updatePayload);
                $profile = $existingProfile;
            } else {
                // Buat atau hubungkan Kartu Keluarga
                $kk = KartuKeluarga::firstOrCreate(
                    ['no_kk' => $data['no_kk']],
                    [
                        'rt_id' => (int) $data['rt_id'],
                        'alamat' => $data['alamat'] ?? ('Wilayah RT ' . str_pad($data['rt_id'], 2, '0', STR_PAD_LEFT)),
                        'status_rumah' => 'tetap',
                        'kepala_keluarga_user_id' => null,
                    ]
                );

                $profilePayload = [
                    'role_id' => $roleWarga ? $roleWarga->id : null,
                    'rt_id' => (int) $data['rt_id'],
                    'kartu_keluarga_id' => $kk->id,
                    'no_kk' => $data['no_kk'],
                    'nik' => $data['nik'],
                    'nama_lengkap' => $data['name'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'no_hp' => $data['no_hp'],
                    'alamat' => $data['alamat'] ?? $kk->alamat,
                    'status' => 'pending',
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('user_profiles', 'siap_ronda')) {
                    $profilePayload['siap_ronda'] = $data['jenis_kelamin'] === 'L';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('user_profiles', 'catatan_verifikasi')) {
                    $profilePayload['catatan_verifikasi'] = 'Pendaftaran warga baru mandiri';
                }

                $profile = UserProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    $profilePayload
                );
            }

            // Catat audit log
            AuditLog::record(
                action: 'pendaftaran_warga',
                entityType: 'user',
                entityId: $user->id,
                deskripsi: "Pendaftaran akun warga mandiri oleh {$user->name} (NIK: {$data['nik']})",
                rtId: (int) $data['rt_id'],
                userId: $user->id
            );

            // Notify Ketua RT
            $rtAdmins = User::whereHas('role', fn ($r) => $r->where('nama_role', 'rt'))
                ->whereHas('profile', fn ($p) => $p->where('rt_id', (int) $data['rt_id']))
                ->get();

            foreach ($rtAdmins as $rtAdmin) {
                Notifikasi::create([
                    'user_id' => $rtAdmin->id,
                    'from_user_id' => $user->id,
                    'tipe' => 'warga_baru',
                    'judul' => 'Pendaftaran Warga Baru',
                    'pesan' => "Warga baru {$data['name']} (NIK: {$data['nik']}, KK: {$data['no_kk']}) telah mendaftar dan menunggu verifikasi Anda.",
                    'dibaca' => false,
                ]);
            }

            return $user;
        });
    }

    public function showRegistrationForm()
    {
        return redirect('/login#register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new \Illuminate\Auth\Events\Registered($user = $this->create($request->all())));

        return redirect('/login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi dari Ketua RT.');
    }
}
