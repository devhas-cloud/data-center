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
        border-radius: 1rem; /* Rounded corners lebih halus */
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05); /* Shadow lembut */
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
    
    .bg-icon-primary { background-color: #eaecfb; color: #4e73df; }
    .bg-icon-info { background-color: #e1f6f8; color: #36b9cc; }
    .bg-icon-success { background-color: #e6fdf4; color: #1cc88a; }

    /* Hapus border lama yang kaku, gunakan style yang lebih clean */
    .border-left-custom { border-left: none !important; }

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
    .text-gray-800 { color: #5a5c69 !important; font-weight: 600; }
    .text-muted-custom { color: #858796 !important; font-size: 0.85rem; }
    
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
        border-left: 4px solid #4e73df; /* Aksen hover */
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="letter-spacing: 0.5px;">
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="letter-spacing: 0.5px;">
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

    <!-- Row 2: Device Slider & Category Percentage -->
    <div class="row mb-4">
        <!-- Column 1: Device Slider -->
        <div class="col-xl-8 col-lg-7">
            <div class="card card-modern shadow mb-4 h-100">
                <div class="card-body p-0">
                    <div id="deviceCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @php $slideIndex = 0; @endphp
                            @forelse($devicesByCategory as $category)
                                @if ($category->devices->count() > 0)
                                    <div class="carousel-item {{ $slideIndex === 0 ? 'active' : '' }}">
                                        <!-- Category Header -->
                                        <div class="card-header py-3 bg-white border-bottom-0">
                                            <h6 class="m-0 font-weight-bold text-primary">
                                                <i class="fas fa-layer-group mr-2 text-info"></i>
                                                {{ $category->category_name }}
                                            </h6>
                                        </div>
                                        <!-- Category Body with Devices -->
                                        <div class="card-body" style=" background-color: #fafbfc; height: 700px; overflow-y: auto; overflow-x: hidden;">
                                            <div class="row">
                                                @foreach ($category->devices as $device)
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card shadow-sm h-100 border-0 device-list-card" style="background: white;">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <div class="badge badge-light bg-light text-dark p-2 mr-2 rounded-circle">
                                                                        <i class="fas fa-microchip text-primary"></i>
                                                                    </div>
                                                                    <h6 class="font-weight-bold text-dark mb-0 text-truncate" style="max-width: 200px;">
                                                                        {{ $device->device_name }}
                                                                    </h6>
                                                                </div>
                                                                <p class="mb-1 small text-muted-custom">
                                                                    <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                                                    <strong>Location:</strong> {{ $device->location ?? 'N/A' }}
                                                                </p>
                                                                @if ($device->latitude && $device->longitude)
                                                                    <p class="mb-0 small text-muted-custom">
                                                                        <i class="fas fa-globe mr-1 text-success"></i>
                                                                        <span class="font-monospace">{{ $device->latitude }}, {{ $device->longitude }}</span>
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @php $slideIndex++; @endphp
                                @endif
                            @empty
                                <div class="carousel-item active">
                                    <div class="card-body">
                                        <div class="alert alert-warning bg-transparent border-0">No devices found.</div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        @if ($slideIndex > 1)
                            <!-- Carousel Controls -->
                            <div class="carousel-indicators" style="bottom: 0;">
                                @for ($i = 0; $i < $slideIndex; $i++)
                                    <button type="button" data-bs-target="#deviceCarousel"
                                        data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"
                                        aria-current="{{ $i === 0 ? 'true' : 'false' }}"></button>
                                @endfor
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#deviceCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#deviceCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>
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
                                <span class="small font-weight-bold text-gray-800">{{ $catPercent['category_name'] }}</span>
                                <span class="small badge badge-light bg-light text-dark px-2 py-1 rounded-pill">{{ $catPercent['percentage'] }}%</span>
                            </div>
                            <div class="progress progress-custom">
                                <div class="progress-bar progress-bar-custom bg-info" role="progressbar"
                                    style="width: {{ $catPercent['percentage'] }}%;"
                                    aria-valuenow="{{ $catPercent['percentage'] }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <div class="text-right mt-1">
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $catPercent['count'] }} devices</small>
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
                <div class="card card-modern shadow h-100">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <span class="badge bg-primary rounded-pill mr-2 p-2">
                                <i class="fas fa-server text-white"></i>
                            </span>
                            {{ $device->device_name }}
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive p-3">
                            <table class="table table-custom mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted-custom">Category</td>
                                        <td class="text-right font-weight-bold text-dark">{{ $device->device_category }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted-custom">Device ID</td>
                                        <td class="text-right text-dark">{{ $device->device_id ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted-custom">Parameters</td>
                                        <td class="text-right small">{{ $device->sensors->pluck('parameter_name')->implode(', ') ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted-custom">Location</td>
                                        <td class="text-right text-dark">{{ $device->location ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted-custom">District</td>
                                        <td class="text-right text-dark">{{ $device->district ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted-custom">IP Address</td>
                                        <td class="text-right text-dark"><span class="font-monospace small">{{ $device->device_ip ?? '-' }}</span></td>
                                    </tr>
                                    @if ($device->latitude && $device->longitude)
                                        <tr>
                                            <td class="text-muted-custom">Coordinates</td>
                                            <td class="text-right small text-primary">
                                                <a href="https://maps.google.com/?q={{ $device->latitude }},{{ $device->longitude }}" target="_blank" class="text-decoration-none">
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
                        <button type="button" class="btn btn-primary-custom btn-sm w-100 text-white rounded-pill shadow-sm view-latest-data"
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize carousel with Bootstrap 5
            const deviceCarouselElement = document.getElementById('deviceCarousel');

            if (deviceCarouselElement) {
                // Create carousel instance
                const carousel = new bootstrap.Carousel(deviceCarouselElement, {
                    interval: 5000, // Sedikit lebih lambat agar enak dilihat
                    ride: 'carousel',
                    pause: 'hover',
                    wrap: true,
                    touch: true
                });
            }

            // Handle View Latest Data button click
            const viewLatestDataButtons = document.querySelectorAll('.view-latest-data');
            const latestDataModal = new bootstrap.Modal(document.getElementById('latestDataModal'));

            viewLatestDataButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const deviceId = this.getAttribute('data-device-id');
                    // Perbaiki tampilan judul modal
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

                    fetch(`/admin/device-latest-data/${deviceId}`)
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
        });
    </script>
@endsection