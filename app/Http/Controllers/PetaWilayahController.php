<?php

namespace App\Http\Controllers;

use App\Models\CctvCamera;
use App\Models\JadwalPosyandu;
use App\Models\Kegiatan;
use App\Models\Umkm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaWilayahController extends Controller
{
    public function index(): View
    {
        return view('peta-wilayah.index');
    }

    public function data(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('role', 'profile');
        $rtId = $user->managedRtId();
        abort_if(! $user->isAdminRw() && ! $rtId, 403, 'Akun belum terhubung ke wilayah RT.');

        $markers = collect();
        $located = fn (Builder $query) => $query->whereNotNull('latitude')->whereNotNull('longitude');
        $forRt = fn (Builder $query) => $user->isAdminRw() ? $query : $query->where('rt_id', $rtId);

        $cctv = CctvCamera::withoutGlobalScopes()->newQuery();
        $located($cctv); $forRt($cctv);
        $markers->push(...$cctv->get()->map(fn ($item) => $this->marker('cctv', $item, $item->nama_lokasi, 'Status: '.ucfirst($item->status))));

        $kegiatan = Kegiatan::query();
        $located($kegiatan); $forRt($kegiatan);
        $markers->push(...$kegiatan->get()->map(fn ($item) => $this->marker('kegiatan', $item, $item->judul, $item->tanggal_mulai->format('d/m/Y H:i').' · '.$item->lokasi)));

        $posyandu = JadwalPosyandu::query();
        $located($posyandu); $forRt($posyandu);
        $markers->push(...$posyandu->get()->map(fn ($item) => $this->marker('posyandu', $item, $item->judul, $item->tanggal_mulai->format('d/m/Y H:i').' · '.$item->lokasi)));

        $umkm = Umkm::with('pemilik.profile')->where('status', 'aktif');
        $located($umkm);
        if (! $user->isAdminRw()) {
            $umkm->whereHas('pemilik.profile', fn (Builder $query) => $query->where('rt_id', $rtId));
        }
        $markers->push(...$umkm->get()->map(fn ($item) => $this->marker('umkm', $item, $item->nama_usaha, 'Produk/Jasa: '.$item->kategori)));

        $defaults = [
            'cctv' => ['CCTV Gerbang RW', 'Titik contoh CCTV di Kota Bekasi', -6.2329000, 106.9896000],
            'kegiatan' => ['Balai Kegiatan Warga', 'Titik contoh kegiatan warga di Kota Bekasi', -6.2395000, 106.9953000],
            'posyandu' => ['Posyandu Melati', 'Titik contoh layanan Posyandu di Kota Bekasi', -6.2460000, 106.9901000],
            'umkm' => ['Sentra UMKM Warga', 'Titik contoh UMKM warga di Kota Bekasi', -6.2418000, 106.9817000],
        ];
        foreach ($defaults as $category => [$name, $info, $latitude, $longitude]) {
            if (! $markers->contains('category', $category)) {
                $markers->push($this->defaultMarker($category, $name, $info, $latitude, $longitude));
            }
        }

        return response()->json(['markers' => $markers->values()]);
    }

    private function marker(string $category, object $item, string $name, string $info): array
    {
        return [
            'id' => $category.'-'.$item->id,
            'category' => $category,
            'name' => $name,
            'info' => $info,
            'latitude' => (float) $item->latitude,
            'longitude' => (float) $item->longitude,
        ];
    }

    private function defaultMarker(string $category, string $name, string $info, float $latitude, float $longitude): array
    {
        return [
            'id' => 'contoh-'.$category,
            'category' => $category,
            'name' => $name,
            'info' => $info,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_sample' => true,
        ];
    }
}
