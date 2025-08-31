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
            // Calendar and Dashboard APIs
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
    | User Routes (Dosen, Mahasiswa, User) - PERBAIKAN MIDDLEWARE
    |--------------------------------------------------------------------------
    */
    // PERBAIKAN: Menggunakan middleware yang lebih fleksibel
    Route::middleware('role:dosen,mahasiswa,user')->prefix('user')->name('user.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // ===== PERBAIKAN: API Routes dengan middleware tambahan =====
        Route::prefix('api')->name('api.')->group(function () {
            // CALENDAR EVENTS API - ROUTE YANG PALING PENTING
            Route::get('/calendar-events', [UserDashboardController::class, 'calendarEvents'])
                ->name('calendar.events')
                ->middleware('throttle:60,1'); // Rate limiting untuk performance
            
            // Reservation details for modal
            Route::get('/reservation-details/{id}', [UserDashboardController::class, 'getReservationDetails'])
                ->name('reservation.details')
                ->where('id', '[0-9]+'); // Pastikan ID numerik
            
            // Laboratory search
            Route::get('/search-laboratories', [UserLaboratoryController::class, 'search'])->name('search.laboratories');
            
            // Availability check - PERBAIKAN: Konsistensi naming
            Route::get('/check-availability', [UserReservationController::class, 'checkAvailability'])->name('check.availability');
            Route::post('/check-availability', [UserReservationController::class, 'checkAvailability'])->name('check.availability.post');
            
            // Laboratory capacity
            Route::get('/laboratory-capacity/{id}', [UserReservationController::class, 'getLaboratoryCapacity'])
                ->name('laboratory.capacity')
                ->where('id', '[0-9]+');
            
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
            // Basic CRUD
            Route::get('/', [UserNotificationController::class, 'index'])->name('index');
            Route::get('/{id}', [UserNotificationController::class, 'show'])->name('show');
            Route::delete('/{id}', [UserNotificationController::class, 'destroy'])->name('destroy');
            
            // Mark as read routes
            Route::post('/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('markAsRead');
            Route::post('/mark-as-read/{id}', [UserNotificationController::class, 'markAsRead'])->name('read');
            
            // Mark all as read
            Route::post('/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('readAll');
            Route::post('/mark-all-read', [UserNotificationController::class, 'markAllAsRead'])->name('read.all');
            
            // Delete operations
            Route::delete('/read/delete-all', [UserNotificationController::class, 'deleteAllRead'])->name('deleteAllRead');
            Route::delete('/read-all', [UserNotificationController::class, 'deleteAllRead'])->name('delete.all.read');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Debug Routes (Development Only) - DIPERBAIKI
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
        
        // ===== PERBAIKAN: Test route untuk user calendar events =====
        Route::get('/test-user-calendar-api', function () {
            try {
                // Simulate request untuk user calendar events
                $request = new \Illuminate\Http\Request();
                $request->merge([
                    'start' => now()->startOfMonth()->format('Y-m-d'),
                    'end' => now()->endOfMonth()->format('Y-m-d')
                ]);
                
                $controller = app(UserDashboardController::class);
                $response = $controller->calendarEvents($request);
                
                return response()->json([
                    'success' => true,
                    'route_exists' => true,
                    'controller_method_exists' => method_exists($controller, 'calendarEvents'),
                    'response_data' => $response->getData(),
                    'current_user' => auth()->user()->only(['id', 'name', 'role']),
                    'test_url' => '/user/api/calendar-events'
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for full trace',
                    'route_exists' => \Illuminate\Support\Facades\Route::has('user.api.calendar.events'),
                    'controller_exists' => class_exists(UserDashboardController::class)
                ], 500);
            }
        })->name('test.user.calendar.api');
        
        // Test direct access to user calendar endpoint
        Route::get('/test-user-calendar-direct', function () {
            try {
                $user = auth()->user();
                
                // Ambil data reservasi langsung
                $reservations = \App\Models\Reservation::with(['laboratory'])
                    ->where('user_id', $user->id)
                    ->whereBetween('reservation_date', [
                        now()->startOfMonth()->format('Y-m-d'),
                        now()->endOfMonth()->format('Y-m-d')
                    ])
                    ->get();
                
                // Format seperti di controller
                $events = $reservations->map(function ($reservation) use ($user) {
                    $statusColors = [
                        'pending' => '#ffc107',
                        'approved' => '#28a745',
                        'rejected' => '#dc3545',
                        'cancelled' => '#6c757d',
                        'completed' => '#17a2b8'
                    ];

                    $color = $statusColors[$reservation->status] ?? '#6c757d';
                    
                    $startDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->start_time;
                    $endDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $reservation->end_time;

                    return [
                        'id' => 'user-reservation-' . $reservation->id,
                        'title' => $reservation->laboratory->name,
                        'start' => $startDateTime,
                        'end' => $endDateTime,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'extendedProps' => [
                            'laboratory' => $reservation->laboratory->name,
                            'user' => $user->name,
                            'purpose' => $reservation->purpose,
                            'status' => ucfirst($reservation->status),
                            'reservation_id' => $reservation->id
                        ]
                    ];
                });
                
                return response()->json([
                    'success' => true,
                    'raw_reservations_count' => $reservations->count(),
                    'formatted_events_count' => $events->count(),
                    'events' => $events->toArray(),
                    'user_info' => $user->only(['id', 'name', 'role']),
                    'date_range' => [
                        'start' => now()->startOfMonth()->format('Y-m-d'),
                        'end' => now()->endOfMonth()->format('Y-m-d')
                    ]
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for full trace'
                ], 500);
            }
        })->name('test.user.calendar.direct');
        
        // Test route validation
        Route::get('/validate-routes', function () {
            $routeChecks = [
                'user.dashboard' => \Illuminate\Support\Facades\Route::has('user.dashboard'),
                'user.api.calendar.events' => \Illuminate\Support\Facades\Route::has('user.api.calendar.events'),
                'user.reservations.create' => \Illuminate\Support\Facades\Route::has('user.reservations.create'),
                'user.reservations.index' => \Illuminate\Support\Facades\Route::has('user.reservations.index'),
            ];
            
            $controllerChecks = [
                'UserDashboardController exists' => class_exists(UserDashboardController::class),
                'calendarEvents method exists' => method_exists(UserDashboardController::class, 'calendarEvents'),
                'index method exists' => method_exists(UserDashboardController::class, 'index'),
            ];
            
            return response()->json([
                'route_checks' => $routeChecks,
                'controller_checks' => $controllerChecks,
                'current_user' => auth()->user()->only(['id', 'name', 'role']),
                'middleware_applied' => 'role:dosen,mahasiswa,user'
            ]);
        })->name('validate.routes');
        
        // Test database connection and models
        Route::get('/db-status', function () {
            try {
                $dbConnection = \Illuminate\Support\Facades\DB::connection()->getPdo();
                
                return response()->json([
                    'database' => 'Connected',
                    'tables' => [
                        'users' => \App\Models\User::count(),
                        'laboratories' => \App\Models\Laboratory::count(),
                        'reservations' => \App\Models\Reservation::count(),
                        'notifications' => \Illuminate\Notifications\DatabaseNotification::count(),
                    ],
                    'current_user_reservations' => \App\Models\Reservation::where('user_id', auth()->id())->count(),
                    'sample_reservation' => \App\Models\Reservation::with(['user', 'laboratory'])->where('user_id', auth()->id())->first(),
                    'recent_reservations' => \App\Models\Reservation::with(['user', 'laboratory'])->where('user_id', auth()->id())->latest()->take(3)->get()
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Database connection failed: ' . $e->getMessage()
                ], 500);
            }
        })->name('db.status');
    });
    
    // ===== TAMBAHAN: Public debug route untuk user role (tanpa admin middleware) =====
    Route::middleware('role:dosen,mahasiswa,user')->prefix('user/debug')->name('user.debug.')->group(function () {
        
        // Quick calendar test untuk user biasa
        Route::get('/calendar-test', function () {
            try {
                $user = auth()->user();
                $controller = app(UserDashboardController::class);
                $request = request();
                
                $response = $controller->calendarEvents($request);
                $data = $response->getData();
                
                return response()->json([
                    'success' => true,
                    'user' => $user->only(['id', 'name', 'role']),
                    'events_count' => is_array($data) ? count($data) : 0,
                    'events_data' => $data,
                    'route_name' => 'user.api.calendar.events',
                    'endpoint_url' => '/user/api/calendar-events'
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'user' => auth()->user()->only(['id', 'name', 'role']),
                    'stack_trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug for stack trace'
                ], 500);
            }
        })->name('calendar.test');
    });
}