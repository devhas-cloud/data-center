@extends('layout.user')
@section('title', 'Device Information')
@section('desktop-selector')
    @include('user.componen.desktop-selector')
@endsection

@section('mobile-selector')
    @include('user.componen.mobile-selector')
@endsection

@section('content')
<div class="container-fluid p-3">
    <div class="row">
        <div class="col-12">
            <!-- Device Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #DAD7D7">
                    <h4 class="mb-0">Device Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 col-md-7">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                            <td style="width: 40%;" class="fw-bold bg-light">Device ID</td>
                                            <td id="device-id" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">Device Status</td>
                                            <td id="device-status" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">Last Update Data</td>
                                            <td id="last-update" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">Device Category</td>
                                            <td id="device-category" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">District / City</td>
                                            <td id="district-city" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">Latitude</td>
                                            <td id="latitude" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">Longitude</td>
                                            <td id="longitude" class="text-muted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold bg-light">Date of Installation</td>
                                            <td id="date-installation" class="text-muted">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-5 d-flex align-items-center justify-content-center">
                            <div class="text-center w-100">
                                <img id="device-image" src="" alt="Device Image" class="img-fluid rounded shadow-sm" style="max-height: 300px; max-width: 100%; display: none;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Configuration Card -->
            <div class="card shadow-sm">
                 <div class="card-header" style="background-color: #DAD7D7">
                    <h4 class="mb-0">Device Configuration</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Parameter</th>
                                    <th>Sensor Name</th>
                                    <th>Serial Number</th>
                                    <th>Unit</th>
                                    <th>Maintenance Date</th>
                                    <th>Calibration Date</th>
                                </tr>
                            </thead>
                            <tbody id="config-parameters">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Loading device configuration...</td>
                                </tr>
                                <!-- Configuration parameters will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- System Logs Card  -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: #DAD7D7">
                    <h4 class="mb-0">System Logs</h4>
                </div>
                <div class="card-body">
                   <div class="table-responsive">
                        <table class="table table-striped table-hover" style="width:100%" id="deviceSyslogTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                               <!-- javascript data table -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Syslog Detail Modal -->
    <div class="modal fade" id="syslogDetailModal" tabindex="-1" aria-labelledby="syslogDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="syslogDetailModalLabel"><i class="bi bi-journal-text"></i> Syslog Detail</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="syslogDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading syslog details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // ==================== INITIALIZATION ====================
        let currentSelectedDevice = null;
        let currentDeviceId = null;
        let currentDevice = null;

        // Get device data from Laravel
        const deviceCategories = @json($deviceCategories);

        // DOM Elements
        const deviceSelector = document.getElementById('deviceSelector');
        const deviceSelectorToggle = document.getElementById('deviceSelectorToggle');
        const deviceOptions = document.querySelectorAll('.device-option');
        const deviceNameElements = document.querySelectorAll('.device-name');
        const mobileDeviceSelector = document.getElementById('mobileDeviceSelector');
        const mobileDeviceToggle = document.getElementById('mobileDeviceToggle');
        const configParametersBody = document.getElementById('config-parameters');
        let syslogTable = null;

        // ==================== DEVICE SELECTOR FUNCTIONS ====================

        /**
         * Initialize device selector functionality
         */
        function initDeviceSelector() {
            // Get initial selected device
            const initialActiveOption = document.querySelector('.device-option.active');
            if (initialActiveOption) {
                currentSelectedDevice = initialActiveOption.getAttribute('data-value');
            }

            // Setup desktop device selector toggle
            if (deviceSelectorToggle) {
                deviceSelectorToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    deviceSelector.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (deviceSelector && !deviceSelector.contains(event.target) &&
                        deviceSelector.classList.contains('show')) {
                        deviceSelector.classList.remove('show');
                    }
                });
            }

            // Setup mobile device selector toggle
            if (mobileDeviceToggle) {
                mobileDeviceToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileDeviceSelector.classList.toggle('show');
                });
            }

            // Handle device selection change (Desktop & Mobile)
            deviceOptions.forEach(option => {
                option.addEventListener('click', function() {
                    handleDeviceSelection(this);
                });
            });
        }

        /**
         * Handle device selection when user clicks on a device option
         * @param {HTMLElement} selectedOption - The selected device option element
         */
        function handleDeviceSelection(selectedOption) {
            const selectedDevice = selectedOption.getAttribute('data-value');

            // Update active state
            deviceOptions.forEach(opt => opt.classList.remove('active'));
            selectedOption.classList.add('active');

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

            // Fetch and display device info
            fetchDeviceInfo(currentDeviceId);
        }

        // ==================== DEVICE DATA FUNCTIONS ====================

        /**
         * Initialize device data
         */
        function initDeviceData() {
            // Get initial device data
            if (deviceCategories.length > 0 && deviceCategories[0].devices.length > 0) {
                currentDevice = deviceCategories[0].devices[0];
                currentDeviceId = currentDevice.device_id;
            }

            // Fetch and display device info
            if (currentDeviceId) {
                fetchDeviceInfo(currentDeviceId);
            }
        }

        /**
         * Fetch device information from API
         * @param {string} deviceId - The ID of the device to fetch
         */
        function fetchDeviceInfo(deviceId) {
            // Show loading state
            configParametersBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Loading device information...
                    </td>
                </tr>
            `;

            fetch(`/user/device-info/${deviceId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    updateDeviceDetails(data.device);
                    updateConfigurationParameters(data.configuration);
                    updateSyslogTable(data.syslogs || []);
                })
                .catch(error => {
                    console.error('Error fetching device info:', error);
                    showErrorState('Failed to load device information. Please try again later.');
                });
        }

        /**
         * Update device details in the UI
         * @param {Object} device - The device data object
         */
        function updateDeviceDetails(device) {
            document.getElementById('device-id').textContent = device.device_id || '-';
            document.getElementById('device-status').textContent = device.device_status || '-';
            document.getElementById('last-update').textContent = device.device_last_update_data || '-';
            document.getElementById('device-category').textContent = device.device_category || '-';
            document.getElementById('district-city').textContent = device.device_district || '-';
            document.getElementById('latitude').textContent = device.device_latitude || '-';
            document.getElementById('longitude').textContent = device.device_longitude || '-';
            document.getElementById('date-installation').textContent = device.device_date_installation || '-';
            const deviceImage = document.getElementById('device-image');
            if (device.device_linked_img) {
                deviceImage.src = `/storage/${device.device_linked_img}`;
                deviceImage.style.display = 'block';
            } else {
                deviceImage.src = '';
                deviceImage.style.display = 'none';
            }
        }

        /**
         * Update configuration parameters table
         * @param {Array} configuration - Array of configuration parameters
         */
        function updateConfigurationParameters(configuration) {
            configParametersBody.innerHTML = '';

            if (!configuration || configuration.length === 0) {
                configParametersBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted">No configuration data available</td>
                    </tr>
                `;
                return;
            }

            configuration.forEach(param => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${param.parameter || '-'}</td>
                    <td>${param.sensor_name || '-'}</td>
                    <td>${param.device_name || '-'}</td>
                    <td>${param.unit || '-'}</td>
                    <td>${param.maintenance_date || '-'}</td>
                    <td>${param.calibration_date || '-'}</td>
                `;
                configParametersBody.appendChild(row);
            });
        }

        /**
         * Show error state in the configuration table
         * @param {string} message - Error message to display
         */
        function showErrorState(message) {
            configParametersBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${message}
                    </td>
                </tr>
            `;
        }

        /**
         * Update syslog table with data
         * @param {Array} syslogs - Array of syslog records
         */
        function updateSyslogTable(syslogs) {
            // Destroy existing DataTable if it exists
            if (syslogTable) {
                syslogTable.destroy();
            }

            // Initialize DataTable with syslog data
            syslogTable = $('#deviceSyslogTable').DataTable({
                data: syslogs,
                columns: [
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'created_date',
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'category',
                        render: function(data) {
                            const badges = {
                                'maintenance': 'bg-warning',
                                'calibration': 'bg-info',
                                'installation': 'bg-success'
                            };
                            const badgeClass = badges[data] || 'bg-secondary';
                            return `<span class="badge ${badgeClass}">${data.toUpperCase()  || '-'}</span>`;
                        }
                    },
                    {
                        data: 'note',
                        render: function(data) {
                            if (!data) return '-';
                            return data.length > 50 ? data.substring(0, 50) + '...' : data;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const viewBtn = `<button class="btn btn-sm btn-info btn-view-syslog me-1" data-id="${row.id}"><i class="bi bi-eye"></i> View</button>`;
                            const downloadBtn = row.linked_file 
                                ? `<a href="/storage/${row.linked_file}" download class="btn btn-sm btn-success"><i class="bi bi-download"></i> Download</a>`
                                : '<span class="text-muted">No file</span>';
                            return viewBtn + downloadBtn;
                        }
                    }
                ],
                order: [[1, 'desc']],
                pageLength: 10,
                language: {
                    emptyTable: "No system logs available for this device"
                }
            });

            // Add event handler for view button
            $('#deviceSyslogTable').off('click', '.btn-view-syslog').on('click', '.btn-view-syslog', function() {
                const syslogId = $(this).data('id');
                showSyslogDetail(syslogId);
            });
        }

        /**
         * Show syslog detail in modal
         * @param {number} syslogId - The ID of the syslog to view
         */
        function showSyslogDetail(syslogId) {
            const modal = new bootstrap.Modal(document.getElementById('syslogDetailModal'));
            const modalContent = document.getElementById('syslogDetailContent');
            
            // Show loading state
            modalContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading syslog details...</p>
                </div>
            `;
            
            modal.show();
            
            // Fetch syslog detail
            fetch(`/user/syslog-detail/${syslogId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch syslog detail');
                    }
                    return response.json();
                })
                .then(data => {
                    displaySyslogDetail(data);
                })
                .catch(error => {
                    console.error('Error fetching syslog detail:', error);
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Failed to load syslog details. Please try again.
                        </div>
                    `;
                });
        }

        /**
         * Display syslog detail in modal
         * @param {Object} data - Syslog detail data
         */
        function displaySyslogDetail(data) {
            const modalContent = document.getElementById('syslogDetailContent');
            
            const badges = {
                'maintenance': 'bg-warning',
                'calibration': 'bg-info',
                'installation': 'bg-success'
            };
            const badgeClass = badges[data.category] || 'bg-secondary';
            
            let detailsHtml = '';
            if (data.details && data.details.length > 0) {
                detailsHtml = `
                    <h6 class="mt-3 mb-2">Details:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Parameter</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.details.map((detail, index) => `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td><strong>${detail.parameter_name || '-'}</strong></td>
                                        <td>${detail.description || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Device Name:</strong> ${data.device_name || '-'}</p>
                        <p><strong>Date:</strong> ${data.created_date || '-'}</p>
                        <p><strong>Category:</strong> <span class="badge ${badgeClass}">${data.category || '-'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created By:</strong> ${data.user_name || '-'}</p>
                        <p><strong>Created At:</strong> ${data.created_at || '-'}</p>
                        <p><strong>File:</strong> ${data.linked_file ? `<a href="/storage/${data.linked_file}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-file-pdf"></i> View PDF</a>` : '<span class="text-muted">No file</span>'}</p>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Note:</h6>
                    <p class="bg-light p-3 rounded">${data.note || '-'}</p>
                </div>
                ${detailsHtml}
            `;
        }

        // ==================== INITIALIZATION ====================
        initDeviceSelector();
        initDeviceData();
    });
</script>
@endsection
