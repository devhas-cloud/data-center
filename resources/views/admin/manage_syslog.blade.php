@extends('layout.admin')
@section('title', 'Manage Syslog')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Syslog</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-end">
                <a href="{{ @route('admin.syslog_add') }}" id="btnSyslog" class="btn btn-primary"><i
                        class="bi bi-plus-lg"></i> Add Syslog</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover" style="width:100%" id="manageSyslogTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Device ID</th>
                            <th>Device Name</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Note</th>
                            <th>File</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                       <!-- javascript data table -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
         document.addEventListener('DOMContentLoaded', function() {
            
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

           // Fetch syslog data and populate the table
           let table = $('#manageSyslogTable').DataTable({
                ajax: {
                    url: '/admin/syslog-data',
                    dataSrc: ''
                },
                columns: [
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {  
                        data: 'device_id'  
                    },
                    {   
                        data: 'device',
                        render: function(data, type, row) {
                            return data ? data.device_name : '-';
                        }
                    },
                    {   
                        data: 'created_date'  
                    },
                    {   
                        data: 'category',
                        render: function(data, type, row) {
                            let badgeClass = 'bg-secondary';
                            if(data === 'maintenance') badgeClass = 'bg-warning';
                            else if(data === 'calibration') badgeClass = 'bg-info';
                            else if(data === 'installation') badgeClass = 'bg-success';
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {   
                        data: 'note',
                        render: function(data, type, row) {
                            return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '-';
                        }
                    },
                    {   
                        data: 'linked_file',
                        render: function(data, type, row) {
                            if(data){
                                return `<a href="/storage/${data}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-pdf"></i> View PDF</a>`;
                            } else {
                                return '<span class="text-muted">No file</span>';
                            }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-primary btn-view" data-id="${row.id}" title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" title="Delete"><i class="bi bi-trash"></i></button>
                            `;
                        }
                    }
                ]  
            }); 
            
            // View button handler
            $(document).on('click', '.btn-view', function() {
                const id = $(this).data('id');
                window.location.href = `/admin/syslog/view/${id}`;
            });
            
            // Edit button handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                window.location.href = `/admin/syslog/edit/${id}`;
            });
            
            // Delete button handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Deleting...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Send delete request
                        $.ajax({
                            url: `/admin/syslog/delete/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    // Reload datatable
                                    table.ajax.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'Failed to delete syslog data',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });


        });

    </script>
@endsection