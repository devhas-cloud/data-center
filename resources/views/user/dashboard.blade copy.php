@extends('layout.user')

@section('desktop-selector')
    @include('user.componen.desktop-selector')
@endsection

@section('mobile-selector')
    @include('user.componen.mobile-selector')
@endsection

@section('content')
    <div class="row mb-4">
        <!-- Card Maps -->
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="card">
                <div class="card-body p-0">
                    <div id="map" style="height: 450px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Card Latest Data Parameter dengan Progress Bar -->
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-steps"></i> Latest Data
                    </h5>
                </div>
                <div class="card-body" id="latestDataProgressBars" style="max-height: 450px; overflow-y: auto;">
                    <!-- Progress bars will be dynamically generated here -->
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading parameter data...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Line Chart dengan Button Parameter -->
    <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up-arrow"></i> Data Chart
                        </h5>
                        {{-- <button class="btn btn-sm btn-secondary" id="resetZoomLineChart" title="Reset Zoom">
                            <i class="bi bi-zoom-out"></i> Reset
                        </button> --}}
                    </div>
                    {{-- <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Zoom: Scroll mouse wheel or drag area | Pan: Hold Ctrl + Drag
                    </small> --}}
                </div>
                <div class="card-body">
                    <div id="lineChartLoader" class="text-center text-muted py-5" style="display: none;">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading chart data...
                    </div>
                    <div style="min-height: 350px; position: relative;">
                        <canvas id="sensorLineChart"></canvas>
                    </div>

                    <!-- Parameter Selection Buttons -->
                    <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center" id="parameterButtons">
                        <!-- Buttons will be dynamically generated here -->
                    </div>

                    <!-- Tombol Tampilkan Batas Acuan -->
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-outline-secondary" id="showReferenceBtn" data-bs-toggle="modal"
                            data-bs-target="#referenceModal">
                            <i class="bi bi-sliders"></i> Tampilkan Batas Acuan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Bar Chart dengan Button Parameter -->
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-fill"></i> Hourly Average
                    </h5>
                </div>
                <div class="card-body">
                    <div id="barChartLoader" class="text-center text-muted py-5" style="display: none;">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading chart data...
                    </div>
                    <div style="min-height: 350px; position: relative;">
                        <canvas id="sensorBarChart"></canvas>
                    </div>

                    <!-- Parameter Selection Buttons for Bar Chart -->
                    <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center" id="barParameterButtons">
                        <!-- Buttons will be dynamically generated here -->
                    </div>

                    <!-- Tombol Tampilkan Batas Acuan Bar Chart -->
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-outline-secondary" id="showReferenceBarBtn" data-bs-toggle="modal"
                            data-bs-target="#referenceBarModal">
                            <i class="bi bi-sliders"></i> Tampilkan Batas Acuan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Wind Rose Chart - Only shown if Wind Direction and Wind Speed parameters exist -->
    <div class="row mb-4" id="windRoseRow" style="display: none;">
        <div class="col-lg-12 col-md-12 mb-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-compass"></i> Wind Rose - Wind Direction Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div id="windRoseLoader" class="text-center text-muted py-5" style="display: none;">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading wind rose data...
                    </div>
                    <div id="windRoseChart" style="width: 100%; min-height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Historical Data -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Historical Data
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date" class="form-control form-control-sm" id="historicalStartDate"
                                style="width: 160px;">
                            <span>-</span>
                            <input type="date" class="form-control form-control-sm" id="historicalEndDate"
                                style="width: 160px;">
                            <button class="btn btn-sm btn-primary" id="loadHistoricalData">
                                <i class="bi bi-search"></i> Load
                            </button>
                            <button class="btn btn-sm btn-success" id="exportHistoricalData">
                                <i class="bi bi-file-earmark-excel"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm" id="historicalDataTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 15%;">Parameter</th>
                                    <th style="width: 12%;">Value</th>
                                    <th style="width: 8%;">Unit</th>
                                    <th style="width: 18%;">Recorded At</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Batas Acuan Line Chart -->
    <div class="modal fade" id="referenceModal" tabindex="-1" aria-labelledby="referenceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="referenceModalLabel">
                        <i class="bi bi-sliders"></i> Batas Acuan Parameter - Line Chart
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="referenceModalBody">
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading reference data...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Batas Acuan Bar Chart -->
    <div class="modal fade" id="referenceBarModal" tabindex="-1" aria-labelledby="referenceBarModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="referenceBarModalLabel">
                        <i class="bi bi-sliders"></i> Batas Acuan Parameter - Bar Chart
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="referenceBarModalBody">
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading reference data...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Load required libraries -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />

    <style>
        /* Preserve chart height on small screens */
        #sensorLineChart,
        #sensorBarChart {
            min-height: 300px !important;
            height: auto !important;
        }

        /* Ensure chart containers maintain minimum height on mobile */
        @media (max-width: 768px) {

            #sensorLineChart,
            #sensorBarChart {
                min-height: 350px !important;
            }

            .card-body canvas {
                min-height: 350px !important;
            }
        }

        @media (max-width: 576px) {

            #sensorLineChart,
            #sensorBarChart {
                min-height: 400px !important;
            }

            .card-body canvas {
                min-height: 400px !important;
            }
        }
    </style>
@endsection


@section('script')
    <!-- JS Libraries -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {

            // ==================== DEVICE SELECTOR ====================
            let currentSelectedDevice = null;

            const deviceSelector = document.getElementById('deviceSelector');
            const deviceSelectorToggle = document.getElementById('deviceSelectorToggle');
            const deviceOptions = document.querySelectorAll('.device-option');
            const deviceNameElements = document.querySelectorAll('.device-name');

            // Get initial selected device
            const initialActiveOption = document.querySelector('.device-option.active');
            if (initialActiveOption) {
                currentSelectedDevice = initialActiveOption.getAttribute('data-value');
            }

            if (deviceSelectorToggle) {
                deviceSelectorToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    deviceSelector.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (deviceSelector && !deviceSelector.contains(event.target) && deviceSelector.classList
                        .contains('show')) {
                        deviceSelector.classList.remove('show');
                    }
                });
            }

            // Mobile Device Selector Toggle
            const mobileDeviceSelector = document.getElementById('mobileDeviceSelector');
            const mobileDeviceToggle = document.getElementById('mobileDeviceToggle');

            if (mobileDeviceToggle) {
                mobileDeviceToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileDeviceSelector.classList.toggle('show');
                });
            }

            // Handle device selection change (Desktop & Mobile)
            deviceOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const selectedDevice = this.getAttribute('data-value');

                    // Update active state
                    deviceOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');

                    // Update device name in toggle
                    deviceNameElements.forEach(nameEl => {
                        nameEl.textContent = selectedDevice;
                    });

                    // Close dropdowns
                    if (deviceSelector) {
                        deviceSelector.classList.remove('show');
                    }
                    if (mobileDeviceSelector) {
                        mobileDeviceSelector.classList.remove('show');
                    }

                    // Update current selected device
                    currentSelectedDevice = selectedDevice;
                    currentDeviceId = selectedDevice;

                    // Reset parameters cache
                    parameters = [];

                    // Load progress bar data first, then update charts
                    loadProgressBarData(selectedDevice)
                        .then(() => {
                            // Update map with fresh data
                            loadMapData(selectedDevice);

                            // Update chart parameters after data is loaded
                            if (parameters.length > 0) {
                                loadLineChartParameters();
                                loadBarChartParameters();

                                // Check and update wind rose
                                checkAndShowWindRose();
                            }
                        })
                        .catch(error => {
                            console.error('Error updating device data:', error);
                        });
                });
            });


            // ==================== END DEVICE SELECTOR ====================




            let map;
            let mapMarkers = [];
            let sensorLineChart = null;
            let sensorBarChart = null;

            // Global parameters
            let parameters = [];

            // Get device data from Laravel
            const deviceCategories = @json($deviceCategories);
            let currentDeviceId = null;
            let currentDevice = null;

            // Get initial device data
            if (deviceCategories.length > 0 && deviceCategories[0].devices.length > 0) {
                currentDevice = deviceCategories[0].devices[0];
                currentDeviceId = currentDevice.device_id;
            }

            // ==================== MAP INITIALIZATION ====================
            function initializeMap() {
                // Initialize the map with first device location or default Indonesia center
                let initialLat = currentDevice ? parseFloat(currentDevice.latitude) : -2.5489;
                let initialLng = currentDevice ? parseFloat(currentDevice.longitude) : 118.0149;
                let initialZoom = currentDevice ? 12 : 5;

                map = L.map('map').setView([initialLat, initialLng], initialZoom);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Load device marker for selected device
                loadDeviceMarker(currentDeviceId);
            }

            function loadMapData(deviceId) {
                if (!deviceId) return;
                loadDeviceMarker(deviceId);
            }

            function loadDeviceMarker(deviceId) {
                if (!deviceId) return;

                // Fetch device data with fresh status
                fetch(`/user/maps-dashboard/${deviceId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Clear existing markers
                        mapMarkers.forEach(marker => map.removeLayer(marker));
                        mapMarkers = [];

                        const lat = parseFloat(data.latitude);
                        const lng = parseFloat(data.longitude);

                        // Validate dari data parameter global
                        const currentTime = new Date();
                        recorded_at = parameters.length > 0 ? parameters[0].recorded_at : null;
                        // Check if device has sensors and recorded_at data

                        let deviceDataTime = new Date(recorded_at);
                        let deviceTimeDiff = (currentTime - deviceDataTime) / (1000 * 60 * 60); // in hours
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

                        iconHtml = data.device_icon;
                        const icon = L.divIcon({
                            html: `<div style="background-color: ${color}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">
                                ${iconHtml}
                               </div>`,
                            iconSize: [32, 32],
                            className: 'custom-div-icon'
                        });
                        const colorText = data.status === 'Online' ? 'green' : 'red';
                        const marker = L.marker([lat, lng], {
                                icon: icon
                            })
                            .bindPopup(`
                            <div style="min-width: 200px;">
                                <h6><strong>${data.device_id}</strong></h6>
                                <strong>Category:</strong> ${data.device_category}<br>
                                <strong>Status:</strong> <span style="color: ${colorText}">${data.status}</span><br>
                                <strong>Coordinates:</strong> ${lat.toFixed(4)}, ${lng.toFixed(4)}
                            </div>
                        `)
                            .addTo(map);

                        // Open popup automatically
                        marker.openPopup();

                        mapMarkers.push(marker);

                        // Center map on marker
                        map.setView([lat, lng], 12);
                    })
                    .catch(error => {
                        console.error('Error loading device location:', error);
                    });
            }

            // ==================== END MAP ====================



            // ==================== LATEST DATA PROGRESS BARS ====================
            function initializeLatestDataProgressBars() {
                // Load progress bar data from API and return Promise
                if (currentDeviceId) {
                    return loadProgressBarData(currentDeviceId);
                }
                return Promise.resolve();
            }

            function loadProgressBarData(deviceId) {
                return fetch(`/user/progress-bar/${deviceId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Progress bar data received:', data);
                        parameters = data; // Store parameters globally
                        renderProgressBars(data);
                        return data; // Return data for chaining
                    })
                    .catch(error => {
                        console.error('Error loading progress bar data:', error);
                        throw error;
                    });
            }

            function renderProgressBars(apiData) {
                const container = document.getElementById('latestDataProgressBars');
                container.innerHTML = '';


                apiData.forEach((item, index) => {
                    const paramName = item.parameter_name;
                    const latestValue = item.latest_value !== null ? parseFloat(item.latest_value) : 0;
                    const maxRange = item.parameter_indicator_range || 100;
                    const unit = item.sensor_unit || '';
                    const recordedAt = item.recorded_at;

                    // Determine color based on recorded_at time difference
                    let color = 'success'; // Default green (recent data)
                    let bootstrapColor = 'success';

                    if (recordedAt) {
                        const currentTime = new Date();
                        const deviceDataTime = new Date(recordedAt);
                        const deviceTimeDiff = (currentTime - deviceDataTime) / (1000 * 60 *
                            60); // in hours

                        if (deviceTimeDiff > 168) { // > 7 days
                            color = '#000000'; // black
                            bootstrapColor = 'dark';
                        } else if (deviceTimeDiff > 72) { // > 3 days
                            color = '#dc3545'; // red
                            bootstrapColor = 'danger';
                        } else if (deviceTimeDiff > 24) { // > 24 hours
                            color = '#fd7e14'; // orange
                            bootstrapColor = 'warning';
                        } else if (deviceTimeDiff > 3) { // > 3 hours
                            color = '#0dcaf0'; // blue/cyan
                            bootstrapColor = 'info';
                        } else {
                            color = '#198754'; // green
                            bootstrapColor = 'success';
                        }
                    } else {
                        // No data recorded
                        color = '#6c757d'; // gray
                        bootstrapColor = 'secondary';
                    }

                    // Calculate percentage based on indicator range
                    const percent = Math.min((latestValue / maxRange) * 100, 100);

                    // Create unique ID for each parameter
                    const paramId = paramName.toLowerCase().replace(/[^a-z0-9]/g, '');

                    const progressHtml = `
                            <div class="${index < apiData.length - 1 ? 'mb-2' : 'mb-0'}">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold"> ${paramName}</span>
                                    <span class="badge bg-${bootstrapColor}" id="${paramId}Value">${latestValue.toFixed(1)} ${unit}</span>
                                </div>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar" role="progressbar" id="${paramId}Progress" style="width: ${percent}%; background-color: ${color};" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100">

                                    </div>
                                </div>
                                <small class="text-muted">Last update: ${recordedAt ? new Date(recordedAt).toLocaleString('en-CA') : 'No data'}</small>
                            </div>
                        `;

                    container.insertAdjacentHTML('beforeend', progressHtml);
                });
            }


            // ==================== END LATEST DATA PROGRESS BARS ====================



            // ==================== LINE CHART ====================
            function initializeLineChart() {
                const ctx = document.getElementById('sensorLineChart');
                if (ctx) {
                    sensorLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Sensor Value',
                                data: [],
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.3)',
                                tension: 0.4,
                                fill: 'origin',
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                pointBackgroundColor: 'rgb(75, 192, 192)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                filler: {
                                    propagate: false
                                },
                                zoom: {
                                    zoom: {
                                        wheel: {
                                            enabled: true,
                                            speed: 0.1,
                                            modifierKey: null
                                        },
                                        drag: {
                                            enabled: true,
                                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                            borderColor: 'rgb(75, 192, 192)',
                                            borderWidth: 1
                                        },
                                        pinch: {
                                            enabled: true
                                        },
                                        mode: 'x',
                                    },
                                    pan: {
                                        enabled: true,
                                        mode: 'x',
                                        modifierKey: 'ctrl'
                                    },
                                    limits: {
                                        x: {
                                            min: 'original',
                                            max: 'original'
                                        },
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Time'
                                    },
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                },
                                y: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Value'
                                    },
                                    beginAtZero: true
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            },
                            elements: {
                                line: {
                                    borderWidth: 2
                                }
                            }
                        }
                    });
                }

                // Parameters will be loaded after progress bar data is available
            }

            let currentSelectedParameter = null;

            function loadLineChartParameters() {
                const data = parameters; // Use globally stored parameters

                if (!data || data.length === 0) {
                    console.error('No parameters available for line chart');
                    return;
                }

                const buttonContainer = document.getElementById('parameterButtons');
                buttonContainer.innerHTML = '';
                data.forEach((item, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-outline-primary btn-sm parameter-btn';
                    button.setAttribute('data-parameter', item.parameter_name);
                    button.setAttribute('data-unit', item.sensor_unit || '');
                    button.innerHTML = `<i class="bi bi-graph-up"></i> ${item.parameter_name}`;

                    // Add click event
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons
                        document.querySelectorAll('.parameter-btn').forEach(btn => {
                            btn.classList.remove('active');
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-outline-primary');
                        });

                        // Add active class to clicked button
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-primary');
                        this.classList.add('active');

                        // Update current selected parameter
                        currentSelectedParameter = this.getAttribute('data-parameter');

                        // Load chart data
                        loadLineChartData(currentDeviceId, currentSelectedParameter);
                    });

                    buttonContainer.appendChild(button);

                    // Auto-select and click first parameter
                    if (index === 0) {
                        button.classList.remove('btn-outline-primary');
                        button.classList.add('btn-primary');
                        button.classList.add('active');
                        currentSelectedParameter = item.parameter_name;
                        loadLineChartData(currentDeviceId, item.parameter_name);
                    }
                });


            }

            function loadLineChartData(deviceId, parameterName) {
                if (!parameterName) {
                    return;
                }

                // Show loader
                document.getElementById('lineChartLoader').style.display = 'block';
                document.getElementById('sensorLineChart').style.display = 'none';

                // Fetch chart data from API (24 hours fixed window for today)
                fetch(`/user/line-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}&interval=5`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loader
                        document.getElementById('lineChartLoader').style.display = 'none';
                        document.getElementById('sensorLineChart').style.display = 'block';



                        // Update chart
                        if (sensorLineChart && data.labels && data.values) {
                            sensorLineChart.data.labels = data.labels;
                            sensorLineChart.data.datasets[0].data = data.values;
                            sensorLineChart.data.datasets[0].label = `${parameterName} (${data.unit || ''})`;
                            sensorLineChart.options.scales.y.title.text =
                                `${parameterName} (${data.unit || ''})`;

                            // Update chart options for fixed 24-hour display
                            sensorLineChart.options.scales.x.ticks = {
                                maxRotation: 45,
                                minRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 24 // Show approximately 24 labels (every hour)
                            };

                            // Connect gaps with line (data flows from left to right)
                            sensorLineChart.data.datasets[0].spanGaps = true; // Connect null values

                            // Animation config for smooth data addition
                            sensorLineChart.options.animation = {
                                duration: 750,
                                easing: 'easeInOutQuart'
                            };

                            sensorLineChart.update();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading line chart data:', error);
                        document.getElementById('lineChartLoader').style.display = 'none';
                        document.getElementById('sensorLineChart').style.display = 'block';
                    });
            }

            // ==================== END LINE CHART ====================



            // ==================== BAR CHART ====================
            function initializeBarChart() {
                const ctx = document.getElementById('sensorBarChart');
                if (ctx) {
                    sensorBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Hourly Average',
                                data: [],
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                borderColor: 'rgb(54, 162, 235)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y.toFixed(2);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Hour'
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Average Value'
                                    },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Parameters will be loaded after progress bar data is available
            }

            let currentSelectedBarParameter = null;

            function loadBarChartParameters() {
                const data = parameters; // Use globally stored parameters

                if (!data || data.length === 0) {
                    console.error('No parameters available for bar chart');
                    return;
                }

                const buttonContainer = document.getElementById('barParameterButtons');
                buttonContainer.innerHTML = '';

                data.forEach((item, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-outline-info btn-sm bar-parameter-btn';
                    button.setAttribute('data-parameter', item.parameter_name);
                    button.setAttribute('data-unit', item.sensor_unit || '');
                    button.innerHTML = `<i class="bi bi-bar-chart"></i> ${item.parameter_name}`;

                    // Add click event
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons
                        document.querySelectorAll('.bar-parameter-btn').forEach(btn => {
                            btn.classList.remove('active');
                            btn.classList.remove('btn-info');
                            btn.classList.add('btn-outline-info');
                        });

                        // Add active class to clicked button
                        this.classList.remove('btn-outline-info');
                        this.classList.add('btn-info');
                        this.classList.add('active');

                        // Update current selected parameter
                        currentSelectedBarParameter = this.getAttribute('data-parameter');

                        // Load chart data
                        loadBarChartData(currentDeviceId, currentSelectedBarParameter);
                    });

                    buttonContainer.appendChild(button);

                    // Auto-select and click first parameter
                    if (index === 0) {
                        button.classList.remove('btn-outline-info');
                        button.classList.add('btn-info');
                        button.classList.add('active');
                        currentSelectedBarParameter = item.parameter_name;
                        loadBarChartData(currentDeviceId, item.parameter_name);
                    }
                });
            }

            function loadBarChartData(deviceId, parameterName) {
                if (!parameterName) {
                    return;
                }

                // Show loader
                document.getElementById('barChartLoader').style.display = 'block';
                document.getElementById('sensorBarChart').style.display = 'none';

                // Fetch chart data from API
                fetch(`/user/bar-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loader
                        document.getElementById('barChartLoader').style.display = 'none';
                        document.getElementById('sensorBarChart').style.display = 'block';

                        // Update chart
                        if (sensorBarChart && data.labels && data.values) {
                            sensorBarChart.data.labels = data.labels;
                            sensorBarChart.data.datasets[0].data = data.values;
                            sensorBarChart.data.datasets[0].label =
                                `${parameterName} - Hourly Avg (${data.unit || ''})`;
                            sensorBarChart.options.scales.y.title.text =
                                `Average ${parameterName} (${data.unit || ''})`;

                            // Animation config
                            sensorBarChart.options.animation = {
                                duration: 750,
                                easing: 'easeInOutQuart'
                            };

                            sensorBarChart.update();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading bar chart data:', error);
                        document.getElementById('barChartLoader').style.display = 'none';
                        document.getElementById('sensorBarChart').style.display = 'block';
                    });
            }

            // Reset Zoom Button for Line Chart
            // document.getElementById('resetZoomLineChart').addEventListener('click', function() {
            //     if (sensorLineChart) {
            //         sensorLineChart.resetZoom();
            //     }
            // });
            // ==================== END BAR CHART ====================



            // ==================== WIND ROSE CHART ====================
            function degToCompass16(deg) {
                const directions = [
                    'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                    'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
                ];
                const index = Math.round(deg / 22.5) % 16;
                return directions[index];
            }

            function checkAndShowWindRose() {
                // Check if Wind Direction parameter exists
                const hasWindDirection = parameters.some(param =>
                    param.parameter_name && param.parameter_name.toLowerCase().includes('windrose')
                );

                if (hasWindDirection) {
                    document.getElementById('windRoseRow').style.display = 'block';
                    renderWindRose();
                } else {
                    document.getElementById('windRoseRow').style.display = 'none';
                }
            }

            async function renderWindRose(range = "realtime") {
                if (!currentDeviceId) return;

                // Show loader
                document.getElementById('windRoseLoader').style.display = 'block';
                document.getElementById('windRoseChart').style.display = 'none';

                try {
                    const res = await fetch(`/user/wind-rose-data/${currentDeviceId}?range=${range}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute(
                                    'content') || ''
                        }
                    });
                    const data = await res.json();

                    // Hide loader
                    document.getElementById('windRoseLoader').style.display = 'none';
                    document.getElementById('windRoseChart').style.display = 'block';

                    // Buat array dari arah dan kecepatan
                    const rawData = [];
                    for (let i = 0; i < data.wdir.length; i++) {
                        const dir = data.wdir[i];
                        const spd = data.wspeed[i];
                        if (dir !== null && spd !== null) {
                            rawData.push({
                                dir: degToCompass16(dir),
                                speed: spd
                            });
                        }
                    }

                    // Kelompokkan data berdasarkan arah
                    const grouped = {};
                    rawData.forEach(d => {
                        if (!grouped[d.dir]) grouped[d.dir] = [];
                        grouped[d.dir].push(d.speed);
                    });

                    const directions = [
                        'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                        'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
                    ];

                    const meanSpeeds = directions.map(dir => {
                        const speeds = grouped[dir] || [];
                        const mean = speeds.length ? speeds.reduce((a, b) => a + b, 0) / speeds.length :
                            0;
                        return +mean.toFixed(2);
                    });

                    const trace = {
                        type: 'barpolar',
                        r: meanSpeeds,
                        theta: directions,
                        name: 'Wind Speed',
                        marker: {
                            color: meanSpeeds,
                            colorscale: 'Bluered',
                            colorbar: {
                                title: 'm/s',
                                thickness: 10
                            }
                        }
                    };

                    const layout = {
                        title: 'Wind Rose (Average Speed)',
                        polar: {
                            angularaxis: {
                                direction: 'clockwise',
                                rotation: 90
                            },
                            radialaxis: {
                                ticksuffix: ' m/s',
                                angle: 45
                            }
                        },
                        margin: {
                            t: 50,
                            b: 30,
                            l: 30,
                            r: 30
                        },
                        showlegend: false
                    };

                    Plotly.newPlot("windRoseChart", [trace], layout);

                } catch (e) {
                    console.error("Error rendering wind rose:", e);
                    document.getElementById('windRoseLoader').style.display = 'none';
                    document.getElementById('windRoseChart').style.display = 'block';
                    document.getElementById('windRoseChart').innerHTML =
                        '<div class="alert alert-warning text-center">Failed to load wind rose data</div>';
                }
            }

            function degToCompass16(deg) {
                const directions = [
                    'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                    'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
                ];
                const index = Math.round(deg / 22.5) % 16;
                return directions[index];
            }





            // ==================== END WIND ROSE CHART ====================


            /*
                            // ==================== MODAL BATAS ACUAN ====================
                            // Show modal and load data for Line Chart
                            const referenceModal = document.getElementById('referenceModal');
                            if (referenceModal) {
                                referenceModal.addEventListener('show.bs.modal', function() {
                                    loadReferenceData();
                                });
                            }

                            // Show modal and load data for Bar Chart
                            const referenceBarModal = document.getElementById('referenceBarModal');
                            if (referenceBarModal) {
                                referenceBarModal.addEventListener('show.bs.modal', function() {
                                    loadReferenceBarData();
                                });
                            }

                            function loadReferenceData() {
                                const modalBody = document.getElementById('referenceModalBody');

                                if (!currentDeviceId) {
                                    modalBody.innerHTML = '<div class="alert alert-warning">Please select a device first.</div>';
                                    return;
                                }

                                // Show loading
                                modalBody.innerHTML = `
            <div class="text-center text-muted py-5">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Loading reference data...
            </div>
        `;

                                // Fetch data using progress bar API (contains parameter info)
                                fetch(`/user/progress-bar/${currentDeviceId}`, {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    renderReferenceTable(data, modalBody);
                                })
                                .catch(error => {
                                    console.error('Error loading reference data:', error);
                                    modalBody.innerHTML = '<div class="alert alert-danger">Failed to load reference data.</div>';
                                });
                            }

                            function loadReferenceBarData() {
                                const modalBody = document.getElementById('referenceBarModalBody');

                                if (!currentDeviceId) {
                                    modalBody.innerHTML = '<div class="alert alert-warning">Please select a device first.</div>';
                                    return;
                                }

                                // Show loading
                                modalBody.innerHTML = `
            <div class="text-center text-muted py-5">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Loading reference data...
            </div>
        `;

                                // Fetch data using progress bar API
                                fetch(`/user/progress-bar/${currentDeviceId}`, {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    renderReferenceTable(data, modalBody);
                                })
                                .catch(error => {
                                    console.error('Error loading reference data:', error);
                                    modalBody.innerHTML = '<div class="alert alert-danger">Failed to load reference data.</div>';
                                });
                            }

                            function renderReferenceTable(data, container) {
                                if (!data || data.length === 0) {
                                    container.innerHTML = '<div class="alert alert-info">No reference data available.</div>';
                                    return;
                                }

                                let tableHtml = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Parameter</th>
                            <th>Batas Min</th>
                            <th>Batas Max</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

                                data.forEach((item, index) => {
                                    const minValue = item.parameter_indicator_min !== null ? item.parameter_indicator_min : '-';
                                    const maxValue = item.parameter_indicator_max !== null ? item.parameter_indicator_max : '-';
                                    const unit = item.sensor_unit || '-';
                                    const status = item.parameter_indicator_range ?
                                        '<span class="badge bg-success">Active</span>' :
                                        '<span class="badge bg-secondary">No Range</span>';

                                    tableHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.parameter_name}</strong></td>
                    <td>${minValue}</td>
                    <td>${maxValue}</td>
                    <td>${unit}</td>
                    <td>${status}</td>
                </tr>
            `;
                                });

                                tableHtml += `
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    Data batas acuan digunakan untuk monitoring parameter sensor
                </small>
            </div>
        `;

                                container.innerHTML = tableHtml;
                            }
                            // ==================== END MODAL BATAS ACUAN ====================
                */


            // ==================== HISTORICAL DATA TABLE ====================
            let historicalDataTable = null;

            function initializeHistoricalDataTable() {
                // Set default date range (last 7 days to today)
                const today = new Date();
                const sevenDaysAgo = new Date();
                sevenDaysAgo.setDate(today.getDate() - 7);

                document.getElementById('historicalStartDate').valueAsDate = sevenDaysAgo;
                document.getElementById('historicalEndDate').valueAsDate = today;

                // Initialize DataTable
                historicalDataTable = $('#historicalDataTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '/user/historical-data',
                        type: 'GET',
                        data: function(d) {
                            d.device_id = currentDeviceId;
                            d.start_date = document.getElementById('historicalStartDate').value;
                            d.end_date = document.getElementById('historicalEndDate').value;
                        },
                        error: function(xhr, error, thrown) {
                            console.error('DataTables error:', error, thrown);
                        }
                    },
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'parameter_name',
                            name: 'parameter_name'
                        },
                        {
                            data: 'value',
                            name: 'value',
                            render: function(data, type, row) {
                                return parseFloat(data).toFixed(2);
                            }
                        },
                        {
                            data: 'unit',
                            name: 'unit'
                        },
                        {
                            data: 'recorded_at',
                            name: 'recorded_at',
                            render: function(data, type, row) {
                                return new Date(data).toLocaleString('en-CA', {
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: false
                                });
                            }
                        },
                        {
                            data: 'status',
                            name: 'status',
                            render: function(data, type, row) {
                                let badgeClass = 'secondary';
                                if (data === 'Normal') badgeClass = 'success';
                                else if (data === 'Warning') badgeClass = 'warning';
                                else if (data === 'Critical') badgeClass = 'danger';
                                return `<span class="badge bg-${badgeClass}">${data || 'N/A'}</span>`;
                            }
                        }
                    ],
                    order: [
                        [4, 'desc']
                    ], // Order by recorded_at descending
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],
                    language: {
                        processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading data...',
                        emptyTable: 'No historical data available',
                        zeroRecords: 'No matching records found'
                    }
                });
            }

            // Load Historical Data Button
            document.getElementById('loadHistoricalData').addEventListener('click', function() {
                const startDate = document.getElementById('historicalStartDate').value;
                const endDate = document.getElementById('historicalEndDate').value;

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }

                if (new Date(startDate) > new Date(endDate)) {
                    alert('Start date must be before end date');
                    return;
                }

                if (historicalDataTable) {
                    historicalDataTable.ajax.reload();
                }
            });

            // Export Historical Data Button
            document.getElementById('exportHistoricalData').addEventListener('click', function() {
                const startDate = document.getElementById('historicalStartDate').value;
                const endDate = document.getElementById('historicalEndDate').value;

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }

                if (!currentDeviceId) {
                    alert('Please select a device first');
                    return;
                }

                // Create download URL
                const exportUrl =
                    `/user/historical-data/export?device_id=${currentDeviceId}&start_date=${startDate}&end_date=${endDate}`;

                // Open in new window to download
                window.open(exportUrl, '_blank');
            });

            // ==================== END HISTORICAL DATA TABLE ====================


            // ==================== INITIALIZE ALL ====================


            // Initialize progress bars first, then load charts after data is available
            initializeLatestDataProgressBars()
                .then(() => {
                    // After progress bar data is loaded, initialize charts
                    initializeMap();
                    initializeLineChart();
                    initializeBarChart();
                    initializeHistoricalDataTable();

                    // Load parameters for both charts using the cached data
                    if (currentDeviceId && parameters.length > 0) {
                        loadLineChartParameters();
                        loadBarChartParameters();

                        // Check and show wind rose if wind direction parameter exists
                        checkAndShowWindRose();
                    }
                })
                .catch(error => {
                    console.error('Error during initialization:', error);
                });

            // Auto refresh data every 60 seconds
            setInterval(function() {
                if (currentDeviceId) {
                    // Refresh map with fresh device status
                    loadMapData(currentDeviceId);

                    // Refresh progress bars
                    loadProgressBarData(currentDeviceId);

                    // Refresh line chart if parameter is selected
                    if (currentSelectedParameter) {
                        loadLineChartData(currentDeviceId, currentSelectedParameter);
                    }

                    // Refresh bar chart if parameter is selected
                    if (currentSelectedBarParameter) {
                        loadBarChartData(currentDeviceId, currentSelectedBarParameter);
                    }

                    // Refresh wind rose if visible
                    const windRoseRow = document.getElementById('windRoseRow');
                    if (windRoseRow && windRoseRow.style.display !== 'none') {
                        renderWindRose();
                    }
                }
            }, 60000);
        });
    </script>
@endsection
