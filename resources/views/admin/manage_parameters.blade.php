@extends('layout.admin')
@section('title', 'Manage parameters')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Parameters</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-end">
                <button id="btnAdd" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Parameter</button>
            </div>
            <div class="table-responsive">
                <table id="parametersTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Label</th>
                            <th>Name Parameter</th>
                            <th>Unit</th>
                            <th>Indicator Min</th>
                            <th>Indicator Max</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="parameterModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalTitle">Add Parameter</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <form id="parameterForm">
                                <input type="hidden" id="parameterId" name="parameterId">

                                <div class="row g-3">
                                    <!-- Label -->
                                    <div class="col-md-6">
                                        <label class="form-label">Label</label>
                                        <input type="text" class="form-control" id="parameter_label"
                                            name="parameter_label" placeholder="e.g., Humidity">
                                    </div>

                                    <!-- Name -->
                                    <div class="col-md-6">
                                        <label class="form-label">Name Parameter</label>
                                        <input type="text" class="form-control" id="parameter_name" name="parameter_name"
                                            placeholder="e.g., humidity" required>
                                    </div>

                                    <!-- Unit -->
                                    <div class="col-md-4">
                                        <label class="form-label">Unit</label>
                                        <input type="text" class="form-control" id="parameter_unit" name="parameter_unit"
                                            placeholder="e.g., %" required>
                                    </div>

                                    <!-- Min -->
                                    <div class="col-md-4">
                                        <label class="form-label">Min Indicator</label>
                                        <input type="number" class="form-control" id="parameter_indicator_min"
                                            name="parameter_indicator_min" placeholder="e.g., 1" required>
                                    </div>

                                    <!-- Max -->
                                    <div class="col-md-4">
                                        <label class="form-label">Max Indicator</label>
                                        <input type="number" class="form-control" id="parameter_indicator_max"
                                            name="parameter_indicator_max" placeholder="e.g., 100" required>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg"></i> Close
                            </button>
                            <button class="btn btn-primary" id="saveBtn">
                                <i class="bi bi-check-lg"></i> Save
                            </button>
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
            let table = $('#parametersTable').DataTable({
                ajax: {
                    url: '/admin/parameters',
                    dataSrc: ''
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'parameter_label'
                    },
                    {
                        data: 'parameter_name'
                    },
                    {
                        data: 'parameter_unit'
                    },
                    {
                        data: 'parameter_indicator_min'
                    },
                    {
                        data: 'parameter_indicator_max'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
						<button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
						<button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
					`;
                        }
                    }
                ]
            });

            // modal handling
            const parameterModal = new bootstrap.Modal(document.getElementById('parameterModal'));

            $('#btnAdd').on('click', function() {
                clearForm();
                $('#modalTitle').text('Add Parameter');
                parameterModal.show();
            });

            // delegate edit
            $('#parametersTable tbody').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                fetch(`/admin/parameters/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        $('#parameterId').val(data.id);
                        $('#parameter_label').val(data.parameter_label);
                        $('#parameter_name').val(data.parameter_name);
                        $('#parameter_unit').val(data.parameter_unit);
                        $('#parameter_indicator_min').val(data.parameter_indicator_min);
                        $('#parameter_indicator_max').val(data.parameter_indicator_max);
                        $('#modalTitle').text('Edit Parameter');
                        parameterModal.show();
                    })
                    .catch(err => Swal.fire('Error', 'Gagal mengambil data parameter', 'error'));
            });
            // delegate delete
            $('#parametersTable tbody').on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/admin/parameters/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken,
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(r => {
                                if (!r.ok) throw new Error('Failed to delete parameter');
                                return r.json();
                            })
                            .then(data => {
                                Swal.fire('Deleted!', data.message, 'success');
                                table.ajax.reload();
                            })
                            .catch(err => Swal.fire('Error', err.message, 'error'));
                    }

                });

            });
            // save button
            $('#saveBtn').on('click', function() {

                const id = $('#parameterId').val();
                const name = $('#parameter_name').val();
                const label = $('#parameter_label').val();
                const unit = $('#parameter_unit').val();
                const indicator_min = $('#parameter_indicator_min').val();
                const indicator_max = $('#parameter_indicator_max').val();

                const payload = {
                    parameter_name: name,
                    parameter_label: label,
                    parameter_unit: unit,
                    parameter_indicator_min: indicator_min,
                    parameter_indicator_max: indicator_max

                };


                const url = id ? `/admin/parameters/${id}` : '/admin/parameters';
                const method = id ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify(payload)
                }).then(async res => {
                    const body = await res.json();
                    if (!res.ok) {
                        const message = body.message || 'Terjadi kesalahan';
                        return Promise.reject(message);
                    }
                    return body;
                }).then(resp => {
                    Swal.fire('Sukses', resp.message || 'Berhasil disimpan', 'success');
                    parameterModal.hide();
                    table.ajax.reload(null, false);
                }).catch(err => {
                    Swal.fire('Error', typeof err === 'string' ? err : err.message ||
                        'Gagal menyimpan',
                        'error');
                });
            });

            function clearForm() {
                $('#parameterId').val('');
                $('#parameter_label').val('');
                $('#parameter_name').val('');
                $('#parameter_unit').val('');
                $('#parameter_indicator_min').val('');
                $('#parameter_indicator_max').val('');
            }


        });
    </script>

@endsection
