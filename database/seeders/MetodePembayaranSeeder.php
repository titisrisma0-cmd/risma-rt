<?php

namespace Database\Seeders;

use App\Models\MetodePembayaran;
use Illuminate\Database\Seeder;

class MetodePembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'kode' => 'qris',
                'nama' => 'QRIS RW SMART',
                'kategori' => 'qris',
                'nama_penerima' => 'RW 05 SMART',
                'nomor_rekening' => 'ID1020030040506',
                'instruksi' => 'Scan kode QRIS menggunakan m-Banking atau aplikasi e-wallet apa saja (GoPay, OVO, DANA, BCA Mobile, dll).',
                'is_active' => true,
            ],
            [
                'kode' => 'dana',
                'nama' => 'DANA',
                'kategori' => 'ewallet',
                'nama_penerima' => 'Bendahara RW SMART',
                'nomor_rekening' => '081234567890',
                'instruksi' => 'Transfer ke nomor akun DANA di atas dan simpan bukti transaksi untuk diunggah.',
                'is_active' => true,
            ],
            [
                'kode' => 'gopay',
                'nama' => 'GoPay',
                'kategori' => 'ewallet',
                'nama_penerima' => 'Bendahara RW SMART',
                'nomor_rekening' => '081234567890',
                'instruksi' => 'Transfer melalui aplikasi Gojek/GoPay ke nomor di atas.',
                'is_active' => true,
            ],
            [
                'kode' => 'ovo',
                'nama' => 'OVO',
                'kategori' => 'ewallet',
                'nama_penerima' => 'Bendahara RW SMART',
                'nomor_rekening' => '081234567890',
                'instruksi' => 'Transfer ke nomor akun OVO di atas.',
                'is_active' => true,
            ],
            [
                'kode' => 'shopeepay',
                'nama' => 'ShopeePay',
                'kategori' => 'ewallet',
                'nama_penerima' => 'Bendahara RW SMART',
                'nomor_rekening' => '081234567890',
                'instruksi' => 'Transfer ke nomor akun ShopeePay di atas.',
                'is_active' => true,
            ],
            [
                'kode' => 'bca',
                'nama' => 'Bank Central Asia (BCA)',
                'kategori' => 'bank',
                'nama_penerima' => 'KAS PENGURUS RW SMART',
                'nomor_rekening' => '8830129841',
                'instruksi' => 'Transfer ATM / Mobile Banking ke rekening BCA Pengurus RW.',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $m) {
            MetodePembayaran::updateOrCreate(
                ['kode' => $m['kode']],
                $m
            );
        }
    }
}
