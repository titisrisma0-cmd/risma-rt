<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    protected $fillable = [
        'kode_iuran',
        'nama_iuran',
        'jenis_iuran',
        'nominal',
        'periode',
        'keterangan',
        'kategori',
        'status'
    ];

    /**
     * Normalize and deduplicate a collection of iuran records.
     *
     * This keeps a single representative iuran for each unique combination
     * shown to users and keeps the duplicate ids for payment lookups.
     */
    public static function dedupeCollection($iuranCollection)
    {
        return $iuranCollection
            ->groupBy(function ($item) {
                return implode('|', [
                    strtolower(trim($item->nama_iuran)),
                    $item->jenis_iuran,
                    $item->nominal,
                    $item->periode,
                    $item->kategori,
                ]);
            })
            ->map(function ($group) {
                $representative = $group->sortByDesc(function ($item) {
                    return ($item->status === 'aktif' ? 1 : 0) + (!empty($item->nama_iuran) ? 1 : 0);
                })->first();

                $representative->duplicate_ids = $group->pluck('id')->values();

                return $representative;
            })
            ->values();
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
