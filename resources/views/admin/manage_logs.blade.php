@extends('layout.admin')
@section('title', 'Manage Logs')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Logs</h4>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="logsTable" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Log Date</th>
                            <th>Device ID</th>
                            <th>Device Name</th>
                            <th>Category</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table body will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Update Log -->
    <div class="modal fade" id="updateLogModal" tabindex="-1" aria-labelledby="updateLogModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="updateLogModalLabel">
                        <i class="bi bi-pencil-square"></i> Update Log Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateLogForm">
                        <input type="hidden" id="logId" name="logId">
                        <div class="mb-3">
                            <label for="logStatus" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="logStatus" name="status" required>
                                <option value="unaction">Unaction</option>
                                <option value="progress">On Progress</option>
                                <option value="solve">Solved</option>
                                <option value="unsolve">Unsolved</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Close
                    </button>
                    <button type="submit" form="updateLogForm" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        $(document).ready(function() {

            // Initialize DataTable
            var table = $('#logsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route('admin.logs_data') }}',
                    dataSrc: '',
                    error: function(xhr, error, code) {
                        console.error('Error loading logs:', error);
                        Swal.fire('Error', 'Failed to load logs data', 'error');
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'created_at',
                        defaultContent: '-',
                        render: function(data, type) {
                            if (!data) return '-';

                            const d = new Date(data);

                            const year = d.getFullYear();
                            const month = String(d.getMonth() + 1).padStart(2, '0');
                            const day = String(d.getDate()).padStart(2, '0');
                            const hour = String(d.getHours()).padStart(2, '0');
                            const min = String(d.getMinutes()).padStart(2, '0');

                            return `${year}-${month}-${day} ${hour}:${min}`;
                        }
                    },
                    {
                        data: 'device_id',
                        defaultContent: '-',
                        className: 'text-center'
                    },
                    {
                        data: null,
                        defaultContent: '-',
                        render: function(data, type, row) {
                            return row.devices && row.devices.device_name ? row.devices
                                .device_name : '-';
                        }
                    },
                    {
                        data: 'category',
                        defaultContent: '-',
                        className: 'text-center',
                        render: function(data) {
                           
                            return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                        }
                    },
                    {
                        data: 'message',
                        defaultContent: '-',
                        render: function(data) {
                            if (!data || data === '-') return '-';
                            return data.length > 50 ?
                                `<span title="${data}">${data.substring(0, 50)}...</span>` :
                                data;
                        }
                    },
                    {
                        data: 'action',
                        defaultContent: 'unaction',
                        className: 'text-center',
                        render: function(data) {
                            const statusConfig = {
                                'unaction': {
                                    class: 'secondary',
                                    text: 'Unaction'
                                },
                                'progress': {
                                    class: 'warning',
                                    text: 'On Progress'
                                },
                                'solve': {
                                    class: 'success',
                                    text: 'Solved'
                                },
                                'unsolve': {
                                    class: 'danger',
                                    text: 'Unsolved'
                                }
                            };
                            const config = statusConfig[data] || statusConfig['unaction'];
                            return `<span class="badge bg-${config.class}">${config.text}</span>`;
                        }
                    },
                    {
                        data: 'updated_at',
                        defaultContent: '-',
                        render: function(data, type) {
                            if (!data) return '-';

                            const d = new Date(data);

                            const year = d.getFullYear();
                            const month = String(d.getMonth() + 1).padStart(2, '0');
                            const day = String(d.getDate()).padStart(2, '0');
                            const hour = String(d.getHours()).padStart(2, '0');
                            const min = String(d.getMinutes()).padStart(2, '0');

                            return `${year}-${month}-${day} ${hour}:${min}`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <button 
                                    class="btn btn-sm btn-primary update-log-btn" 
                                    data-id="${row.id}" 
                                    data-status="${row.status || 'unaction'}"
                                    title="Update Status">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [
                    [1, 'desc']
                ], // Sort by log_date descending
                pageLength: 25,
                language: {
                    processing: "Loading...",
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No logs available"
                }
            });

            // Handle update log button click
            $('#logsTable tbody').on('click', '.update-log-btn', function() {
                const logId = $(this).data('id');
                const logStatus = $(this).data('status') || 'unaction';

                $('#logId').val(logId);
                $('#logStatus').val(logStatus);
                $('#updateLogModal').modal('show');
            });

            // Handle update log form submission
            $('#updateLogForm').on('submit', function(e) {
                e.preventDefault();

                const logId = $('#logId').val();
                const status = $('#logStatus').val();

                if (!logId || !status) {
                    Swal.fire('Validation Error', 'Please select a valid status', 'warning');
                    return;
                }

                // Confirmation dialog
                Swal.fire({
                    title: 'Confirmation',
                    text: 'Are you sure you want to update this log status?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    // Show loading
                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/logs/${logId}`,
                        type: 'PUT',
                        data: {
                            action: status,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#updateLogModal').modal('hide');
                            table.ajax.reload(null,
                                false); // Reload without resetting paging
                            Swal.fire('Success', response.message ||
                                'Log status updated successfully!', 'success');
                        },
                        error: function(xhr) {
                            let errorMessage =
                                'An error occurred while updating the log status.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMessage, 'error');
                        }
                    });
                });
            });
        });
    </script>
@endsection
