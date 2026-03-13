@extends('layout.admin')
@section('title', 'Manage Sensors')

<style>
    /* --- Styles --- */
    .device-group-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        padding: 12px 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 8px 8px 0 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .device-group-header:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        padding-left: 20px;
    }

    .device-group-header i.bi-chevron-down {
        transition: transform 0.3s ease;
    }

    .device-group-header.collapsed i.bi-chevron-down {
        transform: rotate(-90deg);
    }

    .sensor-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .device-sensors {
        background-color: #fff;
        border-top: none;
    }

    .sensor-info {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .sensor-info:last-child {
        border-bottom: none;
    }

    .sensor-info:hover {
        background-color: #f8f9fa;
    }

    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .info-value {
        color: #212529;
        font-size: 0.95rem;
        margin-bottom: 10px;
        word-break: break-word;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-active {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-inactive {
        background-color: #f8d7da;
        color: #842029;
    }

    .device-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: auto;
    }

    .sensors-container {
        max-height: 70vh;
        overflow-y: auto;
        padding-right: 5px;
    }

    .sensors-container::-webkit-scrollbar {
        width: 6px;
    }

    .sensors-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .sensors-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .sensors-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

@section('content')<div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Devices</h4>
        </div>

        <div class="card-body bg-light pt-0 mt-2">

            <div class=" w-md-auto text-end py-2">

                <!-- Action Buttons -->
                <button id="btnAdd" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Device</button>

            </div>

            <!-- Toolbar -->
            <div class="d-flex justify-content-end align-items-center py-2 gap-2">
                <!-- Search Input -->
                <div class="input-group" style="max-width: 300px;">

                    <input type="text" id="searchInput" class="form-control bg-light border-start-0"
                        placeholder="Search sensors...">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                </div>

                <button id="expandAll" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrows-expand"></i> Expand
                    All</button>
                <button id="collapseAll" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrows-collapse"></i>
                    Collapse All</button>
            </div>

            <!-- Container -->
            <div class="sensors-container" id="sensorsContainer">
                <!-- Content Loaded via JS -->
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="sensorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> <span id="modalTitle">Add Sensor</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sensorForm" autocomplete="off">
                        <input type="hidden" id="sensorId" name="sensorId">
                        <input type="hidden" id="sensor_unit" name="sensor_unit">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="device_id" class="form-label fw-bold">Device ID</label>
                                    <select class="form-select select2" id="device_id" name="device_id" required>
                                        <option value="">Select Device</option>
                                        @foreach ($devices as $device)
                                            <option value="{{ $device->device_id }}">{{ $device->device_id }}
                                                ({{ $device->device_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="sensor_number" class="form-label fw-bold">Sensor No.</label>
                                        <input type="text" class="form-control" id="sensor_number" name="sensor_number"
                                            placeholder="e.g. S001" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="sensor_name" class="form-label fw-bold">Sensor Name</label>
                                        <input type="text" class="form-control" id="sensor_name" name="sensor_name"
                                            placeholder="e.g. AT500" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="parameter_name" class="form-label fw-bold">Parameter</label>
                                    <select class="form-select select2" id="parameter_name" name="parameter_name" required>
                                        <option value="">Select Parameter</option>
                                        @foreach ($parameters as $parameter)
                                            <option value="{{ $parameter->parameter_name }}"
                                                data-unit="{{ $parameter->parameter_unit }}">
                                                {{ $parameter->parameter_label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="parameter_number" class="form-label">Param No.</label>
                                        <input type="text" class="form-control" id="parameter_number"
                                            name="parameter_number" placeholder="e.g. P001">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="parameter_indicator_alert" class="form-label fw-bold">Alert Value</label>
                                    <input type="number" class="form-control" id="parameter_indicator_alert"
                                        name="parameter_indicator_alert" placeholder="Threshold alert">
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="maintenance_date" class="form-label">Maintenance</label>
                                        <input type="text" class="form-control" id="maintenance_date"
                                            placeholder="e.g. 2024-12-31" name="maintenance_date">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="calibration_date" class="form-label">Calibration</label>
                                        <input type="text" class="form-control" id="calibration_date"
                                            placeholder="e.g. 2024-12-31" name="calibration_date">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="5" placeholder="Additional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> <i class="bi bi-x-lg"></i>
                        Close</button>
                    <button type="button" class="btn btn-primary" id="saveBtn"><i class="bi bi-check-lg"></i> Save
                        Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        window.csrfToken = '{{ csrf_token() }}';

        document.addEventListener('DOMContentLoaded', function() {
            let sensorsData = [];

            const sensorsContainer = document.getElementById('sensorsContainer');
            const sensorModal = new bootstrap.Modal(document.getElementById('sensorModal'));

            // --- 1. Load & Render Logic ---

            function loadSensors() {
                fetch('/admin/sensors')
                    .then(r => r.json())
                    .then(data => {
                        sensorsData = data;
                        renderSensors(sensorsData, false); // Default: collapsed
                    })
                    .catch(err => Swal.fire('Error', 'Failed to load sensors', 'error'));
            }

            function filterSensors(query) {
                const lowerQuery = query.toLowerCase();
                const filtered = sensorsData.filter(sensor =>
                    (sensor.sensor_name && sensor.sensor_name.toLowerCase().includes(lowerQuery)) ||
                    (sensor.sensor_number && sensor.sensor_number.toLowerCase().includes(lowerQuery)) ||
                    (sensor.device_id && sensor.device_id.toLowerCase().includes(lowerQuery)) ||
                    (sensor.device && sensor.device.device_name.toLowerCase().includes(lowerQuery)) ||
                    (sensor.parameter_name && sensor.parameter_name.toLowerCase().includes(lowerQuery))
                );

                renderSensors(filtered, query.length > 0);
            }

            function renderSensors(sensors, forceExpand = false) {
                sensorsContainer.innerHTML = '';

                if (sensors.length === 0) {
                    sensorsContainer.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No sensors found.</p>
                    </div>`;
                    return;
                }

                const grouped = sensors.reduce((acc, sensor) => {
                    if (!acc[sensor.device_id]) acc[sensor.device_id] = [];
                    acc[sensor.device_id].push(sensor);
                    return acc;
                }, {});

                Object.keys(grouped).sort().forEach(deviceId => {
                    const deviceSensors = grouped[deviceId];
                    sensorsContainer.appendChild(createDeviceCard(deviceId, deviceSensors, forceExpand));
                });
            }

            function createDeviceCard(deviceId, sensors, forceExpand) {
                const card = document.createElement('div');
                card.className = 'sensor-card';
                const deviceName = sensors[0]?.device?.device_name || 'Unknown Device';

                const isCollapsed = !forceExpand;
                const displayStyle = isCollapsed ? 'display: none;' : 'display: block;';
                const headerClass = isCollapsed ? 'collapsed' : '';

                card.innerHTML = `
                <div class="device-group-header ${headerClass}" onclick="toggleDevice('${deviceId}')">
                    <i class="bi bi-chevron-down"></i>
                    <i class="bi bi-hdd-rack"></i>
                    <strong>${deviceName}  (${deviceId}) </strong>
                    <span class="device-badge">${sensors.length} Sensor${sensors.length > 1 ? 's' : ''}</span>
                </div>
                <div class="device-sensors" id="device-${deviceId}" style="${displayStyle}">
                    ${sensors.map(sensor => createSensorRow(sensor)).join('')}
                </div>
            `;
                return card;
            }

            function createSensorRow(sensor) {
                const statusClass = sensor.status === 'active' ? 'status-active' : 'status-inactive';
                return `
                <div class="sensor-info">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="bi bi-broadcast-pin me-1"></i> ${sensor.sensor_name}
                            </h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="status-badge ${statusClass}">${sensor.status}</span>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-2">
                            <div class="info-label">Number</div>
                            <div class="info-value text-muted">${sensor.sensor_number || '-'}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Parameter</div>
                            <div class="info-value">${sensor.parameter_name} <small class="text-muted">(${sensor.parameter_number || '-'})</small></div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Alert</div>
                            <div class="info-value">${sensor.parameter_indicator_alert || '-'}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Maintenance</div>
                            <div class="info-value">${sensor.maintenance_date || '-'}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Calibration</div>
                            <div class="info-value">${sensor.calibration_date || '-'}</div>
                        </div>
                        <div class="col-md-2 text-end">
                             <div class="info-label">Action</div>
                             <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info btn-view" title="View" data-id="${sensor.id}"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-outline-primary btn-edit" title="Edit" data-id="${sensor.id}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger btn-delete" title="Delete" data-id="${sensor.id}"><i class="bi bi-trash"></i></button>
                             </div>
                        </div>
                    </div>
                </div>
            `;
            }

            window.toggleDevice = function(deviceId) {
                const header = document.querySelector(`.device-group-header[onclick*="${deviceId}"]`);
                const content = document.getElementById(`device-${deviceId}`);

                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    header.classList.remove('collapsed');
                } else {
                    content.style.display = 'none';
                    header.classList.add('collapsed');
                }
            };

            // --- 2. Interaction Logic ---

            $('#searchInput').on('input', function() {
                const query = $(this).val();
                filterSensors(query);
            });

            $('#expandAll').on('click', function() {
                $('.device-sensors').slideDown(200);
                $('.device-group-header').removeClass('collapsed');
            });

            $('#collapseAll').on('click', function() {
                $('.device-sensors').slideUp(200);
                $('.device-group-header').addClass('collapsed');
            });

            // --- 3. Helper Functions ---

            // Set Form Mode (Edit/View/Normal)
            function setFormMode(mode) {
                const inputs = $('#sensorForm input, #sensorForm select, #sensorForm textarea');

                if (mode === 'view') {
                    inputs.prop('disabled', true);
                    $('#saveBtn').hide();
                    $('#modalTitle').text('View Sensor Details');
                    // Disable Select2
                    $('.select2').prop("disabled", true).trigger("change");
                } else {
                    inputs.prop('disabled', false);
                    $('#saveBtn').show();
                    $('#modalTitle').text(mode === 'edit' ? 'Edit Sensor' : 'Add New Sensor');
                    // Enable Select2
                    $('.select2').prop("disabled", false).trigger("change");
                }
            }

            // Fetch & Populate Data
            function fetchAndFill(id, callback) {
                fetch(`/admin/sensors/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        $('#sensorId').val(data.id);
                        $('#device_id').val(data.device_id).trigger('change');
                        $('#sensor_number').val(data.sensor_number);
                        $('#sensor_name').val(data.sensor_name);
                        $('#parameter_number').val(data.parameter_number);
                        $('#parameter_name').val(data.parameter_name).trigger('change');
                        $('#parameter_indicator_alert').val(data.parameter_indicator_alert);
                        $('#sensor_unit').val(data.sensor_unit);
                        $('#maintenance_date').val(data.maintenance_date);
                        $('#calibration_date').val(data.calibration_date);
                        $('#status').val(data.status);
                        $('#notes').val(data.notes);
                        if (callback) callback(data);
                    })
                    .catch(err => Swal.fire('Error', 'Failed to load data', 'error'));
            }

            function clearForm() {
                $('#sensorForm')[0].reset();
                $('#sensorId').val('');
                $('.select2').val(null).trigger('change');
                setFormMode('add'); // Reset to add mode (enabled)
            }

            // --- 4. CRUD Actions ---

            // Add
            $('#btnAdd').on('click', function() {
                clearForm();
                sensorModal.show();
            });

            // View
            $(document).on('click', '.btn-view', function() {
                const id = $(this).data('id');
                fetchAndFill(id, function() {
                    setFormMode('view');
                    sensorModal.show();
                });
            });

            // Edit
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                fetchAndFill(id, function() {
                    setFormMode('edit');
                    sensorModal.show();
                });
            });

            // Delete
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Data will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/admin/sensors/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken,
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(data => {
                                Swal.fire('Deleted!', data.message, 'success');
                                loadSensors();
                            })
                            .catch(err => Swal.fire('Error', 'Failed to delete', 'error'));
                    }
                });
            });
            // Save
            $('#saveBtn').on('click', function() {
                const payload = {
                    device_id: $('#device_id').val(),
                    sensor_number: $('#sensor_number').val(),
                    sensor_name: $('#sensor_name').val(),
                    parameter_number: $('#parameter_number').val(),
                    parameter_name: $('#parameter_name').val(),
                    parameter_indicator_alert: $('#parameter_indicator_alert').val(),
                    sensor_unit: $('#sensor_unit').val(),
                    maintenance_date: $('#maintenance_date').val(),
                    calibration_date: $('#calibration_date').val(),
                    status: $('#status').val(),
                    notes: $('#notes').val()
                };

                if (!payload.device_id || !payload.sensor_name || !payload.parameter_name) {
                    Swal.fire('Validation Error', 'Please fill in the required fields.', 'warning');
                    return;
                }

                const id = $('#sensorId').val();
                const url = id ? `/admin/sensors/${id}` : '/admin/sensors';
                const method = id ? 'PUT' : 'POST';

                // Confirmation dialog
                Swal.fire({
                    title: 'Confirmation',
                    text: 'Are you sure you want to save this data?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    // Show loading
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(async res => {
                            const body = await res.json();
                            if (!res.ok) throw new Error(body.message ||
                                'An error occurred');
                            return body;
                        })
                        .then(resp => {
                            Swal.fire('Success', resp.message || 'Data saved successfully',
                                'success');
                            sensorModal.hide();
                            loadSensors();
                        })
                        .catch(err => {
                            Swal.fire('Error', err.message, 'error');
                        });
                });
            });


            // date picker
            $("#maintenance_date, #calibration_date").datepicker({
                dateFormat: 'yy-mm-dd'
            });

            // --- 5. Select2 & Initialization ---

            $('#parameter_name').on('change', function() {
                const unit = $(this).find('option:selected').data('unit');
                $('#sensor_unit').val(unit || '');
            });

            function initSelect2() {
                $('.select2').select2({
                    dropdownParent: $('#sensorModal'),
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            initSelect2();

            $('#sensorModal').on('shown.bs.modal', function() {
                // Re-init to fix z-index
                $('#device_id').select2('destroy').select2({
                    dropdownParent: $('#sensorModal'),
                    theme: 'bootstrap-5',
                    width: '100%'
                });
                $('#parameter_name').select2('destroy').select2({
                    dropdownParent: $('#sensorModal'),
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            });

            // Reset form mode when modal closes to ensure next open is fresh
            $('#sensorModal').on('hidden.bs.modal', function() {
                setFormMode('add');
            });

            // Initial Load
            loadSensors();
        });
    </script>
@endsection
