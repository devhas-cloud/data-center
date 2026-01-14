@extends('layout.admin')
@section('title', 'View Syslog')
@section('content')
<div class="card mt-5">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>View Syslog Detail</h4>
    </div>
    <div class="card-body">
        <!-- Header Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Header Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Device</label>
                        <p class="form-control-plaintext">{{ $syslogHeader->device->device_name ?? '-' }} ({{ $syslogHeader->device_id }})</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <p class="form-control-plaintext">{{ $syslogHeader->created_date }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <p class="form-control-plaintext">
                            @if($syslogHeader->category == 'maintenance')
                                <span class="badge bg-warning">Maintenance</span>
                            @elseif($syslogHeader->category == 'calibration')
                                <span class="badge bg-info">Calibration</span>
                            @elseif($syslogHeader->category == 'installation')
                                <span class="badge bg-success">Installation</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Created By</label>
                        <p class="form-control-plaintext">{{ $syslogHeader->user->name ?? '-' }}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Linked File</label>
                        <p class="form-control-plaintext">
                            @if($syslogHeader->linked_file)
                                <a href="/storage/{{ $syslogHeader->linked_file }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-pdf"></i> View PDF
                                </a>
                            @else
                                <span class="text-muted">No file attached</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Created At</label>
                        <p class="form-control-plaintext">{{ $syslogHeader->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Note</label>
                    <p class="form-control-plaintext">{{ $syslogHeader->note ?? '-' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Detail Section -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Detail Information</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="50">No</th>
                                <th width="300">Parameter</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($syslogHeader->details as $index => $detail)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $detail->parameter->parameter_label ?? $detail->parameter->parameter_name }}</td>
                                <td>{{ $detail->description ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No details available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.manage_syslog') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <div>
                <a href="{{ route('admin.syslog_edit', $syslogHeader->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
