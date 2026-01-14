@extends('layout.admin')
@section('title', 'Manage Users')
@section('content')
    <div class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Manage Users</h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-end" >
                <button id="btnAdd" class="btn btn-success"><i class="bi bi-person-plus"></i> Add User</button>

            </div>
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Level</th>
                            <th>Date Expired</th>
                            @if(auth()->user()->level === 'master')
                            <th>Access</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>


            <!-- View Modal Api Key and Reset Api Key-->
            <div class="modal fade" id="apiKeyModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="apiKeyModalTitle">API Key</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Your API Key:</p>
                            <pre id="apiKeyDisplay" class="bg-light p-2 rounded"></pre>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                    class="bi bi-x-lg"></i> Close</button>
                            <button id="resetApiKeyBtn" type="button" class="btn btn-warning"><i
                                    class="bi bi-arrow-clockwise"></i> Reset API Key</button>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal -->
            <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header  bg-primary text-white">
                            <h5 class="modal-title" id="modalTitle">Add User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="userForm">
                                <input type="hidden" id="userId" />
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" id="name" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" id="username" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" id="email" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" id="password" class="form-control"
                                            placeholder="Kosongkan jika tidak ingin mengubah" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea id="address" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Role</label>
                                        <select id="role" name="role" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Role --</option>
                                            @if(auth()->user()->level === 'master')
                                                <option value="admin">Admin</option>
                                            @endif
                                            <option value="user">User</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Level</label>
                                        <select id="level" name="level" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Level --</option>
                                            @if(auth()->user()->level === 'master')
                                                <option value="master">Master</option>
                                            @endif
                                            <option value="advanced">Advanced</option>
                                            <option value="basic">Basic</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Date Expired</label>
                                        <input type="text" id="date_expired" class="form-control"
                                            placeholder="Date Expired" />
                                    </div>

                                    @if(auth()->user()->level === 'master')
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Access</label>
                                        <select id="access" name="access" class="form-select">
                                            <option value="" selected>-- Pilih Access --</option>
                                            @foreach ($accessUsers as $accessUser)
                                                <option value="{{ $accessUser->username }}">{{ $accessUser->name }}</option>
                                            @endforeach
                                        </select>
                                        <p>
                                            <small class="text-muted">
                                                * Akses khusus untuk pengguna dengan level Master. Admin dengan level Advanced tidak dapat mengakses fitur ini.<br>
                                                * Admin level Master dapat mengatur dan menentukan akses yang diizinkan untuk admin level Advanced, termasuk pengguna mana saja yang dapat dilihat.
                                            </small>
                                        </p>
                                    </div>
                                    @endif



                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                    class="bi bi-x-lg"></i> Cancel</button>
                            <button id="saveBtn" type="button" class="btn btn-primary"><i class="bi bi-save"></i>
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
            let table = $('#usersTable').DataTable({
                ajax: {
                    url: '/admin/users',
                    dataSrc: ''
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'role'
                    },
                    {
                        data: 'level'
                    },
                    {
                        data: 'date_expired',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },

                    @if(auth()->user()->level === 'master')
                    {
                        data: 'access',
                        render: function(data, type, row) {
                            return data ? data : '-';
                        }
                    },
                    @endif
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                        <button class="btn btn-sm btn-warning btn-view-api-key" data-id="${row.id}"><i class="bi bi-key"></i></button>
						<button class="btn btn-sm btn-info btn-edit" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
						<button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}"><i class="bi bi-trash"></i></button>
					`;
                        }
                    }
                ]
            });

            // modal api key handling
            const apiKeyModal = new bootstrap.Modal(document.getElementById('apiKeyModal'));
            $('#usersTable tbody').on('click', '.btn-view-api-key', function() {
                const id = $(this).data('id');
                fetch(`/admin/users/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        $('#apiKeyDisplay').text(data.api_key || 'No API Key assigned.');
                        $('#apiKeyModalTitle').text(`API Key for ${data.name} (${data.username})`);
                        // handle reset api key button
                        $('#resetApiKeyBtn').off('click').on('click', function() {
                            Swal.fire({
                                title: 'Yakin ingin mereset API Key?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Reset',
                            }).then(result => {
                                if (result.isConfirmed) {
                                    fetch(`/admin/users/${id}/reset-api-key`, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': window.csrfToken
                                            }
                                        }).then(r => r.json())
                                        .then(resp => {
                                            Swal.fire('Berhasil', resp.message ||
                                                'API Key direset', 'success');
                                            $('#apiKeyDisplay').text(resp.api_key ||
                                                'No API Key assigned.');
                                        }).catch(() => Swal.fire('Error',
                                            'Gagal mereset API Key', 'error'));
                                }
                            });
                        });
                        apiKeyModal.show();
                    })
                    .catch(err => Swal.fire('Error', 'Gagal mengambil data pengguna', 'error'));
            });

            // modal handling
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));

            $('#btnAdd').on('click', function() {
                clearForm();
                $('#modalTitle').text('Add User');
                userModal.show();
            });

            // delegate edit
            $('#usersTable tbody').on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                fetch(`/admin/users/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        $('#userId').val(data.id);
                        $('#name').val(data.name);
                        $('#username').val(data.username);
                        $('#email').val(data.email);
                        $('#address').val(data.address);
                        $('#role').val(data.role);
                        // Update level options based on role, then set the current level
                        updateLevelOptions(data.role, data.level);
                        $('#date_expired').val(data.date_expired);
                        $('#password').val('');
                        // Set access value if field exists
                        const accessField = $('#access');
                        if (accessField.length > 0 && data.access) {
                            accessField.val(data.access);
                        }
                        $('#modalTitle').text('Edit User');
                        userModal.show();
                    })
                    .catch(err => Swal.fire('Error', 'Gagal mengambil data pengguna', 'error'));
            });

            // delegate delete
            $('#usersTable tbody').on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`/admin/users/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken
                                }
                            }).then(r => r.json())
                            .then(resp => {
                                Swal.fire('Berhasil', resp.message || 'Terhapus', 'success');
                                table.ajax.reload(null, false);
                            }).catch(() => Swal.fire('Error', 'Gagal menghapus', 'error'));
                    }
                });
            });

            // save (create or update)
            $('#saveBtn').on('click', function() {
                const id = $('#userId').val();

                // Client-side validation
                const name = $('#name').val().trim();
                const username = $('#username').val().trim();
                const email = $('#email').val().trim();
                const password = $('#password').val();
                const address = $('#address').val().trim();
                const role = $('#role').val();

                // Validate required fields
                if (!name) {
                    Swal.fire('Validation Error', 'Name is required', 'error');
                    $('#name').focus();
                    return;
                }

                if (!username) {
                    Swal.fire('Validation Error', 'Username is required', 'error');
                    $('#username').focus();
                    return;
                }

                if (username.length < 3) {
                    Swal.fire('Validation Error', 'Username must be at least 3 characters', 'error');
                    $('#username').focus();
                    return;
                }

                if (!email) {
                    Swal.fire('Validation Error', 'Email is required', 'error');
                    $('#email').focus();
                    return;
                }

                // Validate email format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire('Validation Error', 'Please enter a valid email address', 'error');
                    $('#email').focus();
                    return;
                }

                if (!address) {
                    Swal.fire('Validation Error', 'Address is required', 'error');
                    $('#address').focus();
                    return;
                }

                if (!role) {
                    Swal.fire('Validation Error', 'Role is required', 'error');
                    $('#role').focus();
                    return;
                }

                // Password validation (only for new users or when changing password)
                if (!id && !password) {
                    Swal.fire('Validation Error', 'Password is required for new users', 'error');
                    $('#password').focus();
                    return;
                }

                if (password && password.length < 6) {
                    Swal.fire('Validation Error', 'Password must be at least 6 characters', 'error');
                    $('#password').focus();
                    return;
                }

                const payload = {
                    name: name,
                    username: username,
                    email: email,
                    password: password,
                    address: address,
                    role: role,
                    level: $('#level').val(),
                    date_expired: $('#date_expired').val(),
                };

                // Add access field only if user is master level
                const accessField = $('#access');
                if (accessField.length > 0) {
                    payload.access = accessField.val();
                }

                const url = id ? `/admin/users/${id}` : '/admin/users';
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
                    userModal.hide();
                    table.ajax.reload(null, false);
                }).catch(err => {
                    Swal.fire('Error', typeof err === 'string' ? err : err.message ||
                        'Gagal menyimpan',
                        'error');
                });
            });

            $("#date_expired").datepicker({
                dateFormat: 'yy-mm-dd'
            });

            // Update level options based on role
            function updateLevelOptions(selectedRole, currentLevel = null) {
                const levelSelect = $('#level');
                const userLevel = '{{ auth()->user()->level }}';
                levelSelect.empty();
                levelSelect.append('<option value="" disabled selected>-- Pilih Level --</option>');

                if (selectedRole === 'admin') {
                    // Only master can create admin with master level
                    if (userLevel === 'master') {
                        levelSelect.append('<option value="master">Master</option>');
                    }
                    levelSelect.append('<option value="advanced">Advanced</option>');
                } else if (selectedRole === 'user') {
                    levelSelect.append('<option value="advanced">Advanced</option>');
                    levelSelect.append('<option value="basic">Basic</option>');
                }

                // Set current level if provided
                if (currentLevel) {
                    levelSelect.val(currentLevel);
                }
            }

            // Handle role change
            $('#role').on('change', function() {
                const selectedRole = $(this).val();
                updateLevelOptions(selectedRole);
            });

            function clearForm() {
                const userLevel = '{{ auth()->user()->level }}';
                $('#userId').val('');
                $('#userForm')[0].reset();
                $('#password').val('');
                // Reset level options based on user level
                const levelSelect = $('#level');
                levelSelect.empty();
                levelSelect.append('<option value="" disabled selected>-- Pilih Level --</option>');
                if (userLevel === 'master') {
                    levelSelect.append('<option value="master">Master</option>');
                }
                levelSelect.append('<option value="advanced">Advanced</option>');
                levelSelect.append('<option value="basic">Basic</option>');
            }
        });
    </script>

@endsection