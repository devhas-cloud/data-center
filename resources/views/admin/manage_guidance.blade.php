@extends('layout.admin')
@section('title', 'Manage Guidance')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Guidance</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-end">
                <button id="btnAdd" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Guidance</button>
            </div>
            <div class="table-responsive">
                <table id="guidanceTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="guidanceTableBody">
                        @foreach($data as $index => $guidance)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $guidance->title }}</td>
                                <td>{{ $guidance->content }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info btnView" data-id="{{ $guidance->id }}"><i class="bi bi-image"></i> View Img</button>
                                    <button class="btn btn-sm btn-primary btnEdit" data-id="{{ $guidance->id }}"><i class="bi bi-pencil"></i> Edit</button>
                                    <button class="btn btn-sm btn-danger btnDelete" data-id="{{ $guidance->id }}"><i class="bi bi-trash"></i> Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Guidance -->
    <div class="modal fade" id="guidanceModal" tabindex="-1" aria-labelledby="guidanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidanceModalLabel">Add Guidance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="guidanceForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="guidanceId" name="id">
                    <input type="hidden" id="formMethod" name="_method" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback" id="titleError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image <span class="text-danger" id="imageRequired">*</span></label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="invalid-feedback" id="imageError"></div>
                            <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                        </div>
                        <div class="mb-3" id="currentImageContainer" style="display: none;">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img id="currentImage" src="" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="3"></textarea>
                            <div class="invalid-feedback" id="contentError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="link_path" class="form-label">Link Path</label>
                            <input type="url" class="form-control" id="link_path" name="link_path" placeholder="https://example.com">
                            <div class="invalid-feedback" id="linkPathError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Image -->
    <div class="modal fade" id="viewImageModal" tabindex="-1" aria-labelledby="viewImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewImageModalLabel">View Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="viewImage" src="" alt="Guidance Image" class="img-fluid">
                    <div id="noImageText" class="text-muted" style="display: none;">No image available</div>
                </div>
            </div>
        </div>
    </div>

   
@endsection
@section('script')
 <script>
        $(document).ready(function() {
            // Add button click
            $('#btnAdd').click(function() {
                resetForm();
                $('#guidanceModalLabel').text('Add Guidance');
                $('#formMethod').val('POST');
                $('#guidanceId').val('');
                $('#imageRequired').show();
                $('#currentImageContainer').hide();
                $('#guidanceModal').modal('show');
            });

            // Form submit
            $('#guidanceForm').submit(function(e) {
                e.preventDefault();
                clearErrors();

                let formData = new FormData(this);
                let guidanceId = $('#guidanceId').val();
                let url = guidanceId ? `/admin/guidance/${guidanceId}` : '/admin/guidance';
                
                if (guidanceId) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#guidanceModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            displayErrors(errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON.message || 'An error occurred',
                            });
                        }
                    }
                });
            });

            // Edit button click
            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                
                $.ajax({
                    url: `/admin/guidance/${id}`,
                    type: 'GET',
                    success: function(response) {
                        resetForm();
                        $('#guidanceModalLabel').text('Edit Guidance');
                        $('#formMethod').val('PUT');
                        $('#guidanceId').val(response.id);
                        $('#title').val(response.title);
                        $('#description').val(response.description);
                        $('#content').val(response.content);
                        $('#link_path').val(response.link_path);
                        
                        $('#imageRequired').hide();
                        
                        if (response.image_path) {
                            $('#currentImageContainer').show();
                            $('#currentImage').attr('src', `/storage/${response.image_path}`);
                        } else {
                            $('#currentImageContainer').hide();
                        }
                        
                        $('#guidanceModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load guidance data',
                        });
                    }
                });
            });

            // View button click
            $(document).on('click', '.btnView', function() {
                let id = $(this).data('id');
                
                $.ajax({
                    url: `/admin/guidance/${id}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.image_path) {
                            $('#viewImage').attr('src', `/storage/${response.image_path}`).show();
                            $('#noImageText').hide();
                        } else {
                            $('#viewImage').hide();
                            $('#noImageText').show();
                        }
                        $('#viewImageModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load image',
                        });
                    }
                });
            });

            // Delete button click
            $(document).on('click', '.btnDelete', function() {
                let id = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/guidance/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON.message || 'Failed to delete guidance',
                                });
                            }
                        });
                    }
                });
            });

            // Helper functions
            function resetForm() {
                $('#guidanceForm')[0].reset();
                clearErrors();
            }

            function clearErrors() {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            function displayErrors(errors) {
                for (let field in errors) {
                    let errorField = field.replace('_', '');
                    $(`#${field}`).addClass('is-invalid');
                    $(`#${errorField}Error`).text(errors[field][0]);
                }
            }
        });
    </script>

@endsection