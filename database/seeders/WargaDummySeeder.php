<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class WargaDummySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        
        $rtList = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
        $rwList = ['01', '02', '03'];
        $jenisKelamin = ['L', 'P'];
        $agamaList = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
        $pekerjaanList = ['Petani', 'Buruh', 'PNS', 'Swasta', 'Wiraswasta', 'Nelayan', 'Pedagang', 'Guru', 'Dokter', 'Ibu Rumah Tangga', 'Mahasiswa', 'Pensiunan'];
        $statusPerkawinanList = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
        
        // Cek sudah berapa warga
        $existingWarga = User::whereHas('role', function($q) {
            $q->where('name', 'warga');
        })->count();
        
        $this->command->info("Saat ini ada {$existingWarga} warga.");
        
        if ($existingWarga >= 100) {
            $this->command->info("Sudah ada 100 warga atau lebih, tidak perlu menambah.");
            return;
        }
        
        $needToAdd = 100 - $existingWarga;
        $this->command->info("Menambahkan {$needToAdd} data warga...");
        
        $bar = $this->command->getOutput()->createProgressBar($needToAdd);
        
        for ($i = 1; $i <= $needToAdd; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $name = $firstName . ' ' . $lastName;
            $email = strtolower($firstName) . '.' . strtolower($lastName) . $i . '@gmail.com';
            
            // Generate NIK 16 digit
            $nik = '32' . $faker->numberBetween(01, 99) . 
                   str_pad($faker->numberBetween(01, 12), 2, '0', STR_PAD_LEFT) . 
                   str_pad($faker->numberBetween(01, 31), 2, '0', STR_PAD_LEFT) . 
                   $faker->numberBetween(0001, 9999);
            
            $rt = $rtList[array_rand($rtList)];
            $rw = $rwList[array_rand($rwList)];
            $gender = $jenisKelamin[array_rand($jenisKelamin)];
            $phone = '08' . $faker->numberBetween(100000000, 999999999);
            $address = $faker->streetAddress . ', RT ' . $rt . '/RW ' . $rw . ', ' . $faker->city;
            
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role_id' => 3, // role warga
                'nik' => $nik,
                'phone' => $phone,
                'address' => $address,
                'rt' => $rt,
                'rw' => $rw,
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '2005-01-01'),
                'jenis_kelamin' => $gender,
                'pekerjaan' => $pekerjaanList[array_rand($pekerjaanList)],
                'agama' => $agamaList[array_rand($agamaList)],
                'status_perkawinan' => $statusPerkawinanList[array_rand($statusPerkawinanList)],
                'bio' => 'Warga RT ' . $rt . '/RW ' . $rw . ' yang aktif.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine();
        
        $totalWarga = User::whereHas('role', function($q) {
            $q->where('name', 'warga');
        })->count();
        
        $this->command->info("✅ Berhasil! Total warga sekarang: {$totalWarga}");
        $this->command->info("Email contoh: nama.depan.nama.belakang1@gmail.com");
        $this->command->info("Password default: password");
    }
}