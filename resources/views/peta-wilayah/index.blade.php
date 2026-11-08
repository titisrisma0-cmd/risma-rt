@extends('layouts.app')

@section('title', 'Peta Wilayah Lingkungan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">

<div class="content-container">
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-saas mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Peta Wilayah</li>
                </ol>
            </nav>
            <h1 class="page-title"><i class="fas fa-map-location-dot text-success me-2"></i>Peta Wilayah Lingkungan</h1>
            <p class="page-subtitle">Peta interaktif persebaran kamera CCTV, pos keamanan, posyandu, sentra UMKM, dan kegiatan warga.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="mapStatus" class="badge badge-soft-secondary py-2 px-3">
                <i class="fas fa-spinner fa-spin me-1"></i> Memuat lokasi...
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-saas btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- FILTER PILLS --}}
    <div class="saas-card p-3 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <span class="small fw-bold text-dark"><i class="fas fa-filter text-success me-1"></i>Tampilkan Kategori di Peta:</span>
            <div class="map-filters d-flex flex-wrap gap-2">
                @foreach(['cctv' => ['Kamera CCTV', '#147A45', 'fa-camera'], 'kegiatan' => ['Agenda Kegiatan', '#3985E8', 'fa-calendar-days'], 'posyandu' => ['Posyandu RT', '#db2777', 'fa-notes-medical'], 'umkm' => ['Lapak UMKM', '#ea580c', 'fa-store']] as $key => [$label, $color, $icon])
                    <label class="category-pill-label">
                        <input class="category-filter form-check-input me-1" type="checkbox" value="{{ $key }}" checked>
                        <span class="color-dot me-1" style="background-color: {{ $color }};"></span>
                        <i class="fas {{ $icon }} me-1" style="color: {{ $color }};"></i> {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- MAP CONTAINER --}}
    <div class="saas-card overflow-hidden p-0 mb-4 border shadow-sm">
        <div id="wilayahMap" aria-label="Peta interaktif wilayah" style="width: 100%; height: min(72vh, 760px); min-height: 480px;"></div>
    </div>
</div>

<style>
.category-pill-label {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border: 1px solid var(--border-color);
    border-radius: 20px;
    background: #FFFFFF;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--main-text);
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.category-pill-label:hover {
    background: var(--soft-green);
    border-color: var(--primary-green);
}
.color-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.map-marker {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    border: 3px solid #fff;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}
.map-marker i {
    transform: rotate(45deg);
    font-size: 16px;
}
.map-marker-label {
    padding: 5px 10px !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: #17221C !important;
    color: #fff !important;
    font-weight: 600;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}
.map-marker-label:before {
    border-top-color: #17221C !important;
}
.map-legend {
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.96);
    color: #17221C;
    border-radius: 10px;
    border: 1px solid #E9EEEA;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    font-size: 0.82rem;
    line-height: 1.8;
}
.map-legend strong {
    display: block;
    margin-bottom: 4px;
    font-size: 0.85rem;
}
.map-legend div {
    display: flex;
    align-items: center;
    gap: 8px;
}
.map-legend i {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    display: inline-block;
}
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const definitions = {
        cctv: {label: 'Kamera CCTV', color: '#147A45', icon: 'fa-camera'},
        kegiatan: {label: 'Kegiatan Warga', color: '#3985E8', icon: 'fa-calendar-days'},
        posyandu: {label: 'Posyandu RT', color: '#db2777', icon: 'fa-notes-medical'},
        umkm: {label: 'UMKM Warga', color: '#ea580c', icon: 'fa-store'}
    };
    const map = L.map('wilayahMap').setView([-6.2383000, 106.9756000], 14);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    setTimeout(() => map.invalidateSize(), 200);

    const layers = Object.fromEntries(Object.keys(definitions).map(key => [key, L.layerGroup().addTo(map)]));
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const status = document.getElementById('mapStatus');

    try {
        const response = await fetch(@json(route('peta-wilayah.data')), {headers: {'Accept': 'application/json'}});
        if (!response.ok) throw new Error('Data peta gagal dimuat.');
        const {markers} = await response.json();
        const bounds = [];
        markers.forEach(item => {
            const type = definitions[item.category];
            if (!type) return;
            const icon = L.divIcon({
                className: '',
                html: `<div class="map-marker" style="background:${type.color}"><i class="fas ${type.icon}"></i></div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });
            const marker = L.marker([item.latitude, item.longitude], {icon})
                .bindTooltip(`${type.label}: ${escapeHtml(item.name)}`, {className: 'map-marker-label', direction: 'top', offset: [0, -36]})
                .bindPopup(`<strong style="font-size: 14px;">${escapeHtml(item.name)}</strong><br><span class="badge" style="background:${type.color}; color:#fff; font-size:11px;">${type.label}</span>${item.is_sample ? '<br><em class="small text-muted">Lokasi percontohan</em>' : ''}<br><small class="text-secondary mt-1 d-block">${escapeHtml(item.info)}</small>`);
            marker.on('mouseover', function () { this.openPopup(); });
            marker.addTo(layers[item.category]);
            bounds.push([item.latitude, item.longitude]);
        });
        if (bounds.length) map.fitBounds(bounds, {padding: [45, 45], maxZoom: 17});
        const sampleCount = markers.filter(item => item.is_sample).length;
        status.innerHTML = sampleCount 
            ? `<i class="fas fa-check text-success me-1"></i> ${sampleCount} lokasi terpetakan` 
            : `<i class="fas fa-check text-success me-1"></i> ${bounds.length} lokasi ditampilkan`;
    } catch (error) {
        status.textContent = error.message;
        status.classList.add('text-danger');
    }

    document.querySelectorAll('.category-filter').forEach(input => {
        input.addEventListener('change', () => {
            if (input.checked) {
                layers[input.value].addTo(map);
            } else {
                map.removeLayer(layers[input.value]);
            }
        });
    });

    const legend = L.control({position: 'bottomright'});
    legend.onAdd = () => {
        const div = L.DomUtil.create('div', 'map-legend');
        div.innerHTML = '<strong>Legenda Titik Peta</strong>' + Object.values(definitions).map(item => `<div><i style="background:${item.color}"></i>${item.label}</div>`).join('');
        return div;
    };
    legend.addTo(map);
});
</script>
@endpush
