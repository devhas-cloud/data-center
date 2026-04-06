@extends('layout.admin')
@section('title', 'Dashboard')

<!-- Tambahkan Font Google Poppins agar terlihat lebih modern -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* --- Global Styles --- */
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
    }

    /* --- Card Styles --- */
    .card-modern {
        border: none;
        border-radius: 1rem;
        /* Rounded corners lebih halus */
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
        /* Shadow lembut */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        background-color: #fff;
        overflow: hidden;
    }

    .card-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.1);
    }

    /* --- Stat Card Specifics --- */
    .stat-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .bg-icon-primary {
        background-color: #eaecfb;
        color: #4e73df;
    }

    .bg-icon-info {
        background-color: #e1f6f8;
        color: #36b9cc;
    }

    .bg-icon-success {
        background-color: #e6fdf4;
        color: #1cc88a;
    }

    /* Hapus border lama yang kaku, gunakan style yang lebih clean */
    .border-left-custom {
        border-left: none !important;
    }

    /* --- Carousel Styles --- */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.4);
        border-radius: 50%;
        padding: 10px;
        background-size: 60%;
    }

    .carousel-indicators button {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin: 0 5px;
        opacity: 0.5;
        background-color: #4e73df;
    }

    .carousel-indicators button.active {
        opacity: 1;
        background-color: #224abe;
        width: 12px;
        height: 12px;
    }

    /* --- Progress Bar Styles --- */
    .progress-custom {
        height: 10px;
        border-radius: 20px;
        background-color: #eaecfb;
    }

    .progress-bar-custom {
        border-radius: 20px;
        transition: width 1s ease-in-out;
    }

    /* --- Table & Typography --- */
    .text-gray-800 {
        color: #5a5c69 !important;
        font-weight: 600;
    }

    .text-muted-custom {
        color: #858796 !important;
        font-size: 0.85rem;
    }

    .table-custom td {
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    /* Button Hover */
    .btn-primary-custom {
        background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        border: none;
        transition: all 0.3s;
    }

    .btn-primary-custom:hover {
        background: linear-gradient(180deg, #3e63d4 10%, #1a3a9e 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(78, 115, 223, 0.3);
    }

    /* Device List Card */
    .device-list-card:hover {
        border-left: 4px solid #4e73df;
        /* Aksen hover */
    }

    /* Device Status Styles */
    .device-card {
        border-left: 5px solid #6c757d;
        transition: all 0.3s ease-in-out;
    }

    .device-card.status-online {
        border-left-color: #198754;
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
    }

    .device-card.status-offline {
        border-left-color: #dc3545;
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
    }

    .status-badge {
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 0.25rem;
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .status-badge.online {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-badge.offline {
        background-color: #f8d7da;
        color: #842029;
    }

    .device-card-header-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
</style>

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mt-5 mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Dashboard</h1>
                <p class="text-muted small mb-0">Overview sistem monitoring</p>
            </div>
            <div class="d-none d-sm-block">
                <!-- Breadcrumb atau tanggal bisa ditaruh disini -->
                <span class="badge bg-light text-dark border py-2 px-3">
                    <i class="far fa-clock mr-1"></i> {{ now()->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Row 1: Card Total Sum All Categories -->
        <div class="row mb-4">
            <!-- Total All Devices -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-modern shadow-sm h-100 py-3">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"
                                    style="letter-spacing: 0.5px;">
                                    Total Device
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalAll ?? 0) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="stat-icon-wrapper bg-icon-primary">
                                    <i class="fas fa-database"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards per Category -->
            @forelse($categories as $category)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-modern shadow-sm h-100 py-3">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1"
                                        style="letter-spacing: 0.5px;">
                                        {{ $category->category_name }}
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($category->total_items ?? 0) }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="stat-icon-wrapper bg-icon-info">
                                        <i class="fas {{ $category->category_icon ?? 'fa-folder' }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info border-0 bg-info text-white rounded-3 shadow-sm">No categories found.</div>
                </div>
            @endforelse
        </div>

        <!-- Row 2: Device Map & Category Percentage -->
        <div class="row mb-4">
            <!-- Column 1: Device Map -->
            <div class="col-xl-8 col-lg-7">
                <div class="card card-modern shadow mb-4 h-100">
                    <div class="card-body p-0">
                        <div id="map" style="height: 100%; min-height: 700px; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Category Percentage -->
            <div class="col-xl-4 col-lg-5">
                <div class="card card-modern shadow mb-4 h-100">
                    <div class="card-header py-3 bg-white border-bottom-0">
                        <h6 class="m-0 font-weight-bold text-primary">Category</h6>
                    </div>
                    <div class="card-body">
                        @forelse($categoryPercentages as $catPercent)
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span
                                        class="small font-weight-bold text-gray-800">{{ $catPercent['category_name'] }}</span>
                                    <span
                                        class="small badge badge-light bg-light text-dark px-2 py-1 rounded-pill">{{ $catPercent['percentage'] }}%</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar progress-bar-custom bg-info" role="progressbar"
                                        style="width: {{ $catPercent['percentage'] }}%;"
                                        aria-valuenow="{{ $catPercent['percentage'] }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="text-right mt-1">
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $catPercent['count'] }}
                                        devices</small>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-light border-0">No category data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Device Cards -->
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3 font-weight-bold text-gray-800 border-bottom pb-2">
                    <i class="fas fa-list-alt mr-2"></i>Device List
                </h5>
            </div>
            @forelse($devices as $device)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-modern shadow h-100 device-card" data-device-id="{{ $device->device_id }}" data-device-status="unknown">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                            <div class="device-card-header-status">
                                <h6 class="m-0 font-weight-bold text-dark">
                                    <span class="badge bg-primary rounded-pill mr-2 p-2">
                                        <i class="fas fa-server text-white"></i>
                                    </span>
                                    {{ $device->device_name }}
                                </h6>
                                <span class="status-badge online device-status-badge" style="display:none;">Online</span>
                                <span class="status-badge offline device-status-badge" style="display:none;">Offline</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive p-3">
                                <table class="table table-custom mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted-custom">Category</td>
                                            <td class="text-right font-weight-bold text-dark">
                                                {{ $device->device_category }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted-custom">Device ID</td>
                                            <td class="text-right text-dark">{{ $device->device_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted-custom">Parameters</td>
                                            <td class="text-right small">
                                                {{ $device->sensors->pluck('parameter_name')->implode(', ') ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted-custom">Location</td>
                                            <td class="text-right text-dark">{{ $device->location ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted-custom">District</td>
                                            <td class="text-right text-dark">{{ $device->district ?? '-' }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <td class="text-muted-custom">IP Address</td>
                                            <td class="text-right text-dark"><span
                                                    class="font-monospace small">{{ $device->device_ip ?? '-' }}</span>
                                            </td>
                                        </tr> --}}
                                        @if ($device->latitude && $device->longitude)
                                            <tr>
                                                <td class="text-muted-custom">Coordinates</td>
                                                <td class="text-right small text-primary">
                                                    <a href="https://maps.google.com/?q={{ $device->latitude }},{{ $device->longitude }}"
                                                        target="_blank" class="text-decoration-none">
                                                        Lihat Peta <i class="fas fa-external-link-alt ml-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <button type="button"
                                class="btn btn-primary-custom btn-sm w-100 text-white rounded-pill shadow-sm view-latest-data"
                                data-device-id="{{ $device->device_id }}">
                                <i class="fas fa-chart-line mr-1"></i> View Latest Data
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border-0 shadow-sm text-center rounded-3">No devices found.</div>
                </div>
            @endforelse
        </div>

        <!-- Modal Latest Data -->
        <div class="modal fade" id="latestDataModal" tabindex="-1" aria-labelledby="latestDataModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 rounded-top">
                        <h5 class="modal-title" id="latestDataModalLabel">
                            <i class="fas fa-chart-area mr-2"></i> New Date
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="latestDataContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Get data...</p>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Load Leaflet libraries -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            let map;
            let allMarkers = {};
            let markers = [];

            // Ensure Leaflet is loaded
            if (typeof L !== 'undefined') {
                // Initialize the map
                map = L.map('map').setView([-2.5489, 118.0149], 5); // Centered on Indonesia

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Create device icon utility
                const createDeviceIcon = (categoryIcon, status) => {
                    let color = '#6c757d'; // Default gray

                    if (status === 'Online') {
                        color = '#198754'; // green
                    } else {
                        color = '#dc3545'; // red
                    }

                    return L.divIcon({
                        html: `<div style="background-color: ${color}; width: 28px; height: 28px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 7px rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; color: white; font-size: 14px; font-weight: bold;">
                                ${categoryIcon}
                               </div>`,
                        iconSize: [28, 28],
                        className: 'custom-div-icon'
                    });
                };

                // Clear all markers
                function clearAllMarkers() {
                    Object.keys(allMarkers).forEach(cat => {
                        allMarkers[cat].forEach(marker => {
                            map.removeLayer(marker);
                        });
                    });
                    allMarkers = {};
                    markers = [];
                }

                // Update device cards status dengan data real-time
                function updateDeviceCardsStatus(deviceData) {
                    // Build status map from fetched data
                    const statusMap = {};
                    deviceData.forEach((categoryGroup) => {
                        if (categoryGroup.devices && Array.isArray(categoryGroup.devices)) {
                            categoryGroup.devices.forEach((device) => {
                                statusMap[device.device_id] = device.status;
                            });
                        }
                    });

                    // Update each device card's visual status
                    document.querySelectorAll('.device-card').forEach(card => {
                        const deviceId = card.getAttribute('data-device-id');
                        const status = statusMap[deviceId] || 'Offline';
                        
                        // Update data attribute
                        card.setAttribute('data-device-status', status);
                        
                        // Update visual class
                        card.classList.remove('status-online', 'status-offline');
                        if (status === 'Online') {
                            card.classList.add('status-online');
                        } else {
                            card.classList.add('status-offline');
                        }

                        // Update status badges
                        const onlineBadge = card.querySelector('.status-badge.online');
                        const offlineBadge = card.querySelector('.status-badge.offline');
                        
                        if (status === 'Online') {
                            onlineBadge.style.display = 'inline-block';
                            offlineBadge.style.display = 'none';
                        } else {
                            onlineBadge.style.display = 'none';
                            offlineBadge.style.display = 'inline-block';
                        }
                    });
                }

                // Fetch and render admin device data
                function fetchAdminDevices() {
                    fetch("{{ route('admin.devices_data') }}")
                        .then(response => response.json())
                        .then(deviceData => {
                            clearAllMarkers();

                            // Update device cards status
                            updateDeviceCardsStatus(deviceData);

                            // Group devices by coordinates to handle same location
                            const coordsMap = {};

                            deviceData.forEach((categoryGroup, catIndex) => {
                                const category = categoryGroup.device_category;

                                if (!allMarkers[category]) {
                                    allMarkers[category] = [];
                                }

                                // Check if devices exist and is an array
                                if (categoryGroup.devices && Array.isArray(categoryGroup.devices)) {
                                    categoryGroup.devices.forEach((device, devIndex) => {
                                        const lat = parseFloat(device.latitude);
                                        const lng = parseFloat(device.longitude);

                                        // Skip if coordinates are invalid
                                        if (isNaN(lat) || isNaN(lng)) {
                                            console.warn(
                                                `Invalid coordinates for device: ${device.device_id}`
                                            );
                                            return;
                                        }

                                        // Create unique key for coordinates (rounded to 6 decimals)
                                        const coordKey = `${lat.toFixed(6)},${lng.toFixed(6)}`;

                                        if (!coordsMap[coordKey]) {
                                            coordsMap[coordKey] = [];
                                        }

                                        coordsMap[coordKey].push({
                                            device,
                                            category,
                                            categoryIcon: categoryGroup.category_icon,
                                            lat,
                                            lng
                                        });
                                    });
                                }
                            });

                            // Create markers with offset for same coordinates
                            Object.keys(coordsMap).forEach(coordKey => {
                                const devicesAtLocation = coordsMap[coordKey];

                                if (devicesAtLocation.length === 1) {
                                    // Single device at this location - no offset needed
                                    const item = devicesAtLocation[0];
                                    const icon = createDeviceIcon(item.categoryIcon, item.device);
                                    const marker = L.marker([item.lat, item.lng], {
                                        icon: icon
                                    });
                                    marker.bindPopup(
                                        `<div style="min-width: 200px;">
                                            <h6><strong>${item.device.device_id}</strong></h6>
                                            <strong>Category:</strong> ${item.category}<br>
                                            <strong>Status:</strong> <span class="${item.device.status === 'Online' ? 'text-success' : 'text-secondary'}">${item.device.status}</span><br>
                                            <strong>Coordinates:</strong> ${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}<br>
                                           
                                        </div>`
                                    );

                                    markers.push({
                                        marker,
                                        device: item.device,
                                        category: item.category
                                    });
                                    allMarkers[item.category].push(marker);

                                } else {
                                    // Multiple devices at same location - create offset pattern
                                    const radius = 0.0002; // Small offset radius
                                    const angleStep = (2 * Math.PI) / devicesAtLocation.length;

                                    devicesAtLocation.forEach((item, index) => {
                                        // Calculate offset position in circular pattern
                                        const angle = angleStep * index;
                                        const offsetLat = item.lat + (radius * Math.cos(angle));
                                        const offsetLng = item.lng + (radius * Math.sin(angle));

                                        const icon = createDeviceIcon(item.categoryIcon, item
                                            .device);

                                        const marker = L.marker([offsetLat, offsetLng], {
                                            icon: icon
                                        });

                                        const popupContent = `<div style="min-width: 200px;">
                                            <h6><strong>${item.device.device_id}</strong></h6>
                                            <strong>Category:</strong> ${item.category}<br>
                                            <strong>Status:</strong> <span class="${item.device.status === 'Online' ? 'text-success' : 'text-secondary'}">${item.device.status}</span><br>
                                            <strong>Coordinates:</strong> ${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}<br>
                                            ${devicesAtLocation.length > 1 ? `<span class="badge badge-warning"><i class="fas fa-layer-group"></i> ${devicesAtLocation.length} devices at this location</span><br>` : ''}
                                         
                                        </div>`;

                                        marker.bindPopup(popupContent);

                                        markers.push({
                                            marker,
                                            device: item.device,
                                            category: item.category
                                        });
                                        allMarkers[item.category].push(marker);
                                    });
                                }
                            });

                            // Show all markers from all categories on map
                            const bounds = L.latLngBounds();
                            let hasValidMarkers = false;

                            Object.keys(allMarkers).forEach(cat => {
                                allMarkers[cat].forEach(marker => {
                                    marker.addTo(map);
                                    bounds.extend(marker.getLatLng());
                                    hasValidMarkers = true;
                                });
                            });

                            // Auto-fit map to show all markers with padding (only on first load)
                            if (hasValidMarkers && !window.mapInitialized) {
                                map.fitBounds(bounds, {
                                    padding: [50, 50],
                                    maxZoom: 15
                                });
                                window.mapInitialized = true;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching device data:', error);
                        });

                }

                // Initial fetch
                fetchAdminDevices();

                // Gantikan setInterval dengan scheduler yang sinkron dengan jam komputer
                function scheduleDeviceDataFetch() {
                    function scheduleNextFetch() {
                        // Hitung ms sampai awal menit berikutnya
                        const now = new Date();
                        const msUntilNextMinute = (60 - now.getSeconds()) * 1000 - now.getMilliseconds();

                        setTimeout(function() {
                           fetchAdminDevices(); // Jalankan fetch data di awal menit berikutnya
                            scheduleNextFetch();
                        }, msUntilNextMinute);
                    }

                    scheduleNextFetch(); // Mulai scheduler tanpa double fetch saat initial load
                }

                // Panggil untuk mulai refresh tiap menit
                scheduleDeviceDataFetch();


            }

            // Handle View Latest Data button click
            const viewLatestDataButtons = document.querySelectorAll('.view-latest-data');
            if (viewLatestDataButtons.length > 0) {
                const latestDataModal = new bootstrap.Modal(document.getElementById('latestDataModal'));

                viewLatestDataButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const deviceId = this.getAttribute('data-device-id');
                        document.getElementById('latestDataModalLabel').innerHTML = `
                            <i class="fas fa-chart-area mr-2"></i> Data Terbaru - <span class="font-weight-normal">${deviceId}</span>
                        `;

                        latestDataModal.show();

                        document.getElementById('latestDataContent').innerHTML = `
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Memuat data...</p>
                            </div>
                        `;

                        fetch(`{{ route('admin.device_latest_data', ':deviceId') }}`.replace(
                                ':deviceId', deviceId))
                            .then(response => {
                                if (!response.ok) throw new Error('Network error');
                                return response.json();
                            })
                            .then(data => {
                                displayLatestData(data.data);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                document.getElementById('latestDataContent').innerHTML = `
                                    <div class="alert alert-danger border-0 shadow-sm rounded-3 text-center">
                                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>
                                        Gagal memuat data. Silakan coba lagi.
                                    </div>
                                `;
                            });
                    });
                });

                function displayLatestData(data) {
                    if (!data || data.length === 0) {
                        document.getElementById('latestDataContent').innerHTML = `
                            <div class="alert alert-info border-0 shadow-sm rounded-3 text-center">
                                <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                Tidak ada data tersedia untuk device ini.
                            </div>
                        `;
                        return;
                    }

                    let html = `
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Parameter</th>
                                        <th class="border-0">Waktu</th>
                                        <th class="border-0 text-right">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.forEach(item => {
                        html += `
                            <tr>
                                <td class="font-weight-bold text-dark">${item.parameter_label || '-'}</td>
                                <td class="text-muted small">${item.recorded_at || '-'}</td>
                                <td class="text-right">
                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                        ${item.latest_value || '-'}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                        </tbody>
                    </table>
                </div>
            `;

                    document.getElementById('latestDataContent').innerHTML = html;
                }
            }
        });
    </script>
@endsection
