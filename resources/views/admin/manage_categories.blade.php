@extends('layout.admin')
@section('title', 'Manage Categories')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Categories</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-end">
                <button id="btnAdd" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Category</button>
            </div>
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name Category</th>
                            <th>Description</th>
                            <th>Icon SVG</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header  bg-primary text-white">
                            <h5 class="modal-title" id="modalTitle">Add Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="categoryForm">
                                <input type="hidden" id="categoryId" name="categoryId">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Name Category</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" placeholder="e.g., WQMS"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="category_description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="category_description" name="category_description" placeholder="e.g., Water Quality Monitoring System"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="category_icon" class="form-label">Icon SVG</label>
                                    <textarea class="form-control" id="category_icon" name="category_icon" rows="4" placeholder="Paste SVG code here"
                                        required></textarea>
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
        </div>
    </div>




@endsection
@section('script')

    <script>
        // expose CSRF token for fetch
        window.csrfToken = '{{ csrf_token() }}';

        document.addEventListener('DOMContentLoaded', function() {
            let table = $('#categoriesTable').DataTable({
                ajax: {
                    url: '/admin/categories',
                    dataSrc: ''
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'category_description'
                    },
                    {
                        data: 'category_icon',
                        render: function(data, type, row) {
                            return `<div style="width: 50px; height: 50px;">${data}</div>`;
                        }
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
            const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));

            $('#btnAdd').on('click', function() {
                clearForm();
                $('#modalTitle').text('Add Category');
                categoryModal.show();
            });

            // delegate edit
            $('#categoriesTable tbody').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                fetch(`/admin/categories/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        $('#categoryId').val(data.id);
                        $('#category_name').val(data.category_name);
                        $('#category_description').val(data.category_description);
                        $('#category_icon').val(data.category_icon);
                        $('#modalTitle').text('Edit Category');
                        categoryModal.show();
                    })
                    .catch(err => Swal.fire('Error', 'Gagal mengambil data category', 'error'));
            });
            // delegate delete
            $('#categoriesTable tbody').on('click', '.btn-delete', function() {
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
                        fetch(`/admin/categories/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken,
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(r => {
                                if (!r.ok) throw new Error('Failed to delete category');
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

                const id = $('#categoryId').val();
                const name = $('#category_name').val();
                const description = $('#category_description').val();
                const icon = $('#category_icon').val();

                const payload = {
                    category_name: name,
                    category_description: description,
                    category_icon: icon,
                };


                const url = id ? `/admin/categories/${id}` : '/admin/categories';
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
                    categoryModal.hide();
                    table.ajax.reload(null, false);
                }).catch(err => {
                    Swal.fire('Error', typeof err === 'string' ? err : err.message || 'Gagal menyimpan',
                        'error');
                });
            });

            function clearForm() {
                $('#categoryId').val('');
                $('#category_name').val('');
                $('#category_description').val('');
                $('#category_icon').val('');
            }


        });
    </script>

@endsection