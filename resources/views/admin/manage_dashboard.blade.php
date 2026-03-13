@extends('layout.admin')
@section('title', 'Manage Dashboard')

<style>
    /* Device Selector Dropdown */
.device-selector {
    position: relative;
    margin-left: 1rem;
    flex-shrink: 0;
    z-index: 100;
}

.device-selector-toggle {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 180px;
    justify-content: space-between;
}

.device-selector-toggle:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.device-selector-toggle .device-icon {
    color: var(--primary-color);
    margin-right: 0.5rem;
}

.device-selector-toggle .device-name {
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.device-selector-toggle .dropdown-icon {
    color: #6c757d;
    transition: transform 0.3s ease;
}

.device-selector.show .device-selector-toggle .dropdown-icon {
    transform: rotate(180deg);
}

.device-selector-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 250px;
    max-width: 300px;
    z-index: 101;
    margin-top: 8px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    max-height: 400px;
    overflow-y: auto;
}

.device-selector.show .device-selector-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.device-selector-menu::before {
    content: "";
    position: absolute;
    top: -8px;
    right: 20px;
    width: 16px;
    height: 16px;
    background-color: white;
    transform: rotate(45deg);
    box-shadow: -2px -2px 5px rgba(0, 0, 0, 0.05);
}

.device-group {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f1f1;
}

.device-group:last-child {
    border-bottom: none;
}

.device-group-title {
    padding: 0.5rem 1rem;
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.device-option {
    padding: 0.5rem 1rem 0.5rem 1.5rem;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.device-option:hover {
    background-color: #f8f9fa;
    color: var(--primary-color);
}

.device-option.active {
    background-color: rgba(1, 179, 188, 0.1);
    color: var(--primary-color);
    font-weight: 500;
}

.device-option .device-status {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.75rem;
}

.device-option .status-online {
    background-color: #198754;
}

.device-option .status-offline {
    background-color: #dc3545;
}

.device-option .device-id {
    font-size: 0.8rem;
    color: #6c757d;
    margin-left: auto;
}
</style>



    
<style>
        /* Preserve chart height on small screens */
        #sensorLineChart,
        #sensorBarChart,
        #historicalLineChart {
            min-height: 300px !important;
            height: auto !important;
        }

        /* Responsive date inputs for Historical Chart */
        .input-group-sm .input-group-text {
            padding: 0.25rem 0.5rem;
        }

        /* Full width on mobile */
        @media (max-width: 767px) {
            .input-group-sm {
                width: 100%;
            }
            
            #loadHistoricalChart {
                width: 100%;
            }
        }

        /* Side by side on tablet and up */
        @media (min-width: 768px) {
            .input-group-sm {
                flex: 1;
                min-width: 160px;
                max-width: 200px;
            }
        }

        /* Ensure chart containers maintain minimum height on mobile */
        @media (max-width: 768px) {

            #sensorLineChart,
            #sensorBarChart,
            #historicalLineChart {
                min-height: 350px !important;
            }

            .card-body canvas {
                min-height: 350px !important;
            }
        }

        @media (max-width: 576px) {

            #sensorLineChart,
            #sensorBarChart,
            #historicalLineChart {
                min-height: 400px !important;
            }

            .card-body canvas {
                min-height: 400px !important;
            }

            /* Smaller text on mobile for better fit */
            .card-header h5 {
                font-size: 0.95rem;
            }
        }
</style>


@section('content')
    <!-- Device Selector Component -->
    <div class="card mb-4" style="margin-top:50px">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #DAD7D7">
            <h5 class="mb-0">
                <i class="bi bi-cpu-fill"></i> Device Monitoring
            </h5>
            <div class="device-selector" id="deviceSelector">
                <div class="device-selector-toggle" id="deviceSelectorToggle">
                    <i class="bi bi-cpu device-icon"></i>
                    <span class="device-name">{{ isset($deviceCategories[0]['devices'][0]) ? $deviceCategories[0]['devices'][0]['device_id'] : 'Select Device' }}</span>
                    <i class="bi bi-chevron-down dropdown-icon"></i>
                </div>

                <div class="device-selector-menu">
                    @if(isset($deviceCategories) && count($deviceCategories) > 0)
                        @foreach($deviceCategories as $categoryIndex => $category)
                            <div class="device-group">
                                <div class="device-group-title">{{ $category['device_category'] }}</div>
                                @foreach($category['devices'] as $deviceIndex => $device)
                                    <div class="device-option {{ $categoryIndex === 0 && $deviceIndex === 0 ? 'active' : '' }}" data-value="{{ $device['device_id'] }}">
                                        <span>{{ $device['device_name'] }} - ({{ $device['device_id'] }})   </span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="device-group">
                            <div class="text-center p-3 text-muted">
                                <i class="bi bi-info-circle"></i> No devices available
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


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
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #DAD7D7">
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
                <div class="card-header" style="background-color: #DAD7D7">
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

                    <!-- Tombol Regulation Limits Acuan -->
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-outline-secondary" id="regulationLimitsChart">
                            <i class="bi bi-sliders"></i> Regulation Limits
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Bar Chart dengan Button Parameter -->
        <div class="col-lg-6 col-md-12 mb-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #DAD7D7">
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

                    <!-- Tombol Regulation Limits Bar Chart -->
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-outline-secondary" id="regulationLimitsBar">
                            <i class="bi bi-sliders"></i> Regulation Limits
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
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #DAD7D7">
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

    <!-- Card Historical Data Chart Line -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 mb-2">
            <div class="card">
                <div class="card-header" style="background-color: #DAD7D7">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-lg-4">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up"></i> Historical Data Chart Line
                            </h5>
                        </div>
                        <div class="col-12 col-lg-8">
                            <div class="d-flex flex-column flex-md-row gap-2 justify-content-lg-end">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="text" class="form-control" id="historicalChartStartDate"
                                        placeholder="Start Date" autocomplete="off" />
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="text" class="form-control" id="historicalChartEndDate"
                                        placeholder="End Date" autocomplete="off" />
                                </div>
                                <button class="btn btn-sm btn-primary flex-shrink-0" id="loadHistoricalChart">
                                    <i class="bi bi-arrow-clockwise"></i> Show Chart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="historicalChartLoader" class="text-center text-muted py-5" style="display: none;">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading chart data...

                    </div>
                    <div style="min-height: 400px; position: relative;">
                        <canvas id="historicalLineChart"></canvas>
                    </div>
                    <!-- Parameter Selection Buttons for Historical Chart - Moved Below Chart -->
                    <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center" id="historicalParameterButtons">
                        <!-- Buttons will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

   

@endsection


@section('script')
    

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {

            // ==================== DEVICE SELECTOR ====================
            let currentSelectedDevice = null;
            let activeDeviceRequestId = 0;
            let isSwitchingDevice = false;
            const endpointControllers = {
                map: null,
                progress: null,
                line: null,
                bar: null,
                historical: null,
                windRose: null,
                reference: null,
                referenceBar: null
            };

            const deviceSelector = document.getElementById('deviceSelector');
            const deviceSelectorToggle = document.getElementById('deviceSelectorToggle');
            const deviceOptions = document.querySelectorAll('.device-option');
            const deviceNameElements = document.querySelectorAll('.device-name');

            function isStaleRequest(requestId) {
                return requestId !== null && requestId !== undefined && requestId !== activeDeviceRequestId;
            }

            function createEndpointSignal(endpointKey) {
                if (endpointControllers[endpointKey]) {
                    endpointControllers[endpointKey].abort();
                }

                const controller = new AbortController();
                endpointControllers[endpointKey] = controller;
                return controller.signal;
            }

            function setLoaderLoading(loaderId, message = 'Loading data...') {
                const loader = document.getElementById(loaderId);
                if (!loader) return;

                loader.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        ${message}
                    </div>
                `;
                loader.style.display = 'block';
            }

            function setLoaderNoData(loaderId, message = 'No data available') {
                const loader = document.getElementById(loaderId);
                if (!loader) return;

                loader.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox me-2"></i>${message}
                    </div>
                `;
                loader.style.display = 'block';
            }

            function setDeviceLoadingState(isLoading, selectedDevice = null) {
                isSwitchingDevice = isLoading;

                if (deviceSelectorToggle) {
                    deviceSelectorToggle.style.pointerEvents = isLoading ? 'none' : 'auto';
                    deviceSelectorToggle.style.opacity = isLoading ? '0.75' : '1';
                }

                if (isLoading) {
                    const loadingLabel = selectedDevice ? `Loading ${selectedDevice}...` : 'Loading...';
                    deviceNameElements.forEach(nameEl => {
                        nameEl.textContent = loadingLabel;
                    });

                    const latestDataContainer = document.getElementById('latestDataProgressBars');
                    if (latestDataContainer) {
                        latestDataContainer.innerHTML = `
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                Loading device data...
                            </div>
                        `;
                    }

                    const lineLoader = document.getElementById('lineChartLoader');
                    const lineCanvas = document.getElementById('sensorLineChart');
                    if (lineLoader && lineCanvas) {
                        setLoaderLoading('lineChartLoader', 'Loading chart data...');
                        lineCanvas.style.display = 'none';
                    }

                    const barLoader = document.getElementById('barChartLoader');
                    const barCanvas = document.getElementById('sensorBarChart');
                    if (barLoader && barCanvas) {
                        setLoaderLoading('barChartLoader', 'Loading chart data...');
                        barCanvas.style.display = 'none';
                    }
                }
            }

            async function handleDeviceChange(selectedDevice) {
                if (!selectedDevice || (isSwitchingDevice && selectedDevice === currentDeviceId)) {
                    return;
                }

                const requestId = ++activeDeviceRequestId;
                setDeviceLoadingState(true, selectedDevice);

                // Update current selected device
                currentSelectedDevice = selectedDevice;
                currentDeviceId = selectedDevice;

                // Close dropdowns
                if (deviceSelector) {
                    deviceSelector.classList.remove('show');
                }
                if (mobileDeviceSelector) {
                    mobileDeviceSelector.classList.remove('show');
                }

                // Reset parameters cache and current selections
                parameters = [];
                currentSelectedParameter = null;
                currentSelectedBarParameter = null;
                currentHistoricalParameter = null;

                try {
                    // Jalankan data utama secara async paralel agar ringan
                    const [progressResult] = await Promise.allSettled([
                        loadProgressBarData(selectedDevice, requestId),
                        loadMapData(selectedDevice, requestId)
                    ]);

                    if (isStaleRequest(requestId)) {
                        return;
                    }

                    if (progressResult.status === 'fulfilled' && parameters.length > 0) {
                        loadLineChartParameters();
                        loadBarChartParameters();
                        loadHistoricalChartParameters();

                        checkAndShowWindRose(requestId);
                    } else {
                        setLoaderNoData('lineChartLoader', 'No data available');
                        setLoaderNoData('barChartLoader', 'No data available');

                        const lineCanvas = document.getElementById('sensorLineChart');
                        const barCanvas = document.getElementById('sensorBarChart');
                        if (lineCanvas) lineCanvas.style.display = 'none';
                        if (barCanvas) barCanvas.style.display = 'none';

                        const historicalLoader = document.getElementById('historicalChartLoader');
                        const historicalCanvas = document.getElementById('historicalLineChart');
                        if (historicalCanvas) historicalCanvas.style.display = 'none';
                        if (historicalLoader) {
                            setLoaderNoData('historicalChartLoader', 'No data available');
                        }

                        const windRoseRow = document.getElementById('windRoseRow');
                        if (windRoseRow) windRoseRow.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error updating device data:', error);
                } finally {
                    if (!isStaleRequest(requestId)) {
                        setDeviceLoadingState(false);

                        deviceNameElements.forEach(nameEl => {
                            nameEl.textContent = currentDeviceId || 'Select Device';
                        });
                    }
                }
            }

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
                option.addEventListener('click', async function() {
                    const selectedDevice = this.getAttribute('data-value');

                    // Update active state
                    deviceOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');

                    await handleDeviceChange(selectedDevice);
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

            function loadMapData(deviceId, requestId = null) {
                if (!deviceId) return Promise.resolve();
                return loadDeviceMarker(deviceId, requestId);
            }

            function loadDeviceMarker(deviceId, requestId = null) {
                if (!deviceId) return Promise.resolve();
                const signal = createEndpointSignal('map');

                // Fetch device data with fresh status
                return fetch(`/admin/maps-dashboard/${deviceId}`, {
                        method: 'GET',
                        signal,
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
                        if (isStaleRequest(requestId)) {
                            return;
                        }

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
                                <h6><strong>${data.device_name}</strong></h6>
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
                        if (error.name === 'AbortError') {
                            return;
                        }
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

            function loadProgressBarData(deviceId, requestId = null) {
                const signal = createEndpointSignal('progress');
                return fetch(`/admin/progress-bar/${deviceId}`, {
                        method: 'GET',
                        signal,
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
                        if (isStaleRequest(requestId)) {
                            return data;
                        }

                        console.log('Progress bar data received:', data);
                        parameters = data; // Store parameters globally
                        renderProgressBars(data);
                        return data; // Return data for chaining
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return [];
                        }
                        console.error('Error loading progress bar data:', error);
                        throw error;
                    });
            }

            function renderProgressBars(apiData) {
                const container = document.getElementById('latestDataProgressBars');
                container.innerHTML = '';

                if (!apiData || apiData.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox me-2"></i>No data available
                        </div>
                    `;
                    return;
                }


                apiData.forEach((item, index) => {
                    const paramName = item.parameter_label;
                    const latestValue = item.latest_value !== null ? parseFloat(item.latest_value) : 0;
                    const maxRange = item.parameter_indicator_max || 100;
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
                                pointBorderWidth: 2,
                                spanGaps: false, // Jangan hubungkan gap > 5 menit
                                segment: {
                                    borderColor: ctx => ctx.p0.skip || ctx.p1.skip ?
                                        'rgba(0,0,0,0)' : undefined,
                                }
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
            let showRegulationLimits = false; // Track regulation limits state for line chart

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
                    button.setAttribute('data-indicator-max', item.parameter_indicator_max || '');
                    button.setAttribute('data-indicator-min', item.parameter_indicator_min || '');
                    button.setAttribute('data-unit', item.sensor_unit || '');
                    button.innerHTML = `<i class="bi bi-graph-up"></i> ${item.parameter_label}`;

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

                const signal = createEndpointSignal('line');

                // Show loader
                setLoaderLoading('lineChartLoader', 'Loading chart data...');
                document.getElementById('sensorLineChart').style.display = 'none';

                // Fetch chart data from API (rolling 24 hours from current time)
                fetch(`/admin/line-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}`, {
                        method: 'GET',
                        signal,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.labels || !data.values || data.labels.length === 0 || data.values.length ===
                            0) {
                            setLoaderNoData('lineChartLoader', 'No data available');
                            document.getElementById('sensorLineChart').style.display = 'none';
                            return;
                        }

                        // Hide loader
                        document.getElementById('lineChartLoader').style.display = 'none';
                        document.getElementById('sensorLineChart').style.display = 'block';

                        // Update chart
                        if (sensorLineChart && data.labels && data.values) {
                            sensorLineChart.data.labels = data.labels;
                            sensorLineChart.data.datasets[0].data = data.values;
                            sensorLineChart.data.datasets[0].label = `${data.parameter_label} (${data.unit || ''})`;
                            sensorLineChart.options.scales.y.title.text =
                                `${data.parameter_label} (${data.unit || ''})`;

                            // Verify last label matches current time
                            const lastLabel = data.labels[data.labels.length - 1];


                            // Update chart options for rolling 24-hour display
                            sensorLineChart.options.scales.x.ticks = {
                                maxRotation: 45,
                                minRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 24 // Show approximately 24 labels (every hour)
                            };

                            // Jangan hubungkan gap lebih dari 5 menit
                            sensorLineChart.data.datasets[0].spanGaps = false;

                            // Animation config for smooth data addition
                            sensorLineChart.options.animation = {
                                duration: 750,
                                easing: 'easeInOutQuart'
                            };

                            sensorLineChart.update();
                            
                            // Update regulation limits if enabled
                            if (showRegulationLimits) {
                                updateRegulationLimitsChart();
                            }
                        }
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading line chart data:', error);
                        setLoaderNoData('lineChartLoader', 'No data available');
                        document.getElementById('sensorLineChart').style.display = 'none';
                    });
            }

            // Smooth update function for auto-refresh (no full reset)
            function loadLineChartDataSmooth(deviceId, parameterName) {
                if (!parameterName || !sensorLineChart) {
                    return;
                }

                const signal = createEndpointSignal('line');

                // Fetch chart data from API without showing loader
                fetch(`/admin/line-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}`, {
                        method: 'GET',
                        signal,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (sensorLineChart && data.labels && data.values) {
                            // Backend sudah mengirim data 24 jam terakhir yang tepat
                            // Kita hanya perlu update chart dengan data terbaru dari backend

                            const currentLabels = sensorLineChart.data.labels;
                            const currentData = sensorLineChart.data.datasets[0].data;

                            // Check if we have existing data
                            if (currentLabels.length > 0 && data.labels.length > 0) {
                                const currentLastLabel = currentLabels[currentLabels.length - 1];
                                const apiLastLabel = data.labels[data.labels.length - 1];

                                // Cari data baru yang belum ada di chart
                                const newDataToAdd = [];
                                let foundCurrentLast = false;

                                for (let i = 0; i < data.labels.length; i++) {
                                    if (foundCurrentLast) {
                                        newDataToAdd.push({
                                            label: data.labels[i],
                                            value: data.values[i]
                                        });
                                    }
                                    if (data.labels[i] === currentLastLabel) {
                                        foundCurrentLast = true;
                                    }
                                }

                                // Jika ada data baru, tambahkan ke chart
                                if (newDataToAdd.length > 0) {
                                    // Tambahkan data baru di akhir
                                    newDataToAdd.forEach(point => {
                                        sensorLineChart.data.labels.push(point.label);
                                        sensorLineChart.data.datasets[0].data.push(point.value);
                                    });

                                    // Hitung berapa data yang harus dihapus dari awal
                                    // Hanya hapus data yang sudah lebih dari 24 jam
                                    const now = new Date();
                                    const twentyFourHoursAgo = new Date(now.getTime() - (24 * 60 * 60 * 1000));

                                    let removeCount = 0;
                                    for (let i = 0; i < sensorLineChart.data.labels.length; i++) {
                                        const labelStr = sensorLineChart.data.labels[i];
                                        // Parse label format: Y-m-d H:i (e.g., "2024-12-06 10:30")
                                        const labelDate = new Date(labelStr);

                                        if (labelDate < twentyFourHoursAgo) {
                                            removeCount++;
                                        } else {
                                            break; // Data sudah dalam range 24 jam
                                        }
                                    }

                                    // Hapus data yang sudah lebih dari 24 jam
                                    if (removeCount > 0) {
                                        sensorLineChart.data.labels.splice(0, removeCount);
                                        sensorLineChart.data.datasets[0].data.splice(0, removeCount);
                                        console.log('Line Chart - Removed', removeCount,
                                            'old data points (>24h)');
                                    }

                                    console.log('Line Chart Update - Added', newDataToAdd.length,
                                        'new data points. Total points:', sensorLineChart.data.labels
                                        .length,
                                        'Last label:', apiLastLabel);

                                    // Smooth animation for new data
                                    sensorLineChart.options.animation = {
                                        duration: 1000,
                                        easing: 'easeInOutCubic'
                                    };
                                    sensorLineChart.update('active');
                                } else {
                                    // Tidak ada data baru, tapi periksa apakah perlu hapus data lama
                                    const now = new Date();
                                    const twentyFourHoursAgo = new Date(now.getTime() - (24 * 60 * 60 * 1000));

                                    let removeCount = 0;
                                    for (let i = 0; i < sensorLineChart.data.labels.length; i++) {
                                        const labelStr = sensorLineChart.data.labels[i];
                                        const labelDate = new Date(labelStr);

                                        if (labelDate < twentyFourHoursAgo) {
                                            removeCount++;
                                        } else {
                                            break;
                                        }
                                    }

                                    if (removeCount > 0) {
                                        sensorLineChart.data.labels.splice(0, removeCount);
                                        sensorLineChart.data.datasets[0].data.splice(0, removeCount);
                                        console.log('Line Chart - Removed', removeCount,
                                            'old data points (>24h)');
                                        sensorLineChart.update('active');
                                    }
                                }
                            } else {
                                // First load - use full data from backend (already 24 hours)
                                sensorLineChart.data.labels = data.labels;
                                sensorLineChart.data.datasets[0].data = data.values;
                                console.log('Line Chart - First load with', data.labels.length, 'data points');
                                sensorLineChart.update();
                            }
                        }
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading line chart data (smooth):', error);
                    });
            }

            // Toggle Regulation Limits for Line Chart
            function toggleRegulationLimitsChart() {
                showRegulationLimits = !showRegulationLimits;
                const button = document.getElementById('regulationLimitsChart');
                
                if (showRegulationLimits) {
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-secondary');
                } else {
                    button.classList.remove('btn-secondary');
                    button.classList.add('btn-outline-secondary');
                }
                
                updateRegulationLimitsChart();
            }

            function updateRegulationLimitsChart() {
                if (!sensorLineChart) return;
                
                // Remove existing regulation limit datasets
                sensorLineChart.data.datasets = sensorLineChart.data.datasets.filter(ds => 
                    !ds.label || (!ds.label.includes('Max Limit') && !ds.label.includes('Min Limit'))
                );
                
                if (showRegulationLimits && currentSelectedParameter) {
                    // Get indicator range from active parameter button
                    const activeButton = document.querySelector('.parameter-btn.active');
                    if (activeButton) {
                        const maxLimit = parseFloat(activeButton.getAttribute('data-indicator-max'));
                        const minLimit = parseFloat(activeButton.getAttribute('data-indicator-min'));
                        const unit = activeButton.getAttribute('data-unit') || '';
                        const labels = sensorLineChart.data.labels;
                        
                        // Add max limit line
                        if (maxLimit && !isNaN(maxLimit)) {
                            const maxLimitData = new Array(labels.length).fill(maxLimit);
                            
                            sensorLineChart.data.datasets.push({
                                label: `Max Limit (${maxLimit} ${unit})`,
                                data: maxLimitData,
                                borderColor: 'rgba(255, 99, 132, 0.8)',
                                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                                borderWidth: 2,
                                borderDash: [10, 5],
                                pointRadius: 0,
                                fill: false,
                                tension: 0
                            });
                        }
                        
                        // Add min limit line
                        if (minLimit !== null && minLimit !== '' && !isNaN(minLimit)) {
                            const minLimitData = new Array(labels.length).fill(minLimit);
                            
                            sensorLineChart.data.datasets.push({
                                label: `Min Limit (${minLimit} ${unit})`,
                                data: minLimitData,
                                borderColor: 'rgba(255, 159, 64, 0.8)',
                                backgroundColor: 'rgba(255, 159, 64, 0.1)',
                                borderWidth: 2,
                                borderDash: [10, 5],
                                pointRadius: 0,
                                fill: false,
                                tension: 0
                            });
                        }
                    }
                }
                
                sensorLineChart.update();
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
            let showRegulationLimitsBar = false; // Track regulation limits state for bar chart

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
                    button.setAttribute('data-indicator-max', item.parameter_indicator_max || '');
                    button.setAttribute('data-indicator-min', item.parameter_indicator_min || '');
                    button.setAttribute('data-unit', item.sensor_unit || '');
                    button.innerHTML = `<i class="bi bi-bar-chart"></i> ${item.parameter_label}`;

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

                const signal = createEndpointSignal('bar');

                // Show loader
                setLoaderLoading('barChartLoader', 'Loading chart data...');
                document.getElementById('sensorBarChart').style.display = 'none';

                // Fetch chart data from API
                fetch(`/admin/bar-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}`, {
                        method: 'GET',
                        signal,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.labels || !data.values || data.labels.length === 0 || data.values.length ===
                            0) {
                            setLoaderNoData('barChartLoader', 'No data available');
                            document.getElementById('sensorBarChart').style.display = 'none';
                            return;
                        }

                        // Hide loader
                        document.getElementById('barChartLoader').style.display = 'none';
                        document.getElementById('sensorBarChart').style.display = 'block';

                        // Update chart with data validation
                        if (sensorBarChart && data.labels && data.values) {
                            // Ensure labels and values have same length
                            if (data.labels.length === data.values.length) {
                                sensorBarChart.data.labels = data.labels;
                                sensorBarChart.data.datasets[0].data = data.values;
                                sensorBarChart.data.datasets[0].label =
                                    `${data.parameter_label} - Hourly Avg (${data.unit || ''})`;
                                sensorBarChart.options.scales.y.title.text =
                                    `Average ${data.parameter_label} (${data.unit || ''})`;

                                // Verify last label shows current hour (format: 23:00)
                                const lastLabel = data.labels[data.labels.length - 1];

                                // Animation config
                                sensorBarChart.options.animation = {
                                    duration: 750,
                                    easing: 'easeInOutQuart'
                                };

                                sensorBarChart.update();
                                
                                // Update regulation limits if enabled
                                if (showRegulationLimitsBar) {
                                    updateRegulationLimitsBar();
                                }
                            } else {
                                console.error('Data mismatch: labels and values length differ');
                            }
                        }
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading bar chart data:', error);
                        setLoaderNoData('barChartLoader', 'No data available');
                        document.getElementById('sensorBarChart').style.display = 'none';
                    });
            }

            // Smooth update function for bar chart auto-refresh
            function loadBarChartDataSmooth(deviceId, parameterName) {
                if (!parameterName || !sensorBarChart) {
                    return;
                }

                const signal = createEndpointSignal('bar');

                // Fetch chart data from API without showing loader
                fetch(`/admin/bar-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}`, {
                        method: 'GET',
                        signal,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (sensorBarChart && data.labels && data.values) {
                            const currentLabels = sensorBarChart.data.labels;
                            const currentData = sensorBarChart.data.datasets[0].data;

                            // Check if we have existing data
                            if (currentLabels.length > 0 && data.labels.length === data.values.length) {
                                // Create map for efficient comparison
                                const newDataMap = {};
                                data.labels.forEach((label, index) => {
                                    newDataMap[label] = data.values[index];
                                });

                                // Check if there are new hours (labels) to add
                                const lastCurrentLabel = currentLabels[currentLabels.length - 1];
                                const newHoursToAdd = [];

                                let foundCurrentLast = false;
                                for (let i = 0; i < data.labels.length; i++) {
                                    if (foundCurrentLast) {
                                        newHoursToAdd.push({
                                            label: data.labels[i],
                                            value: data.values[i]
                                        });
                                    }
                                    if (data.labels[i] === lastCurrentLabel) {
                                        foundCurrentLast = true;
                                    }
                                }

                                // Add new hours at the end
                                if (newHoursToAdd.length > 0) {
                                    newHoursToAdd.forEach(hour => {
                                        sensorBarChart.data.labels.push(hour.label);
                                        sensorBarChart.data.datasets[0].data.push(hour.value);
                                    });

                                    // Remove old hours from the beginning (same amount as added)
                                    for (let i = 0; i < newHoursToAdd.length; i++) {
                                        sensorBarChart.data.labels.shift();
                                        sensorBarChart.data.datasets[0].data.shift();
                                    }

                                    console.log('Bar Chart Update - Added', newHoursToAdd.length,
                                        'new hours. Last label:',
                                        sensorBarChart.data.labels[sensorBarChart.data.labels.length - 1]);

                                    // Smooth animation
                                    sensorBarChart.options.animation = {
                                        duration: 1000,
                                        easing: 'easeInOutCubic'
                                    };
                                    sensorBarChart.update('active');
                                } else {
                                    // Update existing values if changed (hourly averages might change)
                                    let hasChanges = false;
                                    currentLabels.forEach((label, index) => {
                                        if (newDataMap.hasOwnProperty(label)) {
                                            const newValue = newDataMap[label];
                                            if (currentData[index] !== newValue) {
                                                currentData[index] = newValue;
                                                hasChanges = true;
                                            }
                                        }
                                    });

                                    if (hasChanges) {
                                        console.log('Bar Chart Update - Updated existing hour values');
                                        sensorBarChart.options.animation = {
                                            duration: 500,
                                            easing: 'easeInOutQuad'
                                        };
                                        sensorBarChart.update('active');
                                    }
                                }
                            } else {
                                // First load or data length mismatch - use full data
                                if (data.labels.length === data.values.length) {
                                    sensorBarChart.data.labels = data.labels;
                                    sensorBarChart.data.datasets[0].data = data.values;
                                    sensorBarChart.update();
                                }
                            }
                        }
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading bar chart data (smooth):', error);
                    });
            }

            // Toggle Regulation Limits for Bar Chart
            function toggleRegulationLimitsBar() {
                showRegulationLimitsBar = !showRegulationLimitsBar;
                const button = document.getElementById('regulationLimitsBar');
                
                if (showRegulationLimitsBar) {
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-secondary');
                } else {
                    button.classList.remove('btn-secondary');
                    button.classList.add('btn-outline-secondary');
                }
                
                updateRegulationLimitsBar();
            }

            function updateRegulationLimitsBar() {
                if (!sensorBarChart) return;
                
                // Remove existing regulation limit datasets
                sensorBarChart.data.datasets = sensorBarChart.data.datasets.filter(ds => 
                    !ds.label || (!ds.label.includes('Max Limit') && !ds.label.includes('Min Limit'))
                );
                
                if (showRegulationLimitsBar && currentSelectedBarParameter) {
                    // Get indicator range from active parameter button
                    const activeButton = document.querySelector('.bar-parameter-btn.active');
                    if (activeButton) {
                        const maxLimit = parseFloat(activeButton.getAttribute('data-indicator-max'));
                        const minLimit = parseFloat(activeButton.getAttribute('data-indicator-min'));
                        const unit = activeButton.getAttribute('data-unit') || '';
                        const labels = sensorBarChart.data.labels;
                        
                        // Add max limit line
                        if (maxLimit && !isNaN(maxLimit)) {
                            const maxLimitData = new Array(labels.length).fill(maxLimit);
                            
                            sensorBarChart.data.datasets.push({
                                label: `Max Limit (${maxLimit} ${unit})`,
                                data: maxLimitData,
                                type: 'line',
                                borderColor: 'rgba(255, 99, 132, 0.8)',
                                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                                borderWidth: 2,
                                borderDash: [10, 5],
                                pointRadius: 0,
                                fill: false,
                                tension: 0,
                                order: 0
                            });
                        }
                        
                        // Add min limit line
                        if (minLimit !== null && minLimit !== '' && !isNaN(minLimit)) {
                            const minLimitData = new Array(labels.length).fill(minLimit);
                            
                            sensorBarChart.data.datasets.push({
                                label: `Min Limit (${minLimit} ${unit})`,
                                data: minLimitData,
                                type: 'line',
                                borderColor: 'rgba(255, 159, 64, 0.8)',
                                backgroundColor: 'rgba(255, 159, 64, 0.1)',
                                borderWidth: 2,
                                borderDash: [10, 5],
                                pointRadius: 0,
                                fill: false,
                                tension: 0,
                                order: 0
                            });
                        }
                    }
                }
                
                sensorBarChart.update();
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

            function checkAndShowWindRose(requestId = null) {
                // Check if Wind Direction parameter exists
                const hasWindDirection = parameters.some(param =>
                    param.parameter_name && param.parameter_name.toLowerCase().includes('wdir')
                );

                if (hasWindDirection) {
                    document.getElementById('windRoseRow').style.display = 'block';
                    renderWindRose(requestId);
                } else {
                    document.getElementById('windRoseRow').style.display = 'none';
                }
            }

            async function renderWindRose(requestId = null) {
                if (!currentDeviceId) return;
                const signal = createEndpointSignal('windRose');

                // Show loader
                setLoaderLoading('windRoseLoader', 'Loading wind rose data...');
                document.getElementById('windRoseChart').style.display = 'none';

                try {
                    const res = await fetch(`/admin/wind-rose-data/${currentDeviceId}`, {
                        signal
                    });
                    const data = await res.json();

                    if (isStaleRequest(requestId)) {
                        return;
                    }

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

                        if (rawData.length === 0) {
                            document.getElementById('windRoseLoader').style.display = 'none';
                            document.getElementById('windRoseChart').style.display = 'block';
                            document.getElementById('windRoseChart').innerHTML =
                                '<div class="alert alert-info text-center mb-0">No data available</div>';
                            return;
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
                    if (e.name === 'AbortError') {
                        return;
                    }
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



            // ==================== HISTORICAL DATA CHART LINE ====================
            let historicalLineChart = null;
            let currentHistoricalParameter = null;

            // Helper function to format date time for historical chart
            function formatDateTimeForHistorical(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day} ${hours}:${minutes}`;
            }

            function initializeHistoricalChart() {
                const ctx = document.getElementById('historicalLineChart');
                if (ctx) {
                    historicalLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Historical Data',
                                data: [],
                                borderColor: 'rgb(255, 99, 132)',
                                backgroundColor: 'rgba(255, 99, 132, 0.3)',
                                tension: 0.4,
                                fill: 'origin',
                                pointRadius: 2,
                                pointHoverRadius: 4,
                                pointBackgroundColor: 'rgb(255, 99, 132)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 1,
                                spanGaps: false, // Jangan hubungkan gap > 5 menit
                                segment: {
                                    borderColor: ctx => ctx.p0.skip || ctx.p1.skip ?
                                        'rgba(0,0,0,0)' : undefined,
                                }
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
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: 'rgb(255, 99, 132)',
                                    borderWidth: 1
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
                                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                            borderColor: 'rgb(255, 99, 132)',
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
                                        text: 'Date & Time',
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45,
                                        autoSkip: true,
                                        maxTicksLimit: 20,
                                        font: {
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        display: true,
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                y: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Value',
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        }
                                    },
                                    beginAtZero: false,
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        display: true,
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
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
                                },
                                point: {
                                    hitRadius: 10,
                                    hoverRadius: 6
                                }
                            }
                        }
                    });
                }

                $('#historicalChartStartDate,#historicalChartEndDate').datetimepicker({
                    dateFormat: 'yy-mm-dd',
                    timeFormat: 'HH:mm',
                    showSecond: false,
                    changeMonth: true,
                    changeYear: true,
                    controlType: 'select',
                    oneLine: true,
                    showButtonPanel: true,
                    currentText: 'Now',
                    closeText: 'Done',

                    // pastikan dropdown lengkap
                    stepHour: 1,
                    stepMinute: 1,

                    hourMin: 0,
                    hourMax: 23,
                    minuteMin: 0,
                    minuteMax: 59
                });

                // Set default date range (last 2 days)
                const today = new Date();
                const twoDaysAgo = new Date();
                twoDaysAgo.setDate(today.getDate() - 2);

                $('#historicalChartStartDate').val(formatDateTimeForHistorical(twoDaysAgo));
                $('#historicalChartEndDate').val(formatDateTimeForHistorical(today));

                // Load parameters after progress bar data is available
                if (parameters.length > 0) {
                    loadHistoricalChartParameters();
                }
            }

            function loadHistoricalChartParameters() {
                const data = parameters; // Use globally stored parameters

                if (!data || data.length === 0) {
                    console.error('No parameters available for historical chart');
                    return;
                }

                const buttonContainer = document.getElementById('historicalParameterButtons');

                // Clear existing buttons and reset chart
                buttonContainer.innerHTML = '';

                // Clear chart data to show it's being refreshed
                if (historicalLineChart) {
                    historicalLineChart.data.labels = [];
                    historicalLineChart.data.datasets[0].data = [];
                    historicalLineChart.update();
                }

                data.forEach((item, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-outline-danger btn-sm historical-parameter-btn';
                    button.setAttribute('data-parameter', item.parameter_name);
                    button.setAttribute('data-unit', item.sensor_unit || '');
                    button.innerHTML = `<i class="bi bi-graph-up"></i> ${item.parameter_label}`;

                    // Add click event
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons
                        document.querySelectorAll('.historical-parameter-btn').forEach(btn => {
                            btn.classList.remove('active');
                            btn.classList.remove('btn-danger');
                            btn.classList.add('btn-outline-danger');
                        });

                        // Add active class to clicked button
                        this.classList.remove('btn-outline-danger');
                        this.classList.add('btn-danger');
                        this.classList.add('active');

                        // Update current selected parameter
                        currentHistoricalParameter = this.getAttribute('data-parameter');

                        // Load chart data
                        loadHistoricalChartData(currentDeviceId, currentHistoricalParameter);
                    });

                    buttonContainer.appendChild(button);

                    // Auto-select first parameter and load its data
                    if (index === 0) {
                        button.classList.remove('btn-outline-danger');
                        button.classList.add('btn-danger');
                        button.classList.add('active');
                        currentHistoricalParameter = item.parameter_name;

                        // Load data for the first parameter with current device
                        loadHistoricalChartData(currentDeviceId, item.parameter_name);
                    }
                });
            }

            function loadHistoricalChartData(deviceId, parameterName) {
                if (!parameterName) {
                    console.log('Historical Chart: No parameter selected');
                    return;
                }

                const signal = createEndpointSignal('historical');

                const startDate = $('#historicalChartStartDate').val();
                const endDate = $('#historicalChartEndDate').val();

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }

                if (new Date(startDate) > new Date(endDate)) {
                    alert('Start date must be before end date');
                    return;
                }

                console.log('Loading Historical Chart:', {
                    deviceId: deviceId,
                    parameter: parameterName,
                    startDate: startDate,
                    endDate: endDate
                });

                // Show loader
                setLoaderLoading('historicalChartLoader', 'Loading chart data...');
                document.getElementById('historicalLineChart').style.display = 'none';

                // Fetch chart data from API
                fetch(`/admin/historical-chart-data/${deviceId}?parameter=${encodeURIComponent(parameterName)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`, {
                        method: 'GET',
                    signal,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.labels || !data.values || data.labels.length === 0 || data.values.length ===
                            0) {
                            setLoaderNoData('historicalChartLoader', 'No data available');
                            document.getElementById('historicalLineChart').style.display = 'none';
                            return;
                        }

                        // Hide loader
                        document.getElementById('historicalChartLoader').style.display = 'none';
                        document.getElementById('historicalLineChart').style.display = 'block';

                        // Update chart
                        if (historicalLineChart && data.labels && data.values) {
                            historicalLineChart.data.labels = data.labels;
                            historicalLineChart.data.datasets[0].data = data.values;
                            historicalLineChart.data.datasets[0].label =
                                `${data.parameter_label} (${data.unit || ''})`;
                            historicalLineChart.options.scales.y.title.text =
                                `${data.parameter_label} (${data.unit || ''})`;

                            // Jangan hubungkan gap lebih dari 5 menit
                            historicalLineChart.data.datasets[0].spanGaps = false;

                            // Animation config
                            historicalLineChart.options.animation = {
                                duration: 750,
                                easing: 'easeInOutQuart'
                            };

                            historicalLineChart.update();
                        }
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading historical chart data:', error);
                        setLoaderNoData('historicalChartLoader', 'No data available');
                        document.getElementById('historicalLineChart').style.display = 'none';
                    });
            }

            // Load Historical Chart Button
            document.getElementById('loadHistoricalChart').addEventListener('click', function() {
                if (currentHistoricalParameter && currentDeviceId) {
                    loadHistoricalChartData(currentDeviceId, currentHistoricalParameter);
                } else {
                    alert('Please select a parameter first');
                }
            });

            // ==================== END HISTORICAL DATA CHART LINE ====================



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
                fetch(`/admin/progress-bar/${currentDeviceId}`, {
                        method: 'GET',
                    signal: createEndpointSignal('reference'),
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
                        renderReferenceTable(data, modalBody);
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading reference data:', error);
                        modalBody.innerHTML =
                            '<div class="alert alert-danger">Failed to load reference data.</div>';
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
                fetch(`/admin/progress-bar/${currentDeviceId}`, {
                        method: 'GET',
                    signal: createEndpointSignal('referenceBar'),
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
                        renderReferenceTable(data, modalBody);
                    })
                    .catch(error => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.error('Error loading reference data:', error);
                        modalBody.innerHTML =
                            '<div class="alert alert-danger">Failed to load reference data.</div>';
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
                    const minValue = item.parameter_indicator_min !== null ? item.parameter_indicator_min :
                        '-';
                    const maxValue = item.parameter_indicator_max !== null ? item.parameter_indicator_max :
                        '-';
                    const unit = item.sensor_unit || '-';
                    const status = item.parameter_indicator_max ?
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



            // ==================== Button Regulation Limits ====================
            document.getElementById('regulationLimitsChart')?.addEventListener('click', function() {
                toggleRegulationLimitsChart();
            });

            document.getElementById('regulationLimitsBar')?.addEventListener('click', function() {
                toggleRegulationLimitsBar();
            });



            // ==================== INITIALIZE ALL ====================


            // Initialize progress bars first, then load charts after data is available
            initializeLatestDataProgressBars()
                .then(() => {
                    // After progress bar data is loaded, initialize charts
                    initializeMap();
                    initializeLineChart();
                    initializeBarChart();
                    initializeHistoricalChart();

                    // Load parameters for both charts using the cached data
                    if (currentDeviceId && parameters.length > 0) {
                        loadLineChartParameters();
                        loadBarChartParameters();
                        loadHistoricalChartParameters();

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
                    if (isSwitchingDevice) {
                        return;
                    }

                    const requestId = activeDeviceRequestId;

                    // Refresh map with fresh device status
                    loadMapData(currentDeviceId, requestId);

                    // Refresh progress bars
                    loadProgressBarData(currentDeviceId, requestId);

                    // Refresh line chart if parameter is selected (smooth update)
                    if (currentSelectedParameter) {
                        loadLineChartDataSmooth(currentDeviceId, currentSelectedParameter);
                    }

                    // Refresh bar chart if parameter is selected (smooth update)
                    if (currentSelectedBarParameter) {
                        loadBarChartDataSmooth(currentDeviceId, currentSelectedBarParameter);
                    }

                    // Refresh wind rose if visible
                    const windRoseRow = document.getElementById('windRoseRow');
                    if (windRoseRow && windRoseRow.style.display !== 'none') {
                        renderWindRose(requestId);
                    }
                }
            }, 60000);
        });
    </script>
@endsection
