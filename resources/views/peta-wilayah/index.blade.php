@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<div class="container-fluid px-3 px-lg-4 py-3 map-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div><h2 class="mb-1"><i class="fas fa-map-location-dot text-warning me-2"></i>Peta Wilayah Kota Bekasi</h2><p class="text-muted mb-0">Lokasi CCTV, kegiatan, Posyandu, dan UMKM di Kota Bekasi.</p></div>
        <div id="mapStatus" class="small text-muted">Memuat lokasi…</div>
    </div>
    <div class="map-filters" aria-label="Filter kategori">
        @foreach(['cctv' => ['CCTV', '#2563eb'], 'kegiatan' => ['Kegiatan', '#9333ea'], 'posyandu' => ['Posyandu', '#db2777'], 'umkm' => ['UMKM', '#ea580c']] as $key => [$label, $color])
            <label><input class="category-filter" type="checkbox" value="{{ $key }}" checked><span style="--marker-color:{{ $color }}"></span>{{ $label }}</label>
        @endforeach
    </div>
    <div class="map-shell"><div id="wilayahMap" aria-label="Peta interaktif wilayah"></div></div>
</div>

<style>
.map-page .map-filters{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:10px}.map-page .map-filters label{display:flex;align-items:center;gap:7px;padding:7px 11px;border:1px solid var(--border-color,rgba(148,163,184,.3));border-radius:10px;background:var(--surface,#10271e);cursor:pointer}.map-page .map-filters span{width:11px;height:11px;border-radius:50%;background:var(--marker-color)}.map-shell{overflow:hidden;border:1px solid var(--border-color,rgba(148,163,184,.3));border-radius:15px;box-shadow:0 12px 30px rgba(0,0,0,.15)}#wilayahMap{width:100%;height:min(72vh,760px);min-height:480px;background:#dbeafe}.leaflet-container img.leaflet-tile{max-width:none!important;max-height:none!important}.map-marker{display:grid;place-items:center;width:42px;height:42px;border:4px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.55)}.map-marker i{transform:rotate(45deg);font-size:17px}.map-marker-label{padding:5px 8px!important;border:0!important;border-radius:7px!important;background:#111827!important;color:#fff!important;font-weight:600;box-shadow:0 3px 10px rgba(0,0,0,.3)}.map-marker-label:before{border-top-color:#111827!important}.map-legend{padding:9px 11px;background:rgba(255,255,255,.94);color:#1f2937;border-radius:8px;box-shadow:0 1px 6px rgba(0,0,0,.25);line-height:1.8}.map-legend strong{display:block}.map-legend div{display:flex;align-items:center;gap:7px}.map-legend i{width:11px;height:11px;border-radius:3px}.leaflet-popup-content{line-height:1.45}.leaflet-popup-content strong{font-size:14px}@media(max-width:576px){#wilayahMap{height:65vh;min-height:420px}.map-page .map-filters label{flex:1 1 45%}}
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const definitions = {
        cctv:{label:'CCTV',color:'#2563eb',icon:'fa-camera'}, kegiatan:{label:'Kegiatan',color:'#9333ea',icon:'fa-calendar-days'},
        posyandu:{label:'Posyandu',color:'#db2777',icon:'fa-notes-medical'}, umkm:{label:'UMKM',color:'#ea580c',icon:'fa-store'}
    };
    const map = L.map('wilayahMap').setView([-6.2383000, 106.9756000], 14);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(map);
    setTimeout(() => map.invalidateSize(), 200);
    const layers = Object.fromEntries(Object.keys(definitions).map(key => [key, L.layerGroup().addTo(map)]));
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const status = document.getElementById('mapStatus');
    try {
        const response = await fetch(@json(route('peta-wilayah.data')), {headers:{'Accept':'application/json'}});
        if (!response.ok) throw new Error('Data peta gagal dimuat.');
        const {markers} = await response.json();
        const bounds = [];
        markers.forEach(item => {
            const type = definitions[item.category]; if (!type) return;
            const icon = L.divIcon({className:'',html:`<div class="map-marker" style="background:${type.color}"><i class="fas ${type.icon}"></i></div>`,iconSize:[42,42],iconAnchor:[21,42],popupAnchor:[0,-42]});
            const marker = L.marker([item.latitude,item.longitude],{icon})
                .bindTooltip(`${type.label}: ${escapeHtml(item.name)}`,{className:'map-marker-label',direction:'top',offset:[0,-38]})
                .bindPopup(`<strong>${escapeHtml(item.name)}</strong><br><span>${type.label}</span>${item.is_sample ? '<br><em>Lokasi contoh</em>' : ''}<br><small>${escapeHtml(item.info)}</small>`);
            marker.on('mouseover',function(){this.openPopup()}); marker.addTo(layers[item.category]); bounds.push([item.latitude,item.longitude]);
        });
        if (bounds.length) map.fitBounds(bounds,{padding:[45,45],maxZoom:17});
        const sampleCount = markers.filter(item => item.is_sample).length;
        status.textContent = sampleCount ? `${sampleCount} lokasi contoh Kota Bekasi` : `${bounds.length} lokasi ditampilkan`;
    } catch (error) { status.textContent = error.message; status.classList.add('text-danger'); }
    document.querySelectorAll('.category-filter').forEach(input => input.addEventListener('change', () => input.checked ? layers[input.value].addTo(map) : map.removeLayer(layers[input.value])));
    const legend = L.control({position:'bottomright'}); legend.onAdd=()=>{const div=L.DomUtil.create('div','map-legend');div.innerHTML='<strong>Legenda</strong>'+Object.values(definitions).map(item=>`<div><i style="background:${item.color}"></i>${item.label}</div>`).join('');return div};legend.addTo(map);
});
</script>
@endpush
