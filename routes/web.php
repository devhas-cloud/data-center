<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminDeviceController;
use App\Http\Controllers\AdminHomeController;
use App\Http\Controllers\AdminParameterController;
use App\Http\Controllers\AdminSensorController;
use App\Http\Controllers\AdminAccessController;
use App\Http\Controllers\AdminGuidanceController;
use App\Http\Controllers\AdminLogsController;
use App\Http\Controllers\AdminSyslogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'checkLogin']);;  

Route::get('/login', function () {
    return redirect('/');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Home management
    Route::get('/admin/home', [AdminHomeController::class, 'manageHome'])->name('admin.home');
    Route::get('/admin/devices-data', [AdminHomeController::class, 'getAdminDevicesData'])->name('admin.devices_data');
    Route::get('/admin/device-latest-data/{deviceId}', [AdminHomeController::class, 'getLatestData'])->name('admin.device_latest_data');
    
    // Dashboard management
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/maps-dashboard/{deviceId}', [AdminDashboardController::class, 'getMapsDashboard'])->name('admin.maps_dashboard');
    Route::get('/admin/progress-bar/{deviceId}', [AdminDashboardController::class, 'progressBar'])->name('admin.progress_bar');
    Route::get('/admin/line-chart-data/{deviceId}', [AdminDashboardController::class, 'lineChartData'])->name('admin.line_chart_data');
    Route::get('/admin/bar-chart-data/{deviceId}', [AdminDashboardController::class, 'barChartData'])->name('admin.bar_chart_data');
    Route::get('/admin/wind-rose-data/{deviceId}', [AdminDashboardController::class, 'windRoseData'])->name('admin.wind_rose_data');
    Route::get('/admin/historical-data', [AdminDashboardController::class, 'getHistoricalData'])->name('admin.historical_data');
    Route::get('/admin/historical-data/export', [AdminDashboardController::class, 'exportHistoricalData'])->name('admin.historical_data.export');
    Route::get('/admin/historical-chart-data/{deviceId}', [AdminDashboardController::class, 'getHistoricalChartData'])->name('admin.historical_chart_data');
    

    // User management
    Route::get('/admin/manage-users', [AdminUserController::class, 'manageUsers'])->name('admin.manage_users');
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
    Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
    Route::post('/admin/users/{id}/reset-api-key', [AdminUserController::class, 'resetApiKey']);

    // Parameter management
    Route::get('/admin/manage-parameters', [AdminParameterController::class, 'manageParameters'])->name('admin.manage_parameters');
    Route::get('/admin/parameters', [AdminParameterController::class, 'index']);
    Route::post('/admin/parameters', [AdminParameterController::class, 'store']);
    Route::get('/admin/parameters/{id}', [AdminParameterController::class, 'show']);
    Route::put('/admin/parameters/{id}', [AdminParameterController::class, 'update']);
    Route::delete('/admin/parameters/{id}', [AdminParameterController::class, 'destroy']);

    // Category management
    Route::get('/admin/manage-categories', [AdminCategoryController::class, 'manageCategories'])->name('admin.manage_categories');
    Route::get('/admin/categories', [AdminCategoryController::class, 'index']);
    Route::post('/admin/categories', [AdminCategoryController::class, 'store']);
    Route::get('/admin/categories/{id}', [AdminCategoryController::class, 'show']);
    Route::put('/admin/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/admin/categories/{id}', [AdminCategoryController::class, 'destroy']);

    // Sensor management
    Route::get('/admin/manage-sensors', [AdminSensorController::class, 'manageSensors'])->name('admin.manage_sensors');
    Route::get('/admin/sensors', [AdminSensorController::class, 'index']);
    Route::post('/admin/sensors', [AdminSensorController::class, 'store']);
    Route::get('/admin/sensors/{id}', [AdminSensorController::class, 'show']);
    Route::put('/admin/sensors/{id}', [AdminSensorController::class, 'update']);
    Route::delete('/admin/sensors/{id}', [AdminSensorController::class, 'destroy']);

    // Device management
    Route::get('/admin/manage-devices', [AdminDeviceController::class, 'manageDevices'])->name('admin.manage_devices');
    Route::get('/admin/devices', [AdminDeviceController::class, 'index']);
    Route::post('/admin/devices', [AdminDeviceController::class, 'store']);
    Route::get('/admin/devices/{id}', [AdminDeviceController::class, 'show']);
    Route::put('/admin/devices/{id}', [AdminDeviceController::class, 'update']);
    Route::delete('/admin/devices/{id}', [AdminDeviceController::class, 'destroy']);

    // Access management
    Route::get('/admin/manage-access', [AdminAccessController::class, 'index'])->name('admin.manage_access');
    Route::get('/admin/access/{userId}', [AdminAccessController::class, 'getUserAccess']);
    Route::post('/admin/access/{userId}', [AdminAccessController::class, 'updateUserAccess']);

    // Syslog management
    Route::get('/admin/manage-syslog', [AdminSyslogController::class, 'manageSyslog'])->name('admin.manage_syslog');
    Route::get('/admin/syslog-data', [AdminSyslogController::class, 'index'])->name('admin.syslog_data');
    Route::get('/admin/syslog/add', [AdminSyslogController::class, 'showAddForm'])->name('admin.syslog_add');
    Route::post('/admin/syslog/store', [AdminSyslogController::class, 'store'])->name('admin.syslog_store');
    Route::get('/admin/syslog/view/{id}', [AdminSyslogController::class, 'show'])->name('admin.syslog_view');
    Route::get('/admin/syslog/edit/{id}', [AdminSyslogController::class, 'edit'])->name('admin.syslog_edit');
    Route::put('/admin/syslog/update/{id}', [AdminSyslogController::class, 'update'])->name('admin.syslog_update');
    Route::delete('/admin/syslog/delete/{id}', [AdminSyslogController::class, 'destroy'])->name('admin.syslog_delete');

    // Guidance management
    Route::get('/admin/manage-guidance', [AdminGuidanceController::class, 'index'])->name('admin.manage_guidance');
    Route::post('/admin/guidance', [AdminGuidanceController::class, 'store'])->name('admin.guidance_store');
    Route::get('/admin/guidance/{id}', [AdminGuidanceController::class, 'show'])->name('admin.guidance_show');
    Route::put('/admin/guidance/{id}', [AdminGuidanceController::class, 'update'])->name('admin.guidance_update');
    Route::delete('/admin/guidance/{id}', [AdminGuidanceController::class, 'destroy'])->name('admin.guidance_destroy');

    // Logs management
    Route::get('/admin/manage-logs', [AdminLogsController::class, 'manageLogs'])->name('admin.manage_logs');
    Route::get('/admin/logs-data', [AdminLogsController::class, 'index'])->name('admin.logs_data');
    Route::put('admin/logs/{id}', [AdminLogsController::class, 'update'])->name('admin.update_log');

    //count unread notifications
    Route::get('/admin/unread-notifications-count', [AdminController::class, 'getUnreadLogsCount'])->name('admin.unread_notifications_count');
    Route::post('/admin/mark-logs-read', [AdminController::class, 'markLogsAsRead'])->name('admin.mark_logs_read');

});

Route::middleware(['auth', 'role:user', 'single.session'])->group(function () {

    // User Home
    Route::get('/user/home', [UserController::class, 'home'])->name('user.home');
    Route::get('/user/devices-data', [UserController::class, 'getDeviceForHome'])->name('user.devices_data');
    
    // User Dashboard
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/maps-dashboard/{deviceId}', [UserController::class, 'getMapsDashboard'])->name('user.maps_dashboard');
    Route::get('/user/progress-bar/{deviceId}', [UserController::class, 'progressBar'])->name('user.progress_bar');
    Route::get('/user/line-chart-data/{deviceId}', [UserController::class, 'lineChartData'])->name('user.line_chart_data');
    Route::get('/user/bar-chart-data/{deviceId}', [UserController::class, 'barChartData'])->name('user.bar_chart_data');
    Route::get('/user/wind-rose-data/{deviceId}', [UserController::class, 'windRoseData'])->name('user.wind_rose_data');
    Route::get('/user/historical-data', [UserController::class, 'getHistoricalData'])->name('user.historical_data');
    Route::get('/user/historical-data/export', [UserController::class, 'exportHistoricalData'])->name('user.historical_data.export');
    Route::get('/user/historical-chart-data/{deviceId}', [UserController::class, 'getHistoricalChartData'])->name('user.historical_chart_data');
    
    Route::get('/user/device-info', [UserController::class, 'deviceInfo'])->name('user.device_info');
    Route::get('/user/device-info/{deviceId}', [UserController::class, 'getDeviceInfo'])->name('user.getDeviceInfo');
    Route::get('/user/syslog-detail/{id}', [UserController::class, 'getSyslogDetail'])->name('user.getSyslogDetail');

    // Device Report Routes
    Route::get('/user/device-report', [UserController::class, 'deviceReport'])->name('user.device_report');
    Route::get('/user/get-device-report', [UserController::class, 'getDeviceReport'])->name('user.get_device_report');
    Route::get('/user/get-device-report/{id}', [UserController::class, 'getDeviceReportById'])->name('user.get_device_report_by_id');
    Route::post('/user/save-device-report', [UserController::class, 'saveDeviceReport'])->name('user.save_device_report');
    Route::post('/user/update-device-report', [UserController::class, 'updateDeviceReport'])->name('user.update_device_report');
    Route::delete('/user/delete-device-report/{id}', [UserController::class, 'deleteDeviceReport'])->name('user.delete_device_report');

    // Report table data
    Route::get('/user/report-table-data', [UserController::class, 'getTableDeviceReport'])->name('user.report_table_data');

    // Export routes
    Route::get('/user/export-report-pdf', [UserController::class, 'exportReportPdf'])->name('user.export_report_pdf');
    Route::get('/user/export-report-excel', [UserController::class, 'exportReportExcel'])->name('user.export_report_excel');


    // User Settings
    Route::get('/user/settings', [UserController::class, 'settings'])->name('user.settings');
    Route::post('/user/settings/update-profile', [UserController::class, 'updateProfile'])->name('user.update_profile');
    Route::post('/user/settings/change-password', [UserController::class, 'changePassword'])->name('user.change_password');
    Route::post('/user/settings/update-parameter-alerts', [UserController::class, 'updateParameterAlerts'])->name('user.update_parameter_alerts');

    // Guidance viewing
    Route::get('/user/guidance', [UserController::class, 'getGuidance'])->name('user.get_guidance');

    // Logs viewing
    Route::get('/user/logs-data', [UserController::class, 'getLogsData'])->name('user.logs_data');
    Route::get('/user/user-devices', [UserController::class, 'getUserDevices'])->name('user.user_devices');
    Route::post('/user/mark-logs-read', [UserController::class, 'markLogsAsRead'])->name('user.mark_logs_read');

    //count unread notifications
    Route::get('/user/unread-notifications-count', [UserController::class, 'getUnreadLogsCount'])->name('user.unread_notifications_count');


});
