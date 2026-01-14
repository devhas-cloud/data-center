@extends('layout.user')
@section('title', 'User Settings')
@section('content')
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-12">
                <!-- Profile Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header" style="background-color: #DAD7D7">
                        <!-- You can add buttons or links here for adding new reports  and position right -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Profile</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="profileForm">
                            <input type="hidden" name="id" value="{{ $profile->id }}">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="{{ $profile->username }}" disabled>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ $profile->name }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $profile->email }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Leave blank to keep current password">
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    Update Profile
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>


            <div class="col-12">
                <!-- Parameter Indicator Alert Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header" style="background-color: #DAD7D7">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Parameter Indicator Alert</h5>
                        </div>
                    </div>
                    <div class="card-body">

                        @foreach ($devices as $device)
                            @if ($device->sensors && $device->sensors->count() > 0)
                                <form id="parameterAlertForm_{{ $device->id }}" class="parameterAlertForm" data-device-id="{{ $device->id }}" data-device-name="{{ $device->device_name }}">
                                    <div class="mb-4">
                                        <h6 class="mb-3 text-primary">{{ $device->device_name }} ({{ $device->device_id }})
                                        </h6>

                                        <div class="row">
                                            @foreach ($device->sensors->where('status', 'active') as $sensor)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <input type="hidden"
                                                                name="sensors[{{ $sensor->id }}][sensor_id]"
                                                                value="{{ $sensor->id }}">

                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold">Parameter</label>
                                                                <input type="text" class="form-control parameter-label"
                                                                    value="{{ $sensor->parameter->parameter_label ?? $sensor->parameter_name }}"
                                                                    disabled>
                                                            </div>

                                                            <div class="mb-0">
                                                                <label for="alert_{{ $sensor->id }}"
                                                                    class="form-label">Indicator Alert Value</label>

                                                                <input type="number" class="form-control parameter-alert-input"
                                                                    id="alert_{{ $sensor->id }}"
                                                                    name="sensors[{{ $sensor->id }}][parameter_indicator_alert]"
                                                                    value="{{ $sensor->parameter_indicator_alert }}"
                                                                    data-min="{{ $sensor->parameter_indicator_min }}"
                                                                    data-max="{{ $sensor->parameter_indicator_max }}"
                                                                    placeholder="Enter alert threshold value" @if(!$accessCrud) disabled @endif>
                                                                @if ($sensor->parameter_indicator_min || $sensor->parameter_indicator_max)
                                                                    <small class="text-muted">
                                                                        Range:
                                                                        {{ $sensor->parameter_indicator_min ?? 'N/A' }} -
                                                                        {{ $sensor->parameter_indicator_max ?? 'N/A' }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($accessCrud)
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                Update {{ $device->device_name }}
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </form>
                            @endif
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Profile Form Submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    id: $('#profileForm input[name="id"]').val(),
                    name: $('#name').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    _token: '{{ csrf_token() }}'
                };

                //Swal confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to update your profile.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('user.update_profile') }}',
                            method: 'POST',
                            data: formData,
                            beforeSend: function() {
                                $('#profileForm button[type="submit"]').prop('disabled', true)
                                    .html(
                                        '<span class="spinner-border spinner-border-sm me-2"></span>Updating...'
                                    );
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                });
                                // Clear password field
                                $('#password').val('');
                            },
                            error: function(xhr) {
                                let errorMessage = 'Failed to update profile.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: errorMessage
                                });
                            },
                            complete: function() {
                                $('#profileForm button[type="submit"]').prop('disabled', false)
                                    .html('Update Profile');
                            }
                        });
                    }
                });
            
            
            });

            // Parameter Alert Form Submission with Validation (Multiple Forms)
            $('.parameterAlertForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const deviceId = $form.data('device-id');
                const deviceName = $form.data('device-name');

                // Client-side validation for this specific form
                let validationErrors = [];
                let isValid = true;

                $form.find('.parameter-alert-input').each(function() {
                    const $input = $(this);
                    const value = parseFloat($input.val());
                    const min = parseFloat($input.data('min'));
                    const max = parseFloat($input.data('max'));
                    const parameterName = $input.closest('.card-body').find('.parameter-label').val();

                    // Only validate if value is provided
                    if ($input.val() !== '' && !isNaN(value)) {
                        if (!isNaN(min) && value < min) {
                            validationErrors.push(
                                `<strong>${parameterName}:</strong> Alert value (${value}) cannot be less than minimum value (${min})`
                            );
                            $input.addClass('is-invalid');
                            isValid = false;
                        } else if (!isNaN(max) && value > max) {
                            validationErrors.push(
                                `<strong>${parameterName}:</strong> Alert value (${value}) cannot be greater than maximum value (${max})`
                            );
                            $input.addClass('is-invalid');
                            isValid = false;
                        } else {
                            $input.removeClass('is-invalid');
                        }
                    } else {
                        $input.removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error!',
                        html: validationErrors.join('<br><br>'),
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Proceed with AJAX submission
                const formData = $form.serialize() + '&_token={{ csrf_token() }}';
                const $submitBtn = $form.find('button[type="submit"]');
                const originalBtnText = $submitBtn.html();

                $.ajax({
                    url: '{{ route('user.update_parameter_alerts') }}',
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $submitBtn.prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
                    },
                    success: function(response) {
                        if (response.success) {
                            let message = response.message;

                            // Show errors if any (as warnings)
                            if (response.errors && response.errors.length > 0) {
                                message += '<br><br><small class="text-warning">Warnings:<br>' +
                                    response.errors.join('<br>') + '</small>';
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: message,
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to update parameter alerts.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage += '<br><br>' + xhr.responseJSON.errors.join('<br>');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage
                        });
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });

            // Real-time validation on input change
            $('.parameter-alert-input').on('input', function() {
                const $input = $(this);
                const value = parseFloat($input.val());
                const min = parseFloat($input.data('min'));
                const max = parseFloat($input.data('max'));

                if ($input.val() !== '' && !isNaN(value)) {
                    if ((!isNaN(min) && value < min) || (!isNaN(max) && value > max)) {
                        $input.addClass('is-invalid');
                    } else {
                        $input.removeClass('is-invalid');
                    }
                } else {
                    $input.removeClass('is-invalid');
                }
            });
        });
    </script>
@endsection
