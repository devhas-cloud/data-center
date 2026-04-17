@extends('layout.user')

@section('content')
    <!-- Peta maps Indonesia di sebelah kiri, keterangan card sebelah kanan -->
    <div class="row">
        <div class="col-lg-8">
            <div id="map" style="height: 80vh;  width: 100%;"></div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #DAD7D7">
                    <h5 class="mb-0" id="categoryTitle">
                        <i class="fas fa-microchip"></i> Loading...
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" id="prevCategory">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="nextCategory">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <div id="categoryCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
                        <div class="carousel-inner">
                            <!-- Carousel items will be populated dynamically via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color: #DAD7D7">
                    <h5 class="mb-0" id="categoryTitle">
                        <i class="fas fa-info-circle"></i> Legend
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0" id="legendList">
                        <!-- Legend items will be populated dynamically via JavaScript -->
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#DAD7D7">
                    <h5 class="mb-0" id="categoryTitle">
                        <i class="fas fa-info-circle"></i> Color information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-between align-items-left">
                            <div style="width: 40%; height: 15px; background-color:#198754;"></div>
                            <span class="ml-auto">Online</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-between align-items-left">
                            <div style="width: 40%; height: 15px; background-color:#0dcaf0;"></div>
                            <span class="ml-auto">Latest data > 3 Hours</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-between align-items-left">
                            <div style="width: 40%; height: 15px; background-color:#fd7e14;"></div>
                            <span class="ml-auto">Latest data > 24 Hours</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-between align-items-left">
                            <div style="width: 40%; height: 15px; background-color:#dc3545;"></div>
                            <span class="ml-auto">Latest data > 3 Days</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-between align-items-left">
                            <div style="width: 40%; height: 15px; background-color:#000000;"></div>
                            <span class="ml-auto">Latest data > 7 Days</span>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Load required libraries -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script type="text/javascript">
        // Tunggu hingga DOM sepenuhnya dimuat
        document.addEventListener('DOMContentLoaded', function() {
            let map;
            let allMarkers = {};
            let markers = [];

            // Pastikan Leaflet sudah dimuat
            if (typeof L !== 'undefined') {
                // Initialize the map
                map = L.map('map').setView([-2.5489, 118.0149], 5); // Centered on Indonesia

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Define custom icons based on device category and individual device data
                const createDeviceIcon = (categoryIcon, device) => {
                    let iconHtml = categoryIcon;
                    const currentTime = new Date();
                    let color = '#6c757d'; // Default color (gray) if no data

                    // Check if device has sensors and recorded_at data
                    if (device.sensors && device.sensors.length > 0) {
                        // Cari data terbaru dari semua sensor untuk device ini
                        let latestDataTime = null;

                        device.sensors.forEach(sensor => {
                            if (sensor.recorded_at) {
                                let sensorTime = new Date(sensor.recorded_at);
                                if (!latestDataTime || sensorTime > latestDataTime) {
                                    latestDataTime = sensorTime;
                                }
                            }
                        });

                        // Jika ada data terbaru, tentukan warna berdasarkan selisih waktu
                        if (latestDataTime) {
                            let deviceTimeDiff = (currentTime - latestDataTime) / (1000 * 60 * 60); // in hours

                            if (deviceTimeDiff > 168) { // > 7 days
                                color = '#000000'; // black
                            } else if (deviceTimeDiff > 72) { // > 3 days
                                color = '#dc3545'; // red
                            } else if (deviceTimeDiff > 24) { // > 24 hours
                                color = '#fd7e14'; // orange
                            } else if (deviceTimeDiff > 3) { // > 3 hours
                                color = '#0dcaf0'; // blue/cyan
                            } else {
                                color = '#198754'; // green
                            }
                        }
                    }

                    return L.divIcon({
                        html: `<div style="background-color: ${color}; width: 28px; height: 28px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 7px rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center;">
                                ${iconHtml}
                               </div>`,
                        iconSize: [28, 28],
                        className: 'custom-div-icon'
                    });
                };

                // Function to clear all markers from map
                function clearAllMarkers() {
                    Object.keys(allMarkers).forEach(cat => {
                        allMarkers[cat].forEach(marker => {
                            map.removeLayer(marker);
                        });
                    });
                    allMarkers = {};
                    markers = [];
                }

                // Function to update carousel content
                function updateCarousel(deviceData) {
                    const carouselInner = document.querySelector('#categoryCarousel .carousel-inner');
                    const currentIndex = $('#categoryCarousel .carousel-item.active').index();

                    carouselInner.innerHTML = '';

                    deviceData.forEach((data, index) => {
                        const isActive = index === currentIndex || (currentIndex === -1 && index === 0);
                        const carouselItem = document.createElement('div');
                        carouselItem.className = `carousel-item ${isActive ? 'active' : ''}`;
                        carouselItem.setAttribute('data-category', data.device_category);

                        let devicesHtml = '';
                        data.devices.forEach(device => {
                            const statusIcon = device.status === 'Online' ?
                                '<i class="fas fa-circle text-success"></i>' :
                                '<i class="fas fa-circle text-secondary"></i>';
                            const badgeClass = device.status === 'Online' ? 'badge-success' :
                                'badge-secondary';

                            devicesHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        ${statusIcon}
                                        <strong>${device.device_name}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i>
                                            ${parseFloat(device.latitude).toFixed(4)}, ${parseFloat(device.longitude).toFixed(4)}
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge ${badgeClass} d-block mb-1">
                                            ${device.status}
                                        </span>
                                        <button class="btn btn-sm btn-primary locate-btn"
                                            data-lat="${device.latitude}"
                                            data-lng="${device.longitude}">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                    </div>
                                </li>
                            `;
                        });

                        carouselItem.innerHTML = `
                            <div class="p-3">
                                <ul class="list-group list-group-flush">
                                    ${devicesHtml}
                                </ul>
                            </div>
                        `;
                        carouselInner.appendChild(carouselItem);
                    });

                    // Update category title
                    if (deviceData.length > 0) {
                        const activeItem = $('#categoryCarousel .carousel-item.active');
                        const categoryName = activeItem.data('category') || deviceData[0].device_category;
                        $('#categoryTitle').html('<i class="fas fa-microchip"></i> ' + categoryName);
                    }
                }

                //  Functionto update legend
                function updateLegend(deviceData) {
                    const legendList = document.getElementById('legendList');
                    legendList.innerHTML = '';

                    const categoriesAdded = new Set();

                    deviceData.forEach(categoryGroup => {
                        const category = categoryGroup.device_category;
                        let iconHtml = categoryGroup.category_icon || '';
                        const sum = categoryGroup.devices.length;
                        if (!categoriesAdded.has(category)) {


                            const legendItem = document.createElement('li');
                            legendItem.className = 'mb-2 d-flex align-items-center';
                            legendItem.innerHTML = `
                                <div style="width: 35px; height: 35px; display: flex; justify-content: center; align-items: center; margin-right: 10px;">
                                    ${iconHtml}
                                </div>
                                <span>  ${category} (${sum})</span>
                            `;
                            legendList.appendChild(legendItem);
                            categoriesAdded.add(category);
                        }
                    });
                }

                // Function to fetch and update device data
                function fetchDeviceData() {
                    fetch("{{ route('user.devices_data') }}")
                        .then(response => response.json())
                        .then(deviceData => {
                            // Clear existing markers
                            clearAllMarkers();

                            // Update carousel
                            updateCarousel(deviceData);

                            updateLegend(deviceData);
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

                                    let sensorsHtml = `<br>
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <th>Parameter</th><th>Recorded At</th><th>Latest Value</th>
                                            </thead>
                                            <tbody>
                                        `;
                                    if (item.device.sensors && item.device.sensors.length > 0) {
                                        item.device.sensors.forEach(sensor => {
                                            sensorsHtml +=
                                                `<tr>
                                                    <td>${sensor.parameter_label}</td>
                                                    <td>${sensor.recorded_at}</td>
                                                    <td>${sensor.latest_value}</td>
                                                </tr>`;
                                        });
                                    } else {
                                        sensorsHtml +=
                                            '<tr><td colspan="3"><em>No sensors available</em></td></tr>';
                                    }
                                    sensorsHtml += `</tbody></table>`;

                                    const marker = L.marker([item.lat, item.lng], {
                                        icon: icon
                                    });
                                    marker.bindPopup(
                                        `<div style="min-width: 200px;">
                                            <h6><strong>${item.device.device_id}</strong></h6>
                                            <strong>Category:</strong> ${item.category}<br>
                                            <strong>Status:</strong> <span class="${item.device.status === 'Online' ? 'text-success' : 'text-secondary'}">${item.device.status}</span><br>
                                            <strong>Coordinates:</strong> ${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}<br>
                                            ${sensorsHtml}
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

                                        let sensorsHtml = `<br>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                <th>Parameter</th><th>Recorded At</th><th>Latest Value</th>
                                                </thead>
                                                <tbody>
                                            `;
                                        if (item.device.sensors && item.device.sensors.length >
                                            0) {
                                            item.device.sensors.forEach(sensor => {
                                                sensorsHtml +=
                                                    `<tr>
                                                        <td>${sensor.parameter_name}</td>
                                                        <td>${sensor.recorded_at}</td>
                                                        <td>${sensor.latest_value}</td>
                                                    </tr>`;
                                            });
                                        } else {
                                            sensorsHtml +=
                                                '<tr><td colspan="3"><em>No sensors available</em></td></tr>';
                                        }
                                        sensorsHtml += `</tbody></table>`;

                                        const marker = L.marker([offsetLat, offsetLng], {
                                            icon: icon
                                        });

                                        const popupContent = `<div style="min-width: 200px;">
                                            <h6><strong>${item.device.device_id}</strong></h6>
                                            <strong>Category:</strong> ${item.category}<br>
                                            <strong>Status:</strong> <span class="${item.device.status === 'Online' ? 'text-success' : 'text-secondary'}">${item.device.status}</span><br>
                                            <strong>Coordinates:</strong> ${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}<br>
                                            ${devicesAtLocation.length > 1 ? `<span class="badge badge-warning"><i class="fas fa-layer-group"></i> ${devicesAtLocation.length} devices at this location</span><br>` : ''}
                                            ${sensorsHtml}
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
                fetchDeviceData();

                // Gantikan setInterval dengan scheduler yang sinkron dengan jam komputer
                function scheduleDeviceDataFetch() {
                    function scheduleNextFetch() {
                        // Hitung ms sampai awal menit berikutnya
                        const now = new Date();
                        const msUntilNextMinute = (60 - now.getSeconds()) * 1000 - now.getMilliseconds();

                        setTimeout(function() {
                            fetchDeviceData(); // Jalankan fetch data di awal menit berikutnya
                            scheduleNextFetch();
                        }, msUntilNextMinute);
                    }

                    scheduleNextFetch(); // Mulai scheduler tanpa double fetch saat initial load
                }

                // Panggil untuk mulai refresh tiap menit
                scheduleDeviceDataFetch();

            } else {
                console.error('Leaflet library not loaded');
                document.getElementById('map').innerHTML =
                    '<div class="alert alert-danger">Map failed to load. Please check your internet connection.</div>';
            }

            // Handle category carousel navigation
            $('#prevCategory').on('click', function() {
                $('#categoryCarousel').carousel('pause'); // Pause auto-slide when user navigates manually
                $('#categoryCarousel').carousel('prev');
                setTimeout(function() {
                    $('#categoryCarousel').carousel('cycle'); // Resume auto-slide after 10 seconds
                }, 10000);
            });

            $('#nextCategory').on('click', function() {
                $('#categoryCarousel').carousel('pause'); // Pause auto-slide when user navigates manually
                $('#categoryCarousel').carousel('next');
                setTimeout(function() {
                    $('#categoryCarousel').carousel('cycle'); // Resume auto-slide after 10 seconds
                }, 10000);
            });

            // Update category title when carousel slides
            $('#categoryCarousel').on('slid.bs.carousel', function() {
                const activeItem = $(this).find('.carousel-item.active');
                const categoryName = activeItem.data('category');
                if (categoryName) {
                    $('#categoryTitle').html('<i class="fas fa-microchip"></i> ' + categoryName);
                }
            });

            // Start carousel auto-slide
            $('#categoryCarousel').carousel({
                interval: 5000,
                ride: 'carousel'
            });

            // Add click handlers for locate buttons
            $(document).on('click', '.locate-btn', function() {
                const lat = parseFloat($(this).data('lat'));
                const lng = parseFloat($(this).data('lng'));

                if (map) {
                    // Count how many devices are at this location (rounded to 6 decimals)
                    const coordKey = `${lat.toFixed(6)},${lng.toFixed(6)}`;
                    let devicesAtLocation = 0;

                    // Count devices with same coordinates
                    markers.forEach(markerObj => {
                        const markerLat = parseFloat(markerObj.device.latitude);
                        const markerLng = parseFloat(markerObj.device.longitude);
                        const markerKey = `${markerLat.toFixed(6)},${markerLng.toFixed(6)}`;

                        if (markerKey === coordKey) {
                            devicesAtLocation++;
                        }
                    });

                    // Adjust zoom level based on number of devices at location
                    let zoomLevel = 12; // Default zoom
                    if (devicesAtLocation >= 3) {
                        zoomLevel = 16; // Closer zoom for multiple devices
                    } else if (devicesAtLocation === 2) {
                        zoomLevel = 14; // Medium zoom for 2 devices
                    }

                    map.flyTo([lat, lng], zoomLevel, {
                        duration: 1.5
                    });
                }
            });
        });
    </script>
@endsection
