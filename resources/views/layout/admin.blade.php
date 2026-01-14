<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Admin Dashboard | @yield('title')</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/icon.webp') }}" type="image/x-icon">
    <!-- Meta Tags -->
    <meta name="description" content="Admin Dashboard for Data Center Management">
    <meta name="keywords" content="Admin, Dashboard, Data Center, Management">
    <meta name="author" content="PT HAS Environmental">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">

        

    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #212529;
            --sidebar-hover: #343a40;
            --sidebar-active: #0d6efd;
            --header-height: 60px;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header Styles */
        .navbar-custom {
            background: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
            height: var(--header-height);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            height: calc(100vh - var(--header-height));
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar .sidebar-header {
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.1);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar .sidebar-menu {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .sidebar .sidebar-menu li {
            position: relative;
        }

        .sidebar .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar .sidebar-menu li a:hover {
            background-color: var(--sidebar-hover);
            color: white;
        }

        .sidebar .sidebar-menu li a.active {
            background-color: var(--sidebar-active);
            color: white;
            border-left: 3px solid white;
        }

        .sidebar .sidebar-menu li a i {
            font-size: 1.2rem;
            margin-right: 10px;
            width: 25px;
            text-align: center;
        }

        .sidebar.collapsed .sidebar-menu li a span {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu li a i {
            margin-right: 0;
        }

        .sidebar .sidebar-submenu {
            list-style: none;
            padding-left: 20px;
            background-color: rgba(0, 0, 0, 0.1);
            display: none;
        }

        .sidebar .sidebar-menu li.active .sidebar-submenu {
            display: block;
        }

        .sidebar .sidebar-submenu li a {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
            min-height: calc(100vh - var(--header-height) - 40px);
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        /* Dashboard Cards */
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card .card-header {
            background: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
            color: white;
            font-weight: 600;
            border: none;
        }

        .dashboard-card .card-body {
            padding: 20px;
        }

        .dashboard-card .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .dashboard-card.primary .card-icon {
            color: #0d6efd;
        }

        .dashboard-card.success .card-icon {
            color: #198754;
        }

        .dashboard-card.warning .card-icon {
            color: #ffc107;
        }

        .dashboard-card.danger .card-icon {
            color: #dc3545;
        }

        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .table thead {
            background-color: #f8f9fa;
        }

        .table th {
            border: none;
            font-weight: 600;
            color: #495057;
        }

        .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(1, 179, 188, 0.1);
        }

        /* Footer */
        .footer-custom {
            background: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
            color: white;
            padding: 0.5rem 0;
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }

            .sidebar .sidebar-menu li a span {
                display: none;
            }

            .sidebar .sidebar-menu li a i {
                margin-right: 0;
            }

            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }
        }

        /* Toggle Button */
        .toggle-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            color: white;
        }

        .user-profile img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-profile .user-name {
            font-weight: 500;
        }

        .user-profile .user-role {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
    </style>


</head>

<body>
    <!-- Header using Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid">
            

            <div class="d-flex align-items-center">
                
                 <a class="align-items-center" href="{{ @route('admin.dashboard') }}">
                     <img src="{{ asset('assets/img/icon.webp') }}" alt="Logo" height="60" class="d-inline-block align-text-top">
                 </a>
                 &nbsp;&nbsp;

                <button class="toggle-sidebar me-3" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            </div>

            <div class="ms-auto d-flex align-items-center">
                <!-- Notifications -->
                <div class="position-relative me-3">
                    <button class="btn btn-link text-white position-relative" id="notificationButton" title="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="notification-badge" style="display: none;"></span>
                    </button>
                </div>

                <!-- User Profile -->
                <div class="user-profile">
                    <div class="me-2">
                        <i class="bi bi-person-circle fs-2"></i>
                    </div>
                    <div>
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">{{ ucfirst(Auth::user()->level) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Menu Admin</span>
            <button class="btn btn-sm text-white d-lg-none" id="closeSidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ @route('admin.home') }}"
                    class="{{ request()->is('admin/home') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i>
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="{{ @route('admin.dashboard') }}"
                    class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ @route('admin.manage_users') }}"
                    class="{{ request()->is('admin/manage-users') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li>

            <li>
                <a href="{{ @route('admin.manage_categories') }}"
                    class="{{ request()->is('admin/manage-categories') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
            </li>

            <li>
                <a href="{{ @route('admin.manage_parameters') }}"
                    class="{{ request()->is('admin/manage-parameters') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    <span>Parameter</span>
                </a>
            </li>

            <li>
                <a href="{{ @route('admin.manage_devices') }}"
                    class="{{ request()->is('admin/manage-devices') ? 'active' : '' }}">
                    <i class="bi bi-device-hdd"></i>
                    <span>Devices</span>
                </a>
            </li>

            <li>
                <a href="{{ @route('admin.manage_sensors') }}"
                    class="{{ request()->is('admin/manage-sensors') ? 'active' : '' }}">
                    <i class="bi bi-hdd-network"></i>
                    <span>Sensors</span>
                </a>
            </li>

           
            <li>
                <a href="{{ @route('admin.manage_guidance') }}"
                    class="{{ request()->is('admin/manage-guidance') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Guidance</span>
                </a>
            </li>

            <li>
                <a href="{{ @route('admin.manage_logs') }}"
                    class="{{ request()->is('admin/manage-logs') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i>
                    <span>Logs</span>
                </a>
            </li>
            

            <li>
                <a href="{{ @route('logout') }}">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </aside>


    <!-- Main Content -->
    <main class="main-content" id="mainContent">

        @yield('content')

    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container text-center">
            <p class="mb-0">
               Copyright &copy; {{ date('Y') }} PT HAS Environmental.

            </p>
        </div>
    </footer>



    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Leaflet -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <!-- Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    <!-- Plotly -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>


    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Sidebar
            const toggleSidebar = document.getElementById('toggleSidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');

                // For mobile
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                }
            });

            closeSidebar.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                }
            });

            // Handle submenu
            const menuItems = document.querySelectorAll('.sidebar-menu > li');
            menuItems.forEach(item => {
                const link = item.querySelector('a');
                const submenu = item.querySelector('.sidebar-submenu');

                if (submenu) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        item.classList.toggle('active');
                    });
                }
            });

            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>


    <!-- count unread logs for admin -->
    <script>
        $(document).ready(function() {
            function fetchUnreadLogsCount() {
                $.ajax({
                    url: "{{ route('admin.unread_notifications_count') }}",
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const count = response.unread_count;
                            if (count > 0) {
                                $('.notification-badge').text(count).show();
                            } else {
                                $('.notification-badge').hide();
                            }
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch unread logs count.');
                    }
                });
            }   
            // Initial fetch
            fetchUnreadLogsCount();
            // Fetch every 60 seconds
            setInterval(fetchUnreadLogsCount, 60000);


            // Mark logs as read when notification and redirect to logs page
            $('#notificationButton').on('click', function() {
                $.ajax({
                    url: "{{ route('admin.mark_logs_read') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Redirect to logs page
                            window.location.href = "{{ route('admin.manage_logs') }}";
                        }
                    },
                    error: function() {
                        console.error('Failed to mark logs as read.');
                    }
                });
            });


        });


    </script>

    
    @yield('script')
</body>

</html>
