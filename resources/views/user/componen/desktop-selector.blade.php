 <div class="device-selector" id="deviceSelector">
            <div class="device-selector-toggle" id="deviceSelectorToggle">
                <i class="bi bi-cpu device-icon"></i>
                <span class="device-name">{{ isset($deviceCategories[0]['devices'][0]) ? $deviceCategories[0]['devices'][0]['device_id'] : 'Select Device' }}</span>
                <i class="bi bi-chevron-down dropdown-icon"></i>
            </div>

            <div class="device-selector-menu">
                @if(isset($deviceCategories) && count($deviceCategories) > 0)
                    @foreach($deviceCategories as $categoryIndex => $category)
                        <div class="device-group">
                            <div class="device-group-title">{{ $category['device_category'] }}</div>
                            @foreach($category['devices'] as $deviceIndex => $device)
                                <div class="device-option {{ $categoryIndex === 0 && $deviceIndex === 0 ? 'active' : '' }}" data-value="{{ $device['device_id'] }}">
                                    <span> {{ $device['device_name'] }} - ({{ $device['device_id'] }})</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="device-group">
                        <div class="text-center p-3 text-muted">
                            <i class="bi bi-info-circle"></i> No devices available
                        </div>
                    </div>
                @endif
            </div>
        </div>
