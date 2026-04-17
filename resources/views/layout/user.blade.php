<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Center - @yield('title', 'Data Management')</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/icon.webp') }}" type="image/x-icon">
    <!-- Meta Tags -->
    <meta name="description" content="User Dashboard for Device Monitoring and Management">
    <meta name="keywords" content="User, Dashboard, Device Monitoring, Management">
    <meta name="author" content="PT HAS Environmental">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap-icons.css') }}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('assets/datatables/css/dataTables.bootstrap5.min.css') }}">

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/user-layout.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <!-- Header using Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid d-flex justify-content-between">
            <div class="d-flex align-items-center">

                <a class="align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('assets/img/icon.webp') }}" alt="Logo" height="60"
                        class="d-inline-block align-text-top">
                </a>
                &nbsp;&nbsp;

                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div class="header-right">

                <!-- Icon ebook pedoman -->
                <div class="position-relative me-3">
                    <button class="btn btn-link text-white position-relative p-0" id="ebookButton" title="Guidance">
                        <i class="bi bi-book fs-5"></i>
                    </button>
                </div>

                <!-- Icon Bantuan -->
                {{-- <div class="position-relative me-3">
                    <button class="btn btn-link text-white position-relative p-0" id="helpButton" title="Help">
                        <i class="bi bi-question-circle fs-5"></i>
                    </button>
                </div> --}}

                <!-- Notifications -->
                <div class="position-relative me-3">
                    <button class="btn btn-link text-white position-relative p-0" id="notificationButton">
                        <i class="bi bi-bell fs-5"></i>
                        <div id="notificationBadge" class="notification-badge" style="display: none;">0</div>
                    </button>
                </div>

                <!-- User Profile Dropdown -->
                <div class="user-profile-dropdown" id="userProfileDropdown">
                    <button class="user-profile-toggle" id="userProfileToggle">
                        <div class="me-2">
                            <span class="user-avatar">
                                <i class="bi bi-person-circle fs-4"></i>
                            </span>
                        </div>
                        <div class="user-info">
                            <p class="user-name">{{ Auth::user()->name }}</p>
                            <p class="user-role">{{ Auth::user()->level }}</p>
                        </div>
                        <i class="bi bi-chevron-down dropdown-icon"></i>
                    </button>

                    <div class="user-profile-menu">

                        <div class="dropdown-divider"></div>
                        <a href="{{ @route('logout') }}" class="dropdown-item">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- User Menu Navigation -->
    <nav class="user-menu" id="userMenu">
        <div class="nav-container">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link  {{ request()->is('user/home') ? 'active' : '' }}"
                        href="{{ @route('user.home') }}">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{ request()->is('user/dashboard') ? 'active' : '' }}"
                        href="{{ @route('user.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/device-info') ? 'active' : '' }} "
                        href="{{ @route('user.device_info') }}">
                        <i class="bi bi-cpu me-1"></i> Device Info
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/device-report') ? 'active' : '' }} "
                        href="{{ @route('user.device_report') }}">
                        <i class="bi bi-file-earmark-text me-1"></i> Report
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('user/settings') ? 'active' : '' }} "
                        href="{{ @route('user.settings') }}">
                        <i class="bi bi-gear me-1"></i> Settings
                    </a>
                </li>


            </ul>
        </div>

        <!-- Device Selector Dropdown -->
        @yield('desktop-selector')


    </nav>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <div class="mobile-nav-title">Menu</div>
            <button class="mobile-nav-close" id="mobileNavClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="mobile-nav-menu">
            <a href="{{ @route('user.home') }}"
                class="mobile-nav-item {{ request()->is('user/home') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i> Home
            </a>
            <a href="{{ @route('user.dashboard') }}"
                class="mobile-nav-item {{ request()->is('user/dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ @route('user.device_info') }}"
                class="mobile-nav-item {{ request()->is('user/device-info') ? 'active' : '' }}">
                <i class="bi bi-cpu"></i> Device Info
            </a>
            <a href="{{ @route('user.device_report') }}"
                class="mobile-nav-item {{ request()->is('user/device-report') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Report
            </a>
            <a href="{{ @route('user.settings') }}"
                class="mobile-nav-item {{ request()->is('user/settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>

        <!-- Mobile Device Selector -->
        @yield('mobile-selector')

    </div>

    <!-- Main Content -->
    <main class="main-content">

        @yield('content')

    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container text-center">
            <div class="container text-center">
                <p class="mb-0">
                    Copyright &copy; {{ date('Y') }} PT HAS Environmental.

                </p>
            </div>
        </div>
    </footer>

    <!-- guidance Modal -->
    <div class="modal fade" id="guidanceModal" tabindex="-1" aria-labelledby="guidanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidanceModalLabel">
                        <i class="bi bi-book"></i> Guidance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="accordion" id="guidanceAccordion">

                        <!-- Dynamic content will be loaded here via JavaScript -->

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">
                        <i class="bi bi-question-circle me-2"></i> Help & Documentation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="helpAccordion">
                        <!-- Getting Started -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="bi bi-play-circle me-2"></i> Getting Started
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show"
                                aria-labelledby="headingOne" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <h6>Welcome to the System!</h6>
                                    <p>This guide will help you get started with using the dashboard and monitoring your
                                        devices.</p>
                                    <ul>
                                        <li>Navigate through different sections using the menu bar</li>
                                        <li>Select a device from the device selector dropdown</li>
                                        <li>View real-time data and device status on the dashboard</li>
                                        <li>Check notifications for important alerts</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Device Management -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="bi bi-cpu me-2"></i> Device Management
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <h6>Managing Your Devices</h6>
                                    <p>Learn how to monitor and manage your connected devices:</p>
                                    <ul>
                                        <li><strong>View Device Info:</strong> Click on a device to see detailed
                                            information</li>
                                        <li><strong>Device Status:</strong> Green indicator means online, Red means
                                            offline</li>
                                        <li><strong>Switch Devices:</strong> Use the device selector to switch between
                                            different devices</li>
                                        <li><strong>Device Categories:</strong> Devices are grouped by categories
                                            (Sparing, Boom, Pump, etc.)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Dashboard Features -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false"
                                    aria-controls="collapseThree">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard Features
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse"
                                aria-labelledby="headingThree" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <h6>Dashboard Overview</h6>
                                    <p>The dashboard provides real-time monitoring and analytics:</p>
                                    <ul>
                                        <li><strong>Maps:</strong> View device locations on an interactive map</li>
                                        <li><strong>Real-time Data:</strong> Monitor sensor readings and device
                                            parameters</li>
                                        <li><strong>Status Cards:</strong> Quick overview of device health and
                                            performance</li>
                                        <li><strong>Charts & Graphs:</strong> Visualize historical data and trends</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications & Alerts -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false"
                                    aria-controls="collapseFour">
                                    <i class="bi bi-bell me-2"></i> Notifications & Alerts
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <h6>Understanding Notifications</h6>
                                    <p>Stay informed about device events and alerts:</p>
                                    <ul>
                                        <li><strong>View Logs:</strong> Click the notification bell to view device logs
                                        </li>
                                        <li><strong>Filter Logs:</strong> Use filters to find specific events by date,
                                            device, or category</li>
                                        <li><strong>Status Types:</strong>
                                            <ul>
                                                <li>Error - Critical issues requiring immediate attention</li>
                                                <li>Warning - Potential problems to monitor</li>
                                                <li>Info - General information and updates</li>
                                                <li>Success - Successful operations and confirmations</li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Reports -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFive" aria-expanded="false"
                                    aria-controls="collapseFive">
                                    <i class="bi bi-file-earmark-text me-2"></i> Reports
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <h6>Generate and View Reports</h6>
                                    <p>Access detailed reports and analytics:</p>
                                    <ul>
                                        <li>Generate custom reports for specific date ranges</li>
                                        <li>Export reports in various formats (PDF, Excel, CSV)</li>
                                        <li>View historical data and performance metrics</li>
                                        <li>Schedule automated reports</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                    <i class="bi bi-patch-question me-2"></i> FAQ
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix"
                                data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <h6>Frequently Asked Questions</h6>
                                    <div class="mb-3">
                                        <strong>Q: How often does the data refresh?</strong>
                                        <p>A: The dashboard updates automatically every 60 seconds to show real-time
                                            data.</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Q: What do the different marker colors mean?</strong>
                                        <p>A: Green = Online with recent data, Blue = Data older than 3 hours, Orange =
                                            Data older than 24 hours, Red = Data older than 3 days, Black = Data older
                                            than 7 days.</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Q: How can I contact support?</strong>
                                        <p>A: You can contact support through the profile menu or email
                                            support@example.com</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade notification-modal" id="notificationModal" tabindex="-1"
        aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">
                        <i class="bi bi-bell me-2"></i> Log Device
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="filter-section">
                        <h6 class="filter-title">Filter Data</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="deviceCode" class="form-label">Device</label>
                                <select class="form-select" id="deviceCode">
                                    <option value="">All Device</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category">
                                    <option value="">All Category</option>
                                    <option value="connection">Connection</option>
                                    <option value="network">Network</option>
                                    <option value="sensor">Sensor</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="startDateTime" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDateTime">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="endDateTime" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDateTime">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <div class="category-buttons">
                                    <button class="category-btn active" data-status="all">ALL</button>
                                    <button class="category-btn" data-status="unaction">Unaction</button>
                                    <button class="category-btn" data-status="progress">On Progress</button>
                                    <button class="category-btn" data-status="solve">Solve</button>
                                    <button class="category-btn" data-status="unsolve">Unsolve</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" id="resetFilter">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                    </button>
                                    <button type="button" class="btn btn-primary" id="applyFilter">
                                        <i class="bi bi-funnel me-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="logDeviceTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 10%">Name</th>
                                    <th style="width: 15%">Date</th>
                                    <th style="width: 10%">Category</th>
                                    <th style="width: 30%">Message</th>
                                    <th style="width: 10%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- jQuery -->
    <script src="{{ asset('assets/jquery/js/jquery-3.7.0.min.js') }}"></script>

    <!-- DataTables JS -->
    <script src="{{ asset('assets/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/datatables/js/dataTables.bootstrap5.min.js') }}"></script>


    <!-- Custom JavaScript -->
    <script src="{{ asset('assets/js/user-layout.js') }}"></script>

    <!-- Guidance JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const assetBaseUrl = "{{ asset('') }}";

            const ebookButton = document.getElementById("ebookButton");
            const guidanceModal = new bootstrap.Modal(document.getElementById("guidanceModal"));

            ebookButton.addEventListener("click", function() {
                $.ajax({
                    url: "{{ route('user.get_guidance') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        const guidanceAccordion = document.getElementById("guidanceAccordion");
                        guidanceAccordion.innerHTML = "";

                        if (response.data && response.data.length > 0) {
                            response.data.forEach((item, index) => {
                                const imageUrl = item.image_path ?
                                    assetBaseUrl + item.image_path :
                                    assetBaseUrl +
                                    "storage/guidance_images/default.png";

                                const content = item.content || item.description ||
                                    'No content available';

                                const accordionItem = `
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading${index}">
                                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse${index}"
                                        aria-expanded="${index === 0 ? 'true' : 'false'}"
                                        aria-controls="collapse${index}">
                                        <i class="bi bi-play-circle me-2"></i> ${item.title}
                                    </button>
                                </h2>
                                <div id="collapse${index}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}">
                                    <div class="accordion-body">
                                        ${item.image_path ? `<img src="${imageUrl}" class="img-fluid mb-3" alt="${item.title}" style="max-width: 100%; height: auto;">` : ''}
                                        <p>${content}</p>
                                        ${item.link_path ? `<a href="${item.link_path}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-link-45deg"></i> Visit Link</a>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                                guidanceAccordion.insertAdjacentHTML("beforeend",
                                    accordionItem);
                            });
                        } else {
                            guidanceAccordion.innerHTML =
                                '<div class="alert alert-info">No guidance data available.</div>';
                        }

                        guidanceModal.show();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading guidance:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load guidance data'
                        });
                    }
                });
            });


            //help Modal
            // const helpButton = document.getElementById("helpButton");
            // const helpModal = new bootstrap.Modal(document.getElementById("helpModal"));
            // helpButton.addEventListener("click", function() {
            //     helpModal.show();
            // });




        });
    </script>

    <!-- Notification JavaScript -->
    <script>
        // ambil jumlah notif user yang belum di baca
        document.addEventListener("DOMContentLoaded", function() {

            function fetchUnreadNotificationsCount() {
                $.ajax({
                    url: "{{ route('user.unread_notifications_count') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        const notificationBadge = document.getElementById("notificationBadge");
                        if (response.unread_count > 0) {
                            notificationBadge.style.display = "block";
                            notificationBadge.textContent = response.unread_count;
                        } else {
                            notificationBadge.style.display = "none";
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching unread notifications count:', error);
                    }
                });
            }

            //cek setiap 1 menit
            setInterval(fetchUnreadNotificationsCount, 60000); // 60000 ms = 1 menit
            //panggil saat halaman dimuat
            fetchUnreadNotificationsCount();



            // Notification Modal 
            const notificationButton = document.getElementById("notificationButton");
            const notificationModal = new bootstrap.Modal(
                document.getElementById("notificationModal")
            );
            let table; // Declare table variable outside

            // Fetch user devices for filter
            function loadUserDevices() {
                $.ajax({
                    url: "{{ route('user.user_devices') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            const deviceSelect = document.getElementById("deviceCode");
                            deviceSelect.innerHTML = '<option value="">All Device</option>';
                            response.data.forEach(device => {
                                const option = document.createElement('option');
                                option.value = device.device_id;
                                option.textContent =
                                    `${device.device_id} - ${device.device_name}`;
                                deviceSelect.appendChild(option);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading devices:', error);
                    }
                });
            }

            // Load logs data from backend
            function loadLogsData(filters = {}) {
                const params = new URLSearchParams();
                if (filters.device_id) params.append('device_id', filters.device_id);
                if (filters.category) params.append('category', filters.category);
                if (filters.start_date) params.append('start_date', filters.start_date);
                if (filters.end_date) params.append('end_date', filters.end_date);
                if (filters.status) params.append('status', filters.status);

                $.ajax({
                    url: "{{ route('user.logs_data') }}" + (params.toString() ? '?' + params.toString() :
                        ''),
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            const tableData = response.data.map((log, index) => ({
                                no: index + 1,
                                device_id: log.device_id,
                                device_name: log.device_name,
                                datetime: log.datetime,
                                category: log.category,
                                message: log.message,
                                status: log.status,
                            }));

                            if (table) {
                                table.clear();
                                table.rows.add(tableData);
                                table.draw();
                            } else {
                                initializeDataTable(tableData);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading logs:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load logs data'
                        });
                    }
                });
            }

            // Initialize DataTable
            function initializeDataTable(data) {
                table = $("#logDeviceTable").DataTable({
                    data: data,
                    columns: [{
                            data: "no",
                        },
                        {
                            data: "device_id",
                        },
                        {
                            data: "device_name",
                        },
                        {
                            data: "datetime",
                        },
                        {
                            data: "category",
                        },
                        {
                            data: "message",
                        },
                        {
                            data: "status",
                        },
                    ],
                    responsive: true,
                    paging: true,
                    pageLength: 10,
                    searching: false,
                    info: true,
                    ordering: true,
                    order: [
                        [3, 'desc']
                    ], // Order by datetime descending
                    columnDefs: [{
                            targets: 4, // Category column
                            render: function(data, type, row) {
                                let badgeClass = "";
                                switch (data.toLowerCase()) {
                                    case "connection":
                                        badgeClass = "bg-danger";
                                        break;
                                    case "network":
                                        badgeClass = "bg-warning";
                                        break;
                                    case "sensor":
                                        badgeClass = "bg-info";
                                        break;
                                    default:
                                        badgeClass = "bg-secondary";
                                }
                                return `<span class="badge ${badgeClass}">${data.toUpperCase()}</span>`;
                            },
                        },
                        {
                            targets: 6, // Status column
                            render: function(data, type, row) {
                                let statusClass = "";
                                switch (data.toLowerCase()) {
                                    case "unaction":
                                        statusClass = "status-unaction";
                                        break;
                                    case "progress":
                                        statusClass = "status-onprogress";
                                        break;
                                    case "solve":
                                        statusClass = "status-solve";
                                        break;
                                    case "unsolve":
                                        statusClass = "status-unsolve";
                                        break;
                                    default:
                                        statusClass = "bg-secondary";
                                }
                                return `<span class="status-badge ${statusClass}">${data.toUpperCase()}</span>`;
                            },
                        },
                    ],
                    autoWidth: false,
                });
            }

            // Open modal and load data
            notificationButton.addEventListener("click", function() {
                loadUserDevices();
                loadLogsData();
                notificationModal.show();
            });

            // Mark logs as read when modal closes
            document.getElementById("notificationModal").addEventListener("hidden.bs.modal", function() {
                $.ajax({
                    url: "{{ route('user.mark_logs_read') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update notification badge
                            fetchUnreadNotificationsCount();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error marking logs as read:', error);
                    }
                });
            });

            // Category buttons
            const categoryButtons = document.querySelectorAll(".category-btn");
            categoryButtons.forEach((button) => {
                button.addEventListener("click", function() {
                    // Remove active class from all buttons
                    categoryButtons.forEach((btn) => btn.classList.remove("active"));

                    // Add active class to clicked button
                    this.classList.add("active");

                    // Get current filters
                    const status = this.getAttribute("data-status");
                    const filters = {
                        device_id: document.getElementById("deviceCode").value,
                        category: document.getElementById("category").value,
                        start_date: document.getElementById("startDateTime").value,
                        end_date: document.getElementById("endDateTime").value,
                        status: status === "all" ? "" : status,
                    };

                    loadLogsData(filters);
                });
            });

            // Apply filter button
            document
                .getElementById("applyFilter")
                .addEventListener("click", function() {
                    const filters = {
                        device_id: document.getElementById("deviceCode").value,
                        category: document.getElementById("category").value,
                        start_date: document.getElementById("startDateTime").value,
                        end_date: document.getElementById("endDateTime").value,
                        status: document.querySelector(".category-btn.active").getAttribute("data-status"),
                    };

                    if (filters.status === "all") {
                        filters.status = "";
                    }

                    loadLogsData(filters);
                });

            // Reset filter button
            document
                .getElementById("resetFilter")
                .addEventListener("click", function() {
                    // Reset form inputs
                    document.getElementById("deviceCode").value = "";
                    document.getElementById("category").value = "";
                    document.getElementById("startDateTime").value = "";
                    document.getElementById("endDateTime").value = "";

                    // Reset category buttons
                    categoryButtons.forEach((btn) => btn.classList.remove("active"));
                    document
                        .querySelector('.category-btn[data-status="all"]')
                        .classList.add("active");

                    // Reload data without filters
                    loadLogsData();
                });

            // Auto-hide alerts
            const alerts = document.querySelectorAll(".alert");
            alerts.forEach((alert) => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });




        });
    </script>

    @yield('script')
</body>

</html>
