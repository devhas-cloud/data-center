@extends('layout.admin')
@section('title', 'Manage Devices')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Devices</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-end">
                <button id="btnAdd" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Device</button>
                <a href="{{ @route('admin.manage_access') }}" id="btnAccess" class="btn btn-primary" ><i class="bi bi-shield-lock"></i> Manage Access</a>
                <a href="{{ @route('admin.manage_syslog') }}" id="btnSyslog" class="btn btn-primary"><i class="bi bi-journal-text"></i> Manage Syslog</a>
            </div>
            <div class="table-responsive">
                <table id="devicesTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>Device ID</th>
                            <th>Device Name</th>
                            <th>Device IP</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>District</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Date Installation</th>
                            <th>Image</th>
                            @if(auth()->user()->level === 'master')
                            <th>User Assigned</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal Form (Add/Edit) -->
            <div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header  bg-primary text-white">
                            <h5 class="modal-title" id="modalTitle">Add Device</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="deviceForm">
                                <input type="hidden" id="deviceId" name="deviceId">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="device_id" class="form-label">Device ID</label>
                                            <input type="text" class="form-control" id="device_id" name="device_id" placeholder="e.g., device_001" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="device_name" class="form-label">Device Name</label>
                                            <input type="text" class="form-control" id="device_name" name="device_name" placeholder="e.g., SPARING 1" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="device_ip" class="form-label">Device IP</label>
                                            <input type="text" class="form-control" id="device_ip" name="device_ip" placeholder="e.g., 192.168.1.1">
                                        </div>

                                        <div class="mb-3">
                                            <label for="device_gap_timeout" class="form-label">Device GAP Timeout</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="device_gap_timeout" name="device_gap_timeout" placeholder="e.g., 10" min="1" value="3">
                                                <span class="input-group-text">Minutes</span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="device_category" class="form-label">Device Category</label>
                                            <select class="form-select" id="device_category" name="device_category" required>
                                                <option value="" disabled selected>Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_installation" class="form-label">Date Installation</label>
                                            <input type="date" class="form-control" id="date_installation" name="date_installation">
                                        </div>

                                        
                                       
                                    </div>

                                    <div class="col-md-6">
                                        

                                        <div class="mb-3">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Building A, Floor 3" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="district" class="form-label">District</label>
                                            <input type="text" class="form-control" id="district" name="district" placeholder="e.g., Central District" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" class="form-control" id="latitude" name="latitude" placeholder="e.g., 37.7749" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="longitude" class="form-label">Longitude</label>
                                            <input type="text" class="form-control" id="longitude" name="longitude" placeholder="e.g., -122.4194" required>
                                        </div>

                                        @if(auth()->user()->level === 'master')
                                        <div class="mb-3">
                                            <label for="user_assigned" class="form-label">User Assigned</label>
                                            <select class="form select" id="user_assigned" name="user_assigned">
                                                <option value="" disabled selected>Select User</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                @endforeach
                                            </select>   
                                        </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="linked_img" class="form-label">Device Image</label>
                                            <input type="file" class="form-control" id="linked_img" name="linked_img" accept="image/*">
                                            <small class="text-muted">Max 2MB. Allowed: jpg, jpeg, png, gif</small>
                                            <div id="current_image_preview" class="mt-2" style="display:none;">
                                                <img id="current_image" src="" alt="Current Image" style="max-width: 200px; max-height: 200px;">
                                                <p class="small text-muted mt-1">Current image</p>
                                            </div>
                                        </div>


                                        
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> <i
                                    class="bi bi-x-lg"></i> Close</button>
                            <button type="button" class="btn btn-primary" id="saveBtn"><i class="bi bi-check-lg"></i>
                                Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Image Preview (New) -->
            <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg"> <!-- Menggunakan modal-lg agar gambar lebih besar -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Device Image Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center d-flex justify-content-center align-items-center" style="background-color: #f8f9fa;">
                            <!-- Tag img kosong yang akan diisi src-nya via JS -->
                            <img id="previewImageTag" src="" alt="Device Image" class="img-fluid">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection
@section('script')


    <script>
        // expose CSRF token for fetch
        window.csrfToken = '{{ csrf_token() }}';



        document.addEventListener('DOMContentLoaded', function() {

            let table = $('#devicesTable').DataTable({
                ajax: {
                    url: '/admin/devices',
                    dataSrc: ''
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'device_id',
                        render: function(data, type, row) {
                            return $('<div/>').text(data).html(); // Escape HTML
                        }
                    },
                    {
                        data: 'device_name',
                        render: function(data, type, row) {
                            return $('<div/>').text(data).html(); // Escape HTML
                        }
                    },

                    {
                        data: 'device_ip',
                        render: function(data, type, row) {
                            return $('<div/>').text(data).html(); // Escape HTML
                        }
                    },
                    {
                        data: 'device_category',
                        render: function(data, type, row) {
                            return $('<div/>').text(data).html(); // Escape HTML
                        }
                    },
                    
                    {
                        data: 'location',
                        render: function(data, type, row) {
                            return $('<div/>').text(data).html(); // Escape HTML
                        }
                    },
                    {
                        data: 'district',
                        render: function(data, type, row) {
                            return $('<div/>').text(data).html(); // Escape HTML
                        }
                    },
                    {
                        data: 'latitude'
                    },
                    {
                        data: 'longitude'
                    },
                    {
                        data: 'date_installation',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'linked_img',
                        render: function(data, type, row) {
                            if (data) {
                                // Ubah di sini: Gunakan button dengan class btn-view-img, simpan URL di atribut data-img
                                const imgUrl = `/storage/${data}`;
                                return `<button class="btn btn-sm btn-info btn-view-img" data-img="${imgUrl}"><i class="bi bi-image"></i></button>`;
                            }
                            return '-';
                        }
                    },
                    @if(auth()->user()->level === 'master')
                    {
                        data: 'user.name',
                        render: function(data, type, row) {
                            return data ? $('<div/>').text(data).html() : '-'; // Escape HTML
                        }
                    },
                    @endif
                    {
                        data: null,
                        render: function(data, type, row) {
                            const safeId = parseInt(row.id); // Sanitize ID
                            if (isNaN(safeId)) return '';
                            return `
                                <button class="btn btn-sm btn-primary btn-edit" data-id="${safeId}"><i class="bi bi-pencil-fill"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${safeId}"><i class="bi bi-trash-fill"></i></button>
                            `;
                        }
                    }
                ]
            });

            // modal handling
            const sensorModal = new bootstrap.Modal(document.getElementById('deviceModal'));
            // Inisialisasi Modal Preview Gambar (Baru)
            const imageModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));

            $('#btnAdd').on('click', function() {
                clearForm();
                $('#modalTitle').text('Add Device');
                sensorModal.show();
            });

            // Event Handler untuk tombol View Image di tabel (Baru)
            $('#devicesTable').on('click', '.btn-view-img', function() {
                const imgSrc = $(this).data('img');
                // Set src gambar pada modal preview
                $('#previewImageTag').attr('src', imgSrc);
                // Tampilkan modal
                imageModal.show();
            });

            // edit handler
            $('#devicesTable').on('click', '.btn-edit', function() {
                const id = parseInt($(this).data('id'));
                if (isNaN(id) || id <= 0) {
                    Swal.fire('Error', 'Invalid device ID', 'error');
                    return;
                }

                fetch(`/admin/devices/${id}`, {
                        headers: {
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch device');
                        return response.json();
                    })
                    .then(data => {
                        // Sanitize data before setting values
                        $('#deviceId').val(parseInt(data.id));
                        $('#device_id').val(String(data.device_id || '').substring(0, 255));
                        $('#device_ip').val(String(data.device_ip || '').substring(0, 255));
                        $('#device_gap_timeout').val(data.device_gap_timeout);
                        $('#device_category').val(data.device_category);
                        $('#device_name').val(String(data.device_name || '').substring(0, 255));
                        $('#location').val(String(data.location || '').substring(0, 255));
                        $('#district').val(String(data.district || '').substring(0, 255));
                        $('#latitude').val(data.latitude);
                        $('#longitude').val(data.longitude);
                        $('#user_assigned').val(data.user_assigned).trigger('change');
                        $('#date_installation').val(data.date_installation || '');
                        
                        // Show current image if exists
                        if (data.linked_img) {
                            $('#current_image').attr('src', '/storage/' + data.linked_img);
                            $('#current_image_preview').show();
                        } else {
                            $('#current_image_preview').hide();
                        }
                        
                        $('#modalTitle').text('Edit Device');
                        sensorModal.show();
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to load device data', 'error');
                    });
            });

            // save handlerd dengan notif sweetalert, dan hendle error message dari server
            $('#saveBtn').on('click', function() {
                const id = $('#deviceId').val();

                // Client-side validation
                const device_id = $('#device_id').val().trim();
                const device_ip = $('#device_ip').val().trim();
                const device_gap_timeout = $('#device_gap_timeout').val().trim();
                const device_category = $('#device_category').val();
                const device_name = $('#device_name').val().trim();
                const location = $('#location').val().trim();
                const district = $('#district').val().trim();
                const latitude = $('#latitude').val().trim();
                const longitude = $('#longitude').val().trim();
                const user_assigned = $('#user_assigned').val();

                // Validate required fields
                if (!device_id) {
                    Swal.fire('Validation Error', 'Device ID is required', 'error');
                    $('#device_id').focus();
                    return;
                }

                if (device_ip && !/^(\d{1,3}\.){3}\d{1,3}$/.test(device_ip)) {
                    Swal.fire('Validation Error', 'Device IP must be a valid IP address', 'error');
                    $('#device_ip').focus();
                    return;
                }

                // Validate device_gap_timeout (must be a positive integer) and no null
                if (!device_gap_timeout || isNaN(device_gap_timeout) || parseInt(device_gap_timeout) <= 0) {
                    Swal.fire('Validation Error', 'Device GAP Timeout must be a positive integer', 'error');
                    $('#device_gap_timeout').focus();
                    return;
                }

                // Validate device_id format (alphanumeric, dash, underscore only)
                if (!/^[a-zA-Z0-9_-]+$/.test(device_id)) {
                    Swal.fire('Validation Error',
                        'Device ID can only contain letters, numbers, dash, and underscore', 'error');
                    $('#device_id').focus();
                    return;
                }

                // Max length validation
                if (device_id.length > 255) {
                    Swal.fire('Validation Error', 'Device ID must not exceed 255 characters', 'error');
                    $('#device_id').focus();
                    return;
                }

                if (!device_category) {
                    Swal.fire('Validation Error', 'Device Category is required', 'error');
                    $('#device_category').focus();
                    return;
                }

                if (!device_name) {
                    Swal.fire('Validation Error', 'Serial Number is required', 'error');
                    $('#device_name').focus();
                    return;
                }

                // Validate serial number length
                if (device_name.length > 255) {
                    Swal.fire('Validation Error', 'Serial Number must not exceed 255 characters', 'error');
                    $('#device_name').focus();
                    return;
                }

                if (!location) {
                    Swal.fire('Validation Error', 'Location is required', 'error');
                    $('#location').focus();
                    return;
                }

                // Validate location length
                if (location.length > 255) {
                    Swal.fire('Validation Error', 'Location must not exceed 255 characters', 'error');
                    $('#location').focus();
                    return;
                }

                if (!district) {
                    Swal.fire('Validation Error', 'District is required', 'error');
                    $('#district').focus();
                    return;
                }

                // Validate district length
                if (district.length > 255) {
                    Swal.fire('Validation Error', 'District must not exceed 255 characters', 'error');
                    $('#district').focus();
                    return;
                }

                if (!latitude) {
                    Swal.fire('Validation Error', 'Latitude is required', 'error');
                    $('#latitude').focus();
                    return;
                }

                // Validate latitude format (must be a number between -90 and 90)
                const latNum = parseFloat(latitude);
                if (isNaN(latNum) || latNum < -90 || latNum > 90) {
                    Swal.fire('Validation Error', 'Latitude must be a number between -90 and 90', 'error');
                    $('#latitude').focus();
                    return;
                }

                if (!longitude) {
                    Swal.fire('Validation Error', 'Longitude is required', 'error');
                    $('#longitude').focus();
                    return;
                }

                // Validate longitude format (must be a number between -180 and 180)
                const lonNum = parseFloat(longitude);
                if (isNaN(lonNum) || lonNum < -180 || lonNum > 180) {
                    Swal.fire('Validation Error', 'Longitude must be a number between -180 and 180',
                        'error');
                    $('#longitude').focus();
                    return;
                }

                // Validate image file if provided
                const imageFile = $('#linked_img')[0].files[0];
                if (imageFile) {
                    // Check file size (max 2MB)
                    if (imageFile.size > 2 * 1024 * 1024) {
                        Swal.fire('Validation Error', 'Image file must not exceed 2MB', 'error');
                        $('#linked_img').focus();
                        return;
                    }
                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(imageFile.type)) {
                        Swal.fire('Validation Error', 'Image must be jpeg, jpg, png, or gif format', 'error');
                        $('#linked_img').focus();
                        return;
                    }
                }

                const url = id ? `/admin/devices/${id}` : '/admin/devices';
                const method = id ? 'POST' : 'POST'; // Always POST for FormData
                
                // Use FormData for file upload
                const formData = new FormData();
                formData.append('device_id', device_id);
                formData.append('device_ip', device_ip);
                formData.append('device_gap_timeout', device_gap_timeout);
                formData.append('device_category', device_category);
                formData.append('device_name', device_name);
                formData.append('location', location);
                formData.append('district', district);
                formData.append('latitude', latitude);
                formData.append('longitude', longitude);
                if (user_assigned) formData.append('user_assigned', user_assigned);
                
                const date_installation = $('#date_installation').val();
                if (date_installation) formData.append('date_installation', date_installation);
                
                if (imageFile) formData.append('linked_img', imageFile);
                
                // Add _method for PUT when editing
                if (id) {
                    formData.append('_method', 'PUT');
                }

                fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw err;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        sensorModal.hide();
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: `Device has been ${id ? 'updated' : 'added'} successfully.`
                        })
                    })
                    .catch(error => {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (error.errors) {
                            errorMessage = Object.values(error.errors).flat().join(' ');
                        } else if (error.message) {
                            errorMessage = error.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    });
            });



            // delete handler and confirmation with sweetalert
            $('#devicesTable').on('click', '.btn-delete', function() {
                const id = parseInt($(this).data('id'));
                if (isNaN(id) || id <= 0) {
                    Swal.fire('Error', 'Invalid device ID', 'error');
                    return;
                }
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/admin/devices/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => {
                                        throw err;
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                table.ajax.reload();
                                Swal.fire(
                                    'Deleted!',
                                    'Device has been deleted.',
                                    'success'
                                );
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete the device. Please try again.'
                                });
                            });
                    }
                });
            });


            function clearForm() {
                $('#deviceId').val('');
                $('#deviceForm')[0].reset();
                $('#current_image_preview').hide();
            }

           // Initialize Select2 for searchable dropdowns
              $('#device_category').select2({
                 theme: 'bootstrap-5',
                 dropdownParent: $('#deviceModal')
                });
            
              $('#user_assigned').select2({
                 theme: 'bootstrap-5',
                 dropdownParent: $('#deviceModal')
                });

            // Clean up Select2 when modal is hidden
            $('#deviceModal').on('hidden.bs.modal', function () {
                $('#device_category').val(null).trigger('change');
                $('#user_assigned').val(null).trigger('change');
                // Clear file input and image preview
                $('#linked_img').val('');
                $('#current_image_preview').hide();
            });
            
            // Clean up image preview when image modal is hidden
            $('#imagePreviewModal').on('hidden.bs.modal', function () {
                $('#previewImageTag').attr('src', '');
            });
           
        });
    </script>

@endsection