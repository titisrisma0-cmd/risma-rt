<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class GenerateWargaDummy extends Command
{
    protected $signature = 'warga:generate {count=100}';
    protected $description = 'Generate dummy data warga';

    public function handle()
    {
        $count = $this->argument('count');
        $faker = Faker::create('id_ID');
        
        $rtList = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
        $rwList = ['01', '02', '03'];
        $jenisKelamin = ['L', 'P'];
        $agamaList = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
        $pekerjaanList = ['Petani', 'Buruh', 'PNS', 'Swasta', 'Wiraswasta', 'Nelayan', 'Pedagang', 'Guru', 'Dokter', 'Ibu Rumah Tangga', 'Mahasiswa', 'Pensiunan'];
        
        $existingWarga = User::whereHas('role', fn($q) => $q->where('name', 'warga'))->count();
        
        $this->info("Saat ini ada {$existingWarga} warga.");
        $this->info("Menambahkan {$count} data warga...");
        
        $bar = $this->output->createProgressBar($count);
        
        for ($i = 1; $i <= $count; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $name = $firstName . ' ' . $lastName;
            $email = strtolower($firstName) . '.' . strtolower($lastName) . $i . '@gmail.com';
            
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
                'role_id' => 3,
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
                'status_perkawinan' => 'Kawin',
                'bio' => 'Warga RT ' . $rt . '/RW ' . $rw,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $totalWarga = User::whereHas('role', fn($q) => $q->where('name', 'warga'))->count();
        $this->info("✅ Berhasil! Total warga sekarang: {$totalWarga}");
        $this->info("Email format: nama.depan.nama.belakang{angka}@gmail.com");
        $this->info("Password default: password");
    }
}