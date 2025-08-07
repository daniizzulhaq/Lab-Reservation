<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LaboratoryController as AdminLaboratoryController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ReservationController as UserReservationController;
use App\Http\Controllers\User\LaboratoryController as UserLaboratoryController;
use App\Http\Controllers\Auth\CustomAuthController;
use Illuminate\Support\Facades\Route;

// Landing Page (Public)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Custom Auth Routes
Route::get('/login', [CustomAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomAuthController::class, 'login']);
Route::get('/register', [CustomAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [CustomAuthController::class, 'register']);
Route::post('/logout', [CustomAuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Profile Routes - ADDED FOR PROFILE MANAGEMENT
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Laboratory Management
        Route::resource('laboratories', AdminLaboratoryController::class);

        // Reservation Management
        Route::resource('reservations', AdminReservationController::class);
        Route::post('reservations/{reservation}/approve', [AdminReservationController::class, 'approve'])->name('reservations.approve');
        Route::post('reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');

        // User Management
        Route::resource('users', AdminUserController::class);

        // Reports Routes - FIXED ORDER AND METHODS
        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
        
        // Export routes - PUT SPECIFIC ROUTES BEFORE GENERIC ONES
        Route::get('reports/export/excel', [AdminReportController::class, 'exportExcel'])->name('reports.excel');
        Route::get('reports/export/pdf', [AdminReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export'); // Default to Excel
        
        // Alternative export routes (for backward compatibility)
        Route::get('reports/download/excel', [AdminReportController::class, 'exportExcel'])->name('reports.download.excel');
        Route::get('reports/download/pdf', [AdminReportController::class, 'exportPdf'])->name('reports.download.pdf');

        // API for charts and availability check
        Route::get('api/chart-data', [AdminDashboardController::class, 'getChartData'])->name('api.chart-data');
        Route::get('api/availability-check', [AdminReservationController::class, 'checkAvailability'])->name('api.availability-check');
    });

    // User Routes (Dosen & Mahasiswa)
    Route::middleware('role:dosen,mahasiswa')->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // Laboratory Viewing
        Route::get('laboratories', [UserLaboratoryController::class, 'index'])->name('user.laboratories.index');
        Route::get('laboratories/{laboratory}', [UserLaboratoryController::class, 'show'])->name('laboratories.show');
        Route::get('laboratories/{laboratory}/availability', [UserLaboratoryController::class, 'checkAvailability'])->name('laboratories.availability');

        // Reservation Management
        Route::resource('reservations', UserReservationController::class);
        Route::get('api/calendar-events', [UserReservationController::class, 'getCalendarEvents'])->name('api.calendar-events');
        Route::get('api/search-laboratories', [UserLaboratoryController::class, 'search'])->name('api.search-laboratories');
    });
});

// Additional Debug Routes (REMOVE IN PRODUCTION)
Route::middleware(['auth', 'role:admin'])->prefix('admin/debug')->name('admin.debug.')->group(function () {
    Route::get('routes', function () {
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        });
        
        return response()->json($routes->where('name', 'like', 'admin.reports%'));
    })->name('routes');
    
    Route::get('test-export', function () {
        try {
            $controller = app(\App\Http\Controllers\Admin\ReportController::class);
            return "ReportController loaded successfully";
        } catch (\Exception $e) {
            return "Error loading ReportController: " . $e->getMessage();
        }
    })->name('test.export');
});