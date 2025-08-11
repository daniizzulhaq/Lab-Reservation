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
use App\Http\Controllers\User\NotificationController as UserNotificationController;
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

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // API Routes - MOVE BEFORE RESOURCE ROUTES TO PREVENT CONFLICTS
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('chart-data', [AdminDashboardController::class, 'getChartData'])->name('chart-data');
            Route::get('calendar-events', [AdminDashboardController::class, 'calendarEvents'])->name('calendar-events');
            Route::get('availability-check', [AdminReservationController::class, 'checkAvailability'])->name('availability-check');
        });

        // Laboratory Management
        Route::resource('laboratories', AdminLaboratoryController::class);

        // Reservation Management
        Route::resource('reservations', AdminReservationController::class);
        Route::post('reservations/{reservation}/approve', [AdminReservationController::class, 'approve'])->name('reservations.approve');
        Route::post('reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('reservations.reject');

        // User Management
        Route::resource('users', AdminUserController::class);

        // Reports Routes - FIXED: Added missing routes that match the error
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('index');
            
            // Direct export routes (matching the error message)
            Route::get('excel', [AdminReportController::class, 'exportExcel'])->name('excel');
            Route::get('pdf', [AdminReportController::class, 'exportPdf'])->name('pdf');
            
            // Alternative export routes with explicit export prefix
            Route::get('export/excel', [AdminReportController::class, 'exportExcel'])->name('export.excel');
            Route::get('export/pdf', [AdminReportController::class, 'exportPdf'])->name('export.pdf');
            
            // Download routes
            Route::get('download/excel', [AdminReportController::class, 'exportExcel'])->name('download.excel');
            Route::get('download/pdf', [AdminReportController::class, 'exportPdf'])->name('download.pdf');
            
            // Generic export route (defaults to Excel)
            Route::get('export', [AdminReportController::class, 'export'])->name('export');
        });
    });

    // User Routes (Dosen & Mahasiswa) - FIXED: Use correct role names
    Route::middleware('role:dosen,mahasiswa,user')->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // API Routes - MOVE BEFORE RESOURCE ROUTES
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('dashboard-calendar-events', [UserDashboardController::class, 'calendarEvents'])->name('dashboard-calendar-events');
            Route::get('calendar-events', [UserReservationController::class, 'getCalendarEvents'])->name('calendar-events');
            Route::get('search-laboratories', [UserLaboratoryController::class, 'search'])->name('search-laboratories');
            Route::post('check-availability', [UserDashboardController::class, 'checkAvailability'])->name('check-availability');
            
            // Notification API routes
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('unread-count', [UserNotificationController::class, 'getUnreadCount'])->name('unread-count');
                Route::get('latest', [UserNotificationController::class, 'getLatestNotifications'])->name('latest');
            });
        });

        // Laboratory Viewing
        Route::get('laboratories', [UserLaboratoryController::class, 'index'])->name('laboratories.index');
        Route::get('laboratories/{laboratory}', [UserLaboratoryController::class, 'show'])->name('laboratories.show');
        Route::get('laboratories/{laboratory}/availability', [UserLaboratoryController::class, 'checkAvailability'])->name('laboratories.availability');

        // Reservation Management
        Route::resource('reservations', UserReservationController::class);

        // Notification Management
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [UserNotificationController::class, 'index'])->name('index');
            Route::get('/{id}', [UserNotificationController::class, 'show'])->name('show');
            Route::post('/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('readAll');
            Route::delete('/{id}', [UserNotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/read/all', [UserNotificationController::class, 'deleteAllRead'])->name('deleteAllRead');
        });
    });
});

// Fallback route untuk redirect berdasarkan role setelah login
Route::middleware('auth')->get('/redirect', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif (in_array($user->role, ['dosen', 'mahasiswa', 'user'])) {
        return redirect()->route('user.dashboard');
    }
    
    return redirect()->route('home');
})->name('role.redirect');

// Debug Routes (REMOVE IN PRODUCTION)
if (config('app.debug')) {
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
            
            return response()->json($routes->sortBy('uri')->values());
        })->name('routes');
        
        Route::get('user-info', function () {
            return response()->json([
                'user' => auth()->user(),
                'role' => auth()->user()->role,
                'permissions' => auth()->user()->getAllPermissions() ?? 'No permissions package'
            ]);
        })->name('user.info');
        
        Route::get('test-export', function () {
            try {
                $controller = app(\App\Http\Controllers\Admin\ReportController::class);
                return "ReportController loaded successfully. Available methods: " . 
                       implode(', ', get_class_methods($controller));
            } catch (\Exception $e) {
                return "Error loading ReportController: " . $e->getMessage();
            }
        })->name('test.export');
    });
}