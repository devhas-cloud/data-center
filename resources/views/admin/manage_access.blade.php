@extends('layout.admin')
@section('title', 'Manage Access')


<style>
    .category-card {
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .category-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .device-list {
        max-height: 450px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .device-list::-webkit-scrollbar {
        width: 8px;
    }

    .device-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .device-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .device-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .device-item {
        padding: 0.75rem 1.5rem;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .device-item:last-child {
        border-bottom: none;
    }

    .device-item:hover {
        background-color: #f8f9fa;
    }

    .device-item.selected {
        background-color: #e7f3ff;
        border-left: 4px solid #0d6efd;
    }

    .device-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .device-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .device-name {
        font-weight: 500;
        color: #495057;
        font-size: 0.95rem;
    }

    .device-id {
        font-weight: 600;
        color: #212529;
        font-size: 0.9rem;
    }

    .device-location {
        font-size: 0.85rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .select-all-btn {
        margin-bottom: 1rem;
    }

    .user-selector {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .save-access-btn {
        position: sticky;
        bottom: 20px;
        z-index: 100;
    }

    /* Select2 Custom Styling */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 48px;
        font-size: 1.1rem;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 46px;
        padding-left: 12px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }

    .select2-dropdown {
        font-size: 1rem;
    }

    .select2-results__option {
        padding: 10px 12px;
    }
</style>

@section('content')
    <div class="card mb-4" style="margin-top:50px">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #DAD7D7">
            <h4 class="mb-0">
                <i class="bi bi-shield-lock"></i> Manage Device Access
            </h4>
        </div>
    </div>

    <!-- User Selector -->
    <div class="user-selector">
        <div class="row align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-bold">
                    <i class="bi bi-person-circle"></i> Select User
                </label>
                <select id="userSelect" class="form-select form-select-lg">
                    <option value="" selected disabled>-- Select User to Manage Access --</option>
                    @if($users->count() > 0)
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" data-username="{{ $user->username }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    @else
                        <option disabled>No users available</option>
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <button id="saveAccessBtn" class="btn btn-success btn-lg w-100" disabled>
                    <i class="bi bi-save"></i> Save Access
                </button>
            </div>
        </div>
    </div>

    <!-- Access Categories -->
    <div id="accessCategories" style="display: none;">
        <div class="row">
            @foreach($categories as $category)
                <div class="col-lg-6 col-md-12">
                    <div class="category-card">
                        <div class="category-header">
                            <i class="bi bi-collection"></i>
                            <span>{{ $category->category_name }}</span>
                            <span class="badge bg-light text-dark ms-auto">
                                <span class="selected-count-{{ $category->id }}">0</span> / {{ $category->devices->count() }}
                            </span>
                        </div>
                        <div class="card-body p-0">
                            @if($category->devices->count() > 0)
                                <div class="p-3">
                                    <button class="btn btn-sm btn-outline-primary select-all-category" 
                                            data-category-id="{{ $category->id }}">
                                        <i class="bi bi-check-all"></i> Select All
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary deselect-all-category" 
                                            data-category-id="{{ $category->id }}">
                                        <i class="bi bi-x-circle"></i> Deselect All
                                    </button>
                                </div>
                                <div class="device-list">
                                    @foreach($category->devices->sortBy('location') as $device)
                                        <div class="device-item" 
                                             data-device-id="{{ $device->id }}"
                                             data-category-id="{{ $category->id }}">
                                            <input type="checkbox" 
                                                   class="device-checkbox" 
                                                   data-device-id="{{ $device->id }}"
                                                   data-category-id="{{ $category->id }}"
                                                   id="device-{{ $device->id }}">
                                            <div class="device-info">
                                                <div class="device-id">{{ $device->device_id }}</div>
                                                <div class="device-name">{{ $device->device_name }}</div>
                                                <div class="device-location">
                                                    <i class="bi bi-geo-alt"></i>
                                                    {{ $device->location }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> No devices in this category
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

   
@endsection
@section('script')\
     
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentUserId = null;
            let currentUserAccess = [];

            const userSelect = document.getElementById('userSelect');
            const accessCategories = document.getElementById('accessCategories');
            const saveAccessBtn = document.getElementById('saveAccessBtn');

            // Initialize Select2
            $('#userSelect').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Select User to Manage Access --',
                allowClear: true,
                dropdownParent: $('.user-selector')
            });

            // Handle user selection (Select2 compatible)
            $('#userSelect').on('change', function() {
                currentUserId = this.value;
                if (currentUserId) {
                    loadUserAccess(currentUserId);
                    accessCategories.style.display = 'block';
                    saveAccessBtn.disabled = false;
                } else {
                    accessCategories.style.display = 'none';
                    saveAccessBtn.disabled = true;
                }
            });

            // Load user's current access
            function loadUserAccess(userId) {
                fetch(`/admin/access/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        currentUserAccess = data.access || [];
                        updateCheckboxes();
                    })
                    .catch(error => {
                        console.error('Error loading user access:', error);
                        Swal.fire('Error', 'Failed to load user access', 'error');
                    });
            }

            // Update checkboxes based on current access
            function updateCheckboxes() {
                // Reset all checkboxes first
                document.querySelectorAll('.device-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.closest('.device-item').classList.remove('selected');
                });

                // Check boxes for devices user has access to
                currentUserAccess.forEach(access => {
                    const checkbox = document.querySelector(`.device-checkbox[data-device-id="${access.device_id}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.closest('.device-item').classList.add('selected');
                    }
                });

                updateCategoryCounts();
            }

            // Update selected count for each category
            function updateCategoryCounts() {
                document.querySelectorAll('.category-card').forEach(card => {
                    const categoryId = card.querySelector('.select-all-category')?.getAttribute('data-category-id');
                    if (categoryId) {
                        const checkedCount = card.querySelectorAll('.device-checkbox:checked').length;
                        const countSpan = document.querySelector(`.selected-count-${categoryId}`);
                        if (countSpan) {
                            countSpan.textContent = checkedCount;
                        }
                    }
                });
            }

            // Handle device checkbox change
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('device-checkbox')) {
                    const deviceItem = e.target.closest('.device-item');
                    if (e.target.checked) {
                        deviceItem.classList.add('selected');
                    } else {
                        deviceItem.classList.remove('selected');
                    }
                    updateCategoryCounts();
                }
            });

            // Handle device item click (toggle checkbox)
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('device-item') || 
                    e.target.classList.contains('device-info') ||
                    e.target.classList.contains('device-name') ||
                    e.target.classList.contains('device-id') ||
                    e.target.classList.contains('device-location') ||
                    e.target.closest('.device-info')) {
                    const deviceItem = e.target.classList.contains('device-item') ? e.target : e.target.closest('.device-item');
                    const checkbox = deviceItem?.querySelector('.device-checkbox');
                    if (checkbox && e.target !== checkbox && !e.target.classList.contains('bi-geo-alt')) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });

            // Select all in category
            document.querySelectorAll('.select-all-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category-id');
                    document.querySelectorAll(`.device-checkbox[data-category-id="${categoryId}"]`).forEach(checkbox => {
                        checkbox.checked = true;
                        checkbox.closest('.device-item').classList.add('selected');
                    });
                    updateCategoryCounts();
                });
            });

            // Deselect all in category
            document.querySelectorAll('.deselect-all-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category-id');
                    document.querySelectorAll(`.device-checkbox[data-category-id="${categoryId}"]`).forEach(checkbox => {
                        checkbox.checked = false;
                        checkbox.closest('.device-item').classList.remove('selected');
                    });
                    updateCategoryCounts();
                });
            });

            // Save access
            saveAccessBtn.addEventListener('click', function() {
                if (!currentUserId) {
                    Swal.fire('Error', 'Please select a user first', 'error');
                    return;
                }

                const selectedDevices = [];
                document.querySelectorAll('.device-checkbox:checked').forEach(checkbox => {
                    selectedDevices.push({
                        device_id: parseInt(checkbox.getAttribute('data-device-id')),
                        category_id: parseInt(checkbox.getAttribute('data-category-id'))
                    });
                });

                Swal.fire({
                    title: 'Save Access?',
                    text: `Update access for this user with ${selectedDevices.length} devices?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/admin/access/${currentUserId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                devices: selectedDevices
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Success', data.message || 'Access updated successfully', 'success');
                                loadUserAccess(currentUserId);
                            } else {
                                Swal.fire('Error', data.message || 'Failed to update access', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving access:', error);
                            Swal.fire('Error', 'Failed to save access', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection