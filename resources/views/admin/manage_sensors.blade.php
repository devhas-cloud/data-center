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

    <!-- ===== ADD SENSOR MODAL (multi-row) ===== -->
    <div class="modal fade" id="addSensorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Sensors</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column">

                    <!-- Device ID selector -->
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold"><i class="bi bi-hdd-rack me-1"></i>Device ID <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_device_id">
                                <option value="">— Select Device —</option>
                                @foreach ($devices as $device)
                                    <option value="{{ $device->device_id }}">{{ $device->device_id }} ({{ $device->device_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7 text-end">
                            <small class="text-muted me-2"><i class="bi bi-info-circle"></i> Fill in sensor rows below. Rows with empty Sensor Name & Parameter will be skipped.</small>
                        </div>
                    </div>

                    <!-- Sensor rows table -->
                    <div class="table-responsive flex-grow-1" style="overflow-x: auto;">
                        <table class="table table-bordered table-sm align-middle" id="addSensorTable" style="min-width: 1200px;">
                            <thead class="table-primary sticky-top">
                                <tr>
                                    <th style="width:40px;" class="text-center">#</th>
                                    <th style="min-width:110px;">Sensor No. <span class="text-danger">*</span></th>
                                    <th style="min-width:130px;">Sensor Name <span class="text-danger">*</span></th>
                                    <th style="min-width:160px;">Parameter <span class="text-danger">*</span></th>
                                    <th style="min-width:110px;">Param No.</th>
                                    <th style="min-width:110px;">Status</th>
                                    <th style="min-width:110px;">Alert Value</th>
                                    <th style="min-width:130px;">Maintenance</th>
                                    <th style="min-width:130px;">Calibration</th>
                                    <th style="min-width:180px;">Notes</th>
                                    <th style="width:48px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="addSensorRows">
                                <!-- rows injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-success btn-sm" id="btnAddRow">
                            <i class="bi bi-plus-lg"></i> Add Row
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancel</button>
                    <button type="button" class="btn btn-success" id="btnSaveAll"><i class="bi bi-check-lg"></i> Save All Sensors</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== EDIT SENSOR MODAL (single row) ===== -->
    <div class="modal fade" id="editSensorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> <span id="editModalTitle">Edit Sensor</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSensorForm" autocomplete="off">
                        <input type="hidden" id="editSensorId">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Device ID</label>
                                <select class="form-select select2-edit" id="edit_device_id" name="device_id" required>
                                    <option value="">Select Device</option>
                                    @foreach ($devices as $device)
                                        <option value="{{ $device->device_id }}">{{ $device->device_id }} ({{ $device->device_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Sensor No.</label>
                                <input type="text" class="form-control" id="edit_sensor_number" placeholder="e.g. S001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Sensor Name</label>
                                <input type="text" class="form-control" id="edit_sensor_name" placeholder="e.g. AT500" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-select" id="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Parameter</label>
                                <select class="form-select select2-edit" id="edit_parameter_name" name="parameter_name" required>
                                    <option value="">Select Parameter</option>
                                    @foreach ($parameters as $parameter)
                                        <option value="{{ $parameter->parameter_name }}" data-unit="{{ $parameter->parameter_unit }}">
                                            {{ $parameter->parameter_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Param No.</label>
                                <input type="text" class="form-control" id="edit_parameter_number" placeholder="e.g. P001">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Alert Value</label>
                                <input type="number" class="form-control" id="edit_parameter_indicator_alert" placeholder="Threshold">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Maintenance Date</label>
                                <input type="text" class="form-control edit-datepicker" id="edit_maintenance_date" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Calibration Date</label>
                                <input type="text" class="form-control edit-datepicker" id="edit_calibration_date" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" id="edit_sensor_unit" placeholder="Auto-filled" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="edit_notes" rows="3" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEdit"><i class="bi bi-check-lg"></i> Save Changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        window.csrfToken = '{{ csrf_token() }}';

        // Parameters data from server for dynamic row generation
        const parametersData = @json($parameters);

        document.addEventListener('DOMContentLoaded', function () {
            let sensorsData = [];

            const sensorsContainer  = document.getElementById('sensorsContainer');
            const addSensorModal    = new bootstrap.Modal(document.getElementById('addSensorModal'));
            const editSensorModal   = new bootstrap.Modal(document.getElementById('editSensorModal'));

            // ─────────────────────────────────────────
            // 1.  LOAD & RENDER
            // ─────────────────────────────────────────
            function loadSensors() {
                fetch('/admin/sensors')
                    .then(r => r.json())
                    .then(data => {
                        sensorsData = data;
                        renderSensors(sensorsData, false);
                    })
                    .catch(() => Swal.fire('Error', 'Failed to load sensors', 'error'));
            }

            function filterSensors(query) {
                const q = query.toLowerCase();
                const filtered = sensorsData.filter(s =>
                    (s.sensor_name    && s.sensor_name.toLowerCase().includes(q)) ||
                    (s.sensor_number  && s.sensor_number.toLowerCase().includes(q)) ||
                    (s.device_id      && s.device_id.toLowerCase().includes(q)) ||
                    (s.device         && s.device.device_name.toLowerCase().includes(q)) ||
                    (s.parameter_name && s.parameter_name.toLowerCase().includes(q))
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
                const grouped = sensors.reduce((acc, s) => {
                    if (!acc[s.device_id]) acc[s.device_id] = [];
                    acc[s.device_id].push(s);
                    return acc;
                }, {});
                Object.keys(grouped).sort().forEach(deviceId => {
                    sensorsContainer.appendChild(createDeviceCard(deviceId, grouped[deviceId], forceExpand));
                });
            }

            function createDeviceCard(deviceId, sensors, forceExpand) {
                const card       = document.createElement('div');
                card.className   = 'sensor-card';
                const deviceName = sensors[0]?.device?.device_name || 'Unknown Device';
                const collapsed  = !forceExpand;

                card.innerHTML = `
                    <div class="device-group-header ${collapsed ? 'collapsed' : ''}" onclick="toggleDevice('${deviceId}')">
                        <i class="bi bi-chevron-down"></i>
                        <i class="bi bi-hdd-rack"></i>
                        <strong>${deviceName} (${deviceId})</strong>
                        <span class="device-badge">${sensors.length} Sensor${sensors.length !== 1 ? 's' : ''}</span>
                    </div>
                    <div class="device-sensors" id="device-${deviceId}" style="${collapsed ? 'display:none;' : 'display:block;'}">
                        ${sensors.map(s => createSensorRow(s)).join('')}
                    </div>`;
                return card;
            }

            function createSensorRow(sensor) {
                const sc = sensor.status === 'active' ? 'status-active' : 'status-inactive';
                return `
                <div class="sensor-info">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="bi bi-broadcast-pin me-1"></i> ${sensor.sensor_name}
                            </h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="status-badge ${sc}">${sensor.status}</span>
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
                                <button class="btn btn-outline-info  btn-view"   title="View"   data-id="${sensor.id}"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-outline-primary btn-edit"  title="Edit"   data-id="${sensor.id}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger  btn-delete" title="Delete" data-id="${sensor.id}"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            window.toggleDevice = function (deviceId) {
                const header  = document.querySelector(`.device-group-header[onclick*="${deviceId}"]`);
                const content = document.getElementById(`device-${deviceId}`);
                const hidden  = content.style.display === 'none';
                content.style.display = hidden ? 'block' : 'none';
                header.classList.toggle('collapsed', !hidden);
            };

            // ─────────────────────────────────────────
            // 2.  SEARCH / EXPAND-COLLAPSE
            // ─────────────────────────────────────────
            $('#searchInput').on('input', function () { filterSensors($(this).val()); });

            $('#expandAll').on('click', function () {
                $('.device-sensors').slideDown(200);
                $('.device-group-header').removeClass('collapsed');
            });
            $('#collapseAll').on('click', function () {
                $('.device-sensors').slideUp(200);
                $('.device-group-header').addClass('collapsed');
            });

            // ─────────────────────────────────────────
            // 3.  ADD MODAL – multi-row
            // ─────────────────────────────────────────

            // Build parameter <options> string once
            const paramOptions = parametersData.map(p =>
                `<option value="${p.parameter_name}" data-unit="${p.parameter_unit}">${p.parameter_label}</option>`
            ).join('');

            function buildSensorRowHTML(idx) {
                return `
                <tr data-row="${idx}">
                    <td class="text-center text-muted fw-bold row-num">${idx}</td>
                    <td><input type="text" class="form-control form-control-sm" name="sensor_number" placeholder="S00${idx}"></td>
                    <td><input type="text" class="form-control form-control-sm" name="sensor_name"   placeholder="Sensor name" required></td>
                    <td>
                        <select class="form-select form-select-sm param-select" name="parameter_name">
                            <option value="">— Select —</option>
                            ${paramOptions}
                        </select>
                    </td>
                    <td><input type="text"   class="form-control form-control-sm" name="parameter_number"          placeholder="P00${idx}"></td>
                    <td>
                        <select class="form-select form-select-sm" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control form-control-sm" name="parameter_indicator_alert" placeholder="0"></td>
                    <td><input type="text"   class="form-control form-control-sm add-date"            name="maintenance_date"   placeholder="YYYY-MM-DD"></td>
                    <td><input type="text"   class="form-control form-control-sm add-date"            name="calibration_date"   placeholder="YYYY-MM-DD"></td>
                    <td><input type="text"   class="form-control form-control-sm"                     name="notes"              placeholder="Notes..."></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove row">
                            <i class="bi bi-dash-circle"></i>
                        </button>
                    </td>
                </tr>`;
            }

            function renumberRows() {
                $('#addSensorRows tr').each(function (i) {
                    $(this).attr('data-row', i + 1).find('.row-num').text(i + 1);
                });
            }

            function initAddDatepicker(ctx) {
                $(ctx).find('.add-date').datepicker({ dateFormat: 'yy-mm-dd' });
            }

            // Reset add modal and start with 1 row
            function resetAddModal() {
                $('#add_device_id').val('').trigger('change');
                $('#addSensorRows').empty();
                addNewRow();
            }

            function addNewRow() {
                const idx = $('#addSensorRows tr').length + 1;
                const $row = $(buildSensorRowHTML(idx));
                $('#addSensorRows').append($row);
                initAddDatepicker($row);
            }

            $('#btnAdd').on('click', function () {
                resetAddModal();
                addSensorModal.show();
            });

            $('#btnAddRow').on('click', function () { addNewRow(); });

            // Remove row
            $(document).on('click', '.btn-remove-row', function () {
                if ($('#addSensorRows tr').length <= 1) {
                    Swal.fire('Info', 'At least one sensor row is required.', 'info');
                    return;
                }
                $(this).closest('tr').remove();
                renumberRows();
            });

            // Auto-fill sensor_unit hidden when param changes (add rows)
            $(document).on('change', '.param-select', function () {
                // just visual – actual unit stored server-side from ParameterModel
            });

            // Device ID select2 for add modal
            $('#addSensorModal').on('shown.bs.modal', function () {
                $('#add_device_id').select2({
                    dropdownParent: $('#addSensorModal'),
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            });

            // Save all rows
            $('#btnSaveAll').on('click', function () {
                const deviceId = $('#add_device_id').val();
                if (!deviceId) {
                    Swal.fire('Validation', 'Please select a Device ID.', 'warning');
                    return;
                }

                const rows = [];
                let hasValid = false;

                $('#addSensorRows tr').each(function () {
                    const $tr = $(this);
                    const sensorName    = $tr.find('[name=sensor_name]').val().trim();
                    const parameterName = $tr.find('[name=parameter_name]').val();
                    if (!sensorName && !parameterName) return; // skip blank rows silently

                    hasValid = true;
                    rows.push({
                        sensor_number:             $tr.find('[name=sensor_number]').val().trim(),
                        sensor_name:               sensorName,
                        parameter_name:            parameterName,
                        parameter_number:          $tr.find('[name=parameter_number]').val().trim(),
                        status:                    $tr.find('[name=status]').val(),
                        parameter_indicator_alert: $tr.find('[name=parameter_indicator_alert]').val(),
                        maintenance_date:          $tr.find('[name=maintenance_date]').val(),
                        calibration_date:          $tr.find('[name=calibration_date]').val(),
                        notes:                     $tr.find('[name=notes]').val().trim(),
                        sensor_unit: (() => {
                            const pname = $tr.find('[name=parameter_name]').val();
                            const found = parametersData.find(p => p.parameter_name === pname);
                            return found ? found.parameter_unit : '';
                        })(),
                    });
                });

                if (!hasValid || rows.length === 0) {
                    Swal.fire('Validation', 'Please fill in at least one sensor row (Sensor Name & Parameter).', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Confirmation',
                    text: `Save ${rows.length} sensor(s) for device ${deviceId}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    Swal.fire({ title: 'Saving...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    fetch('/admin/sensors/bulk', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                        body: JSON.stringify({ device_id: deviceId, sensors: rows })
                    })
                    .then(async res => {
                        const body = await res.json();
                        if (!res.ok) throw new Error(body.message || 'An error occurred');
                        return body;
                    })
                    .then(resp => {
                        Swal.fire('Success', resp.message, 'success');
                        addSensorModal.hide();
                        loadSensors();
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'));
                });
            });

            // ─────────────────────────────────────────
            // 4.  EDIT MODAL – single sensor
            // ─────────────────────────────────────────

            function initEditSelect2() {
                $('.select2-edit').select2({
                    dropdownParent: $('#editSensorModal'),
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            function setEditMode(readonly) {
                const fields = '#editSensorForm input, #editSensorForm select, #editSensorForm textarea';
                $(fields).prop('disabled', readonly);
                if (readonly) {
                    $('#btnSaveEdit').hide();
                    $('#editModalTitle').text('View Sensor Details');
                    $('.select2-edit').prop('disabled', true).trigger('change');
                } else {
                    $('#btnSaveEdit').show();
                    $('#editModalTitle').text('Edit Sensor');
                    $('.select2-edit').prop('disabled', false).trigger('change');
                }
            }

            function fetchAndFillEdit(id, viewOnly) {
                fetch(`/admin/sensors/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        $('#editSensorId').val(data.id);
                        $('#edit_device_id').val(data.device_id).trigger('change');
                        $('#edit_sensor_number').val(data.sensor_number);
                        $('#edit_sensor_name').val(data.sensor_name);
                        $('#edit_parameter_name').val(data.parameter_name).trigger('change');
                        $('#edit_parameter_number').val(data.parameter_number);
                        $('#edit_parameter_indicator_alert').val(data.parameter_indicator_alert);
                        $('#edit_sensor_unit').val(data.sensor_unit);
                        $('#edit_maintenance_date').val(data.maintenance_date);
                        $('#edit_calibration_date').val(data.calibration_date);
                        $('#edit_status').val(data.status);
                        $('#edit_notes').val(data.notes);
                        setEditMode(viewOnly);
                        editSensorModal.show();
                    })
                    .catch(() => Swal.fire('Error', 'Failed to load sensor data', 'error'));
            }

            // View
            $(document).on('click', '.btn-view', function () { fetchAndFillEdit($(this).data('id'), true); });
            // Edit
            $(document).on('click', '.btn-edit', function () { fetchAndFillEdit($(this).data('id'), false); });

            // Auto-fill unit on parameter change
            $('#edit_parameter_name').on('change', function () {
                const unit = $(this).find('option:selected').data('unit');
                $('#edit_sensor_unit').val(unit || '');
            });

            // Save edit
            $('#btnSaveEdit').on('click', function () {
                const id = $('#editSensorId').val();
                if (!id) return;

                const payload = {
                    device_id:                  $('#edit_device_id').val(),
                    sensor_number:              $('#edit_sensor_number').val(),
                    sensor_name:                $('#edit_sensor_name').val(),
                    parameter_name:             $('#edit_parameter_name').val(),
                    parameter_number:           $('#edit_parameter_number').val(),
                    parameter_indicator_alert:  $('#edit_parameter_indicator_alert').val(),
                    sensor_unit:                $('#edit_sensor_unit').val(),
                    maintenance_date:           $('#edit_maintenance_date').val(),
                    calibration_date:           $('#edit_calibration_date').val(),
                    status:                     $('#edit_status').val(),
                    notes:                      $('#edit_notes').val(),
                };

                if (!payload.device_id || !payload.sensor_name || !payload.parameter_name) {
                    Swal.fire('Validation Error', 'Device ID, Sensor Name, and Parameter are required.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Confirmation',
                    text: 'Save changes to this sensor?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    Swal.fire({ title: 'Saving...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    fetch(`/admin/sensors/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                        body: JSON.stringify(payload)
                    })
                    .then(async res => {
                        const body = await res.json();
                        if (!res.ok) throw new Error(body.message || 'An error occurred');
                        return body;
                    })
                    .then(resp => {
                        Swal.fire('Success', resp.message || 'Sensor updated', 'success');
                        editSensorModal.hide();
                        loadSensors();
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'));
                });
            });

            // ─────────────────────────────────────────
            // 5.  DELETE
            // ─────────────────────────────────────────
            $(document).on('click', '.btn-delete', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Data will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch(`/admin/sensors/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Content-Type': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => { Swal.fire('Deleted!', data.message, 'success'); loadSensors(); })
                    .catch(() => Swal.fire('Error', 'Failed to delete', 'error'));
                });
            });

            // ─────────────────────────────────────────
            // 6.  EDIT MODAL INIT
            // ─────────────────────────────────────────
            $('#editSensorModal').on('shown.bs.modal', function () {
                // Destroy & re-init select2 to fix z-index inside modal
                $('.select2-edit').select2('destroy');
                initEditSelect2();

                // Datepicker
                $('.edit-datepicker').datepicker({ dateFormat: 'yy-mm-dd' });
            });

            $('#editSensorModal').on('hidden.bs.modal', function () {
                setEditMode(false);
            });

            // ─────────────────────────────────────────
            // Initial load
            // ─────────────────────────────────────────
            loadSensors();
        });
    </script>
@endsection
