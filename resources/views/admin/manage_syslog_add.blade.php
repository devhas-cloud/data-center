@extends('layout.admin')
@section('title', 'Manage Syslog')
@section('content')
<div class="card mt-5">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4> Manage Syslog</h4>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Validation Error!</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <form id="syslogForm" method="POST" action="{{ route('admin.syslog_store') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Header Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Header Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="device_id" class="form-label">Device <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="device_id" name="device_id" required>
                                <option value="">-- Select Device --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->device_id }}">{{ $device->device_name }} ({{ $device->device_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}" id="date" name="date" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="calibration">Calibration</option>
                                <option value="installation">Installation</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pdf_file" class="form-label">Upload PDF</label>
                            <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                            <small class="text-muted">Maximum file size: 5MB</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3" placeholder="Enter additional notes..."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Detail Section -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Information</h5>
                    <button type="button" class="btn btn-light btn-sm" id="btnAddRow">
                        <i class="bi bi-plus-lg"></i> Add Row
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="detailTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th width="300">Parameter</th>
                                    <th>Description</th>
                                    <th width="100">Action</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                <tr class="detail-row">
                                    <td class="text-center">1</td>
                                    <td>
                                        <select class="form-select parameter-select" name="details[0][parameter_name]" required disabled>
                                            <option value="">-- Select Device First --</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="details[0][description]" placeholder="Enter description...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btnRemoveRow" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ @route('admin.manage_syslog') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                    <button type="button" id="btnReset" class="btn btn-warning me-2">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Syslog
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection

@section('script')
<script>
// Data dari backend
const syslogData = @json($syslogData);

document.addEventListener('DOMContentLoaded', function() {
    let rowCount = 1;
    let selectedDeviceId = '';
    let deviceParameters = [];
    
    // Show SweetAlert for success message
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    @endif
    
    // Show SweetAlert for error message
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
    @endif
    
    // Initialize Select2 for device select with search
    $('#device_id').select2({
        placeholder: '-- Select Device --',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
    });

    // File validation for PDF upload
    document.getElementById('pdf_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Check file type
            if (file.type !== 'application/pdf') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type!',
                    text: 'Please upload only PDF files.',
                    confirmButtonColor: '#d33'
                });
                e.target.value = '';
                return;
            }
            
            // Check file size (5MB = 5 * 1024 * 1024 bytes)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large!',
                    text: 'Maximum file size is 5MB. Your file is ' + (file.size / (1024 * 1024)).toFixed(2) + 'MB.',
                    confirmButtonColor: '#d33'
                });
                e.target.value = '';
                return;
            }
        }
    });
    // Handle device selection change (using jQuery for Select2 compatibility)
    $('#device_id').on('change', function() {
        selectedDeviceId = $(this).val();
        
        if (selectedDeviceId) {
            // Filter parameters untuk device yang dipilih
            deviceParameters = [];
            syslogData.forEach(item => {
                if (item.device_id === selectedDeviceId) {
                    item.parameter.forEach(param => {
                        if (!deviceParameters.find(p => p.parameter_name === param.parameter_name)) {
                            deviceParameters.push(param);
                        }
                    });
                }
            });
            
            // Enable parameter selects dan update options
            updateAllParameterSelects();
        } else {
            // Reset jika device tidak dipilih
            deviceParameters = [];
            document.querySelectorAll('.parameter-select').forEach(select => {
                select.disabled = true;
                select.innerHTML = '<option value="">-- Select Device First --</option>';
            });
        }
    });
    
    // Add new row
    document.getElementById('btnAddRow').addEventListener('click', function() {
        if (!selectedDeviceId) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select a device first!',
                confirmButtonColor: '#f0ad4e'
            });
            return;
        }
        const tbody = document.getElementById('detailTableBody');
        const newRow = document.querySelector('.detail-row').cloneNode(true);
        
        // Update row number
        rowCount++;
        newRow.querySelector('td:first-child').textContent = rowCount;
        
        // Update input names
        const parameterSelect = newRow.querySelector('select[name^="details"]');
        const descriptionInput = newRow.querySelector('input[name^="details"]');
        
        parameterSelect.name = `details[${rowCount - 1}][parameter_name]`;
        parameterSelect.value = '';
        parameterSelect.classList.add('parameter-select');
        
        descriptionInput.name = `details[${rowCount - 1}][description]`;
        descriptionInput.value = '';
        
        // Enable remove button
        const removeBtn = newRow.querySelector('.btnRemoveRow');
        removeBtn.disabled = false;
        
        tbody.appendChild(newRow);
        updateRemoveButtons();
        updateAllParameterSelects();
        
        // Add change listener untuk parameter select yang baru
        parameterSelect.addEventListener('change', function() {
            updateAllParameterSelects();
        });
    });
    
    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btnRemoveRow')) {
            const row = e.target.closest('tr');
            row.remove();
            updateRowNumbers();
            updateRemoveButtons();
            updateAllParameterSelects();
        }
    });
    
    // Reset button handler
    document.getElementById('btnReset').addEventListener('click', function() {
        Swal.fire({
            icon: 'question',
            title: 'Are you sure?',
            text: 'Do you want to reset the form?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reset it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Reset form
                document.getElementById('syslogForm').reset();
            
            // Reset Select2
            $('#device_id').val('').trigger('change');
            
            // Reset variables
            selectedDeviceId = '';
            deviceParameters = [];
            rowCount = 1;
            
            // Remove all rows except first
            const tbody = document.getElementById('detailTableBody');
            const rows = tbody.querySelectorAll('tr');
            for (let i = rows.length - 1; i > 0; i--) {
                rows[i].remove();
            }
            
            // Reset first row
            const firstRow = tbody.querySelector('tr');
            firstRow.querySelector('td:first-child').textContent = '1';
            firstRow.querySelector('select[name^="details"]').name = 'details[0][parameter_name]';
            firstRow.querySelector('select[name^="details"]').value = '';
            firstRow.querySelector('select[name^="details"]').disabled = true;
            firstRow.querySelector('select[name^="details"]').innerHTML = '<option value="">-- Select Device First --</option>';
            firstRow.querySelector('input[name^="details"]').name = 'details[0][description]';
            firstRow.querySelector('input[name^="details"]').value = '';
            firstRow.querySelector('.btnRemoveRow').disabled = true;
            
            // Reset date to today
            document.getElementById('date').value = '{{ date("Y-m-d") }}';
            
            // Remove validation classes
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            }
        });
    });
    
    // Update all parameter selects
    function updateAllParameterSelects() {
        const selectedParameters = getSelectedParameters();
        
        document.querySelectorAll('.parameter-select').forEach(select => {
            const currentValue = select.value;
            select.disabled = !selectedDeviceId;
            
            // Build options
            let html = '<option value="">-- Select Parameter --</option>';
            deviceParameters.forEach(param => {
                // Tampilkan parameter jika belum dipilih atau ini adalah parameter yang sedang dipilih
                if (!selectedParameters.includes(param.parameter_name) || param.parameter_name === currentValue) {
                    html += `<option value="${param.parameter_name}">${param.parameter_label}</option>`;
                }
            });
            
            select.innerHTML = html;
            select.value = currentValue; // Restore value
        });
    }
    
    // Get all selected parameters
    function getSelectedParameters() {
        const selected = [];
        document.querySelectorAll('.parameter-select').forEach(select => {
            if (select.value) {
                selected.push(select.value);
            }
        });
        return selected;
    }
    
    // Update row numbers
    function updateRowNumbers() {
        const rows = document.querySelectorAll('#detailTableBody tr');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
            row.querySelector('select[name^="details"]').name = `details[${index}][parameter_name]`;
            row.querySelector('input[name^="details"]').name = `details[${index}][description]`;
        });
        rowCount = rows.length;
    }
    
    // Update remove buttons (disable if only one row)
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('#detailTableBody tr');
        const removeButtons = document.querySelectorAll('.btnRemoveRow');
        
        if (rows.length === 1) {
            removeButtons[0].disabled = true;
        } else {
            removeButtons.forEach(btn => btn.disabled = false);
        }
    }
    
    // Form validation
    document.getElementById('syslogForm').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#detailTableBody tr');
        let isValid = true;
        
        rows.forEach(row => {
            const parameterSelect = row.querySelector('select[name^="details"]');
            if (!parameterSelect.value) {
                isValid = false;
                parameterSelect.classList.add('is-invalid');
            } else {
                parameterSelect.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                text: 'Please select a parameter for all detail rows.',
                confirmButtonColor: '#d33'
            });
        }
    });
    
    // Add change listener to initial parameter select
    document.querySelector('.parameter-select').addEventListener('change', function() {
        updateAllParameterSelects();
    });
});
</script>
@endsection