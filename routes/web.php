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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page (Public)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [CustomAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomAuthController::class, 'login']);
    Route::get('/register', [CustomAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomAuthController::class, 'register']);
});

Route::post('/logout', [CustomAuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    
    // Home Route
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Role Redirect Route
    Route::get('/redirect', function () {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (in_array($user->role, ['dosen', 'mahasiswa', 'user'])) {
            return redirect()->route('user.dashboard');
        }
        
        return redirect()->route('home');
    })->name('role.redirect');

    // Profile Routes (Common for all authenticated users)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // API Routes (Must be before resource routes)
        Route::prefix('api')->name('api.')->group(function () {
            // Calendar and Dashboard APIs - FIXED ROUTES
            Route::get('/calendar-events', [AdminDashboardController::class, 'calendarEvents'])->name('calendar.events');
            Route::get('/chart-data', [AdminDashboardController::class, 'getChartData'])->name('chart.data');
            Route::get('/reservation-details/{id}', [AdminDashboardController::class, 'getReservationDetails'])->name('reservation.details');
            
            // Availability check
            Route::get('/availability-check', [AdminReservationController::class, 'checkAvailability'])->name('availability-check');
            Route::post('/availability-check', [AdminReservationController::class, 'checkAvailability'])->name('availability-check.post');
        });

        // Laboratory Management
        Route::resource('laboratories', AdminLaboratoryController::class);

        // Reservation Management
        Route::resource('reservations', AdminReservationController::class);
        Route::prefix('reservations/{reservation}')->name('reservations.')->group(function () {
            Route::post('/approve', [AdminReservationController::class, 'approve'])->name('approve');
            Route::post('/reject', [AdminReservationController::class, 'reject'])->name('reject');
        });

        // User Management
        Route::resource('users', AdminUserController::class);

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('index');
            
            // Export routes
            Route::get('/export/excel', [AdminReportController::class, 'exportExcel'])->name('excel');
            Route::get('/export/pdf', [AdminReportController::class, 'exportPdf'])->name('pdf');
            
            // Alternative with export prefix (if needed)
            Route::prefix('export')->name('export.')->group(function () {
                Route::get('/excel-alt', [AdminReportController::class, 'exportExcel'])->name('excel-alt');
                Route::get('/pdf-alt', [AdminReportController::class, 'exportPdf'])->name('pdf-alt');
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | User Routes (Dosen, Mahasiswa, User)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:dosen,mahasiswa,user')->prefix('user')->name('user.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            // Calendar Events for Dashboard
            Route::get('/calendar-events', [UserDashboardController::class, 'calendarEvents'])->name('calendar.events');
            
            // Laboratory search
            Route::get('/search-laboratories', [UserLaboratoryController::class, 'search'])->name('search.laboratories');
            
            // Availability check - ENHANCED with new routes
            Route::get('/availability-check', [UserReservationController::class, 'checkAvailability'])->name('availability-check');
            Route::post('/availability-check', [UserReservationController::class, 'checkAvailability'])->name('availability-check.post');
            Route::post('/check-availability', [UserReservationController::class, 'checkAvailability'])->name('check.availability');
            
            // Laboratory capacity - NEW ROUTE ADDED
            Route::get('/laboratory-capacity/{id}', [UserReservationController::class, 'getLaboratoryCapacity'])->name('laboratory-capacity');
            
            // Notifications API
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/unread-count', [UserNotificationController::class, 'getUnreadCount'])->name('unread-count');
                Route::get('/latest', [UserNotificationController::class, 'getLatestNotifications'])->name('latest');
            });
        });

        // Laboratory Routes
        Route::prefix('laboratories')->name('laboratories.')->group(function () {
            Route::get('/', [UserLaboratoryController::class, 'index'])->name('index');
            Route::get('/{laboratory}', [UserLaboratoryController::class, 'show'])->name('show');
            Route::get('/{laboratory}/availability', [UserLaboratoryController::class, 'checkAvailability'])->name('availability');
        });

        // Reservation Management
        Route::resource('reservations', UserReservationController::class);

        // Notification Management
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [UserNotificationController::class, 'index'])->name('index');
            Route::get('/{id}', [UserNotificationController::class, 'show'])->name('show');
            Route::post('/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('read.all');
            Route::post('/read-all-alt', [UserNotificationController::class, 'markAllAsRead'])->name('readAll');
            Route::delete('/{id}', [UserNotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/read-all', [UserNotificationController::class, 'deleteAllRead'])->name('delete.all.read');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Debug Routes (Development Only)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    Route::middleware(['auth', 'role:admin'])->prefix('debug')->name('debug.')->group(function () {
        
        // List all routes
        Route::get('/routes', function () {
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
        
        // User information
        Route::get('/user-info', function () {
            return response()->json([
                'user' => auth()->user(),
                'role' => auth()->user()->role,
                'permissions' => 'Role-based access control'
            ]);
        })->name('user.info');
        
        // Test calendar events - FIXED ROUTE
        Route::get('/test-admin-calendar', function () {
            try {
                $controller = app(AdminDashboardController::class);
                $request = request();
                $response = $controller->calendarEvents($request);
                return $response;
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for full trace'
                ], 500);
            }
        })->name('test.admin.calendar');
        
        // Test user calendar events
        Route::get('/test-user-calendar', function () {
            try {
                $controller = app(UserDashboardController::class);
                $request = request();
                $response = $controller->calendarEvents($request);
                return $response;
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for full trace'
                ], 500);
            }
        })->name('test.user.calendar');
        
        // Create sample data
        Route::get('/create-sample-data', function () {
            try {
                $laboratories = \App\Models\Laboratory::all();
                $users = \App\Models\User::whereIn('role', ['dosen', 'mahasiswa', 'user'])->get();
                
                if ($laboratories->isEmpty() || $users->isEmpty()) {
                    return response()->json([
                        'error' => 'No laboratories or users found',
                        'laboratories_count' => $laboratories->count(),
                        'users_count' => $users->count()
                    ]);
                }
                
                $reservation = \App\Models\Reservation::create([
                    'user_id' => $users->random()->id,
                    'laboratory_id' => $laboratories->random()->id,
                    'reservation_date' => now()->addDays(rand(1, 14))->format('Y-m-d'),
                    'start_time' => sprintf('%02d:00:00', rand(8, 14)),
                    'end_time' => sprintf('%02d:00:00', rand(10, 17)),
                    'purpose' => 'Test Reservation - ' . fake()->sentence(4),
                    'description' => 'Sample reservation created from debug route for testing purposes',
                    'participant_count' => rand(5, 30),
                    'status' => fake()->randomElement(['pending', 'approved', 'rejected'])
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sample reservation created successfully',
                    'reservation' => $reservation->load(['user', 'laboratory']),
                    'redirect' => route('admin.dashboard')
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for full trace'
                ], 500);
            }
        })->name('create.sample.data');
        
        // Check database status
        Route::get('/db-status', function () {
            try {
                $dbConnection = \Illuminate\Support\Facades\DB::connection()->getPdo();
                
                return response()->json([
                    'database' => 'Connected',
                    'tables' => [
                        'users' => \App\Models\User::count(),
                        'laboratories' => \App\Models\Laboratory::count(),
                        'reservations' => \App\Models\Reservation::count(),
                    ],
                    'sample_user' => \App\Models\User::first(),
                    'sample_laboratory' => \App\Models\Laboratory::first(),
                    'recent_reservations' => \App\Models\Reservation::with(['user', 'laboratory'])->latest()->take(3)->get()
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Database connection failed: ' . $e->getMessage()
                ], 500);
            }
        })->name('db.status');
    });
}