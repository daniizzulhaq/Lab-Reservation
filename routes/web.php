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
use Illuminate\Http\Request;

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
    | Admin Routes - DIPERBAIKI UNTUK CALENDAR
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // ===== API ROUTES - DIPERBAIKI DAN DIPERLUAS =====
        Route::prefix('api')->name('api.')->group(function () {
            
            // ===== CALENDAR API ROUTES - UTAMA =====
            // Primary calendar events endpoint
            Route::get('/calendar-events', [AdminDashboardController::class, 'calendarEvents'])
                ->name('calendar.events');
            
            // Debug calendar endpoint untuk troubleshooting
            Route::get('/calendar-debug', [AdminDashboardController::class, 'debugCalendar'])
                ->name('calendar.debug');
            
            // Test events dengan data statis untuk debugging
            Route::get('/calendar-test-events', [AdminDashboardController::class, 'testEvents'])
                ->name('calendar.test.events');
            
            // Alternative calendar events dengan error handling berbeda
            Route::get('/calendar-events-alt', function(Request $request) {
                try {
                    \Log::info('Alternative admin calendar route accessed', [
                        'user_id' => auth()->id(),
                        'user_role' => auth()->user()->role,
                        'params' => $request->all()
                    ]);
                    
                    // Direct database query sebagai fallback
                    $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
                    $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));
                    
                    $reservations = \App\Models\Reservation::with(['laboratory', 'user'])
                        ->whereBetween('reservation_date', [$start, $end])
                        ->get();
                    
                    $events = $reservations->map(function ($reservation) {
                        if (!$reservation->laboratory || !$reservation->user) {
                            return null;
                        }
                        
                        $statusColors = [
                            'pending' => '#ffc107',
                            'approved' => '#198754',
                            'rejected' => '#dc3545',
                            'cancelled' => '#6c757d',
                            'completed' => '#17a2b8'
                        ];

                        $color = $statusColors[$reservation->status] ?? '#6c757d';
                        $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';
                        
                        $startTime = \Str::substr($reservation->start_time, 0, 8);
                        $endTime = \Str::substr($reservation->end_time, 0, 8);
                        
                        $startDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $startTime;
                        $endDateTime = $reservation->reservation_date->format('Y-m-d') . 'T' . $endTime;

                        return [
                            'id' => 'admin-reservation-' . $reservation->id,
                            'title' => $reservation->laboratory->name . ' - ' . $reservation->user->name,
                            'start' => $startDateTime,
                            'end' => $endDateTime,
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                            'textColor' => $textColor,
                            'allDay' => false,
                            'extendedProps' => [
                                'laboratory' => $reservation->laboratory->name,
                                'user' => $reservation->user->name,
                                'purpose' => $reservation->purpose ?? 'Tidak ada tujuan',
                                'status' => ucfirst($reservation->status),
                                'participant_count' => $reservation->participant_count ?? 0,
                                'reservation_id' => $reservation->id,
                                'description' => $reservation->description ?? '',
                                'admin_notes' => $reservation->admin_notes ?? ''
                            ]
                        ];
                    })->filter()->values();
                    
                    \Log::info('Alternative calendar events loaded', [
                        'events_count' => $events->count(),
                        'date_range' => [$start, $end]
                    ]);
                    
                    return response()->json($events, 200, [
                        'Content-Type' => 'application/json; charset=utf-8',
                        'X-Alternative-Route' => 'true'
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Alternative admin calendar route failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'error' => true,
                        'message' => 'Failed to load calendar data',
                        'debug' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            })->name('calendar.events.alt');
            
            // Simple JSON test untuk memastikan route berfungsi
            Route::get('/test-json', function() {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin API routes working',
                    'timestamp' => now()->toISOString(),
                    'user' => auth()->user()->only(['id', 'name', 'role'])
                ]);
            })->name('test.json');
            
            // ===== EXISTING ROUTES - JANGAN UBAH =====
            // Chart data
            Route::get('/chart-data', [AdminDashboardController::class, 'getChartData'])
                ->name('chart.data');
            
            // Reservation details for modal
            Route::get('/reservation-details/{id}', [AdminDashboardController::class, 'getReservationDetails'])
                ->name('reservation.details')
                ->where('id', '[0-9]+');
            
            // Availability check
            Route::get('/availability-check', [AdminReservationController::class, 'checkAvailability'])
                ->name('availability-check');
            Route::post('/availability-check', [AdminReservationController::class, 'checkAvailability'])
                ->name('availability-check.post');
        });

        // ===== TAMBAHAN: CALENDAR MANAGEMENT ROUTES =====
        Route::prefix('calendar')->name('calendar.')->group(function () {
            
            // Calendar view page (jika diperlukan halaman terpisah)
            Route::get('/', function() {
                $stats = [
                    'total_laboratories' => \App\Models\Laboratory::count(),
                    'total_users' => \App\Models\User::whereIn('role', ['user', 'dosen', 'mahasiswa'])->count(),
                    'pending_reservations' => \App\Models\Reservation::where('status', 'pending')->count(),
                    'total_reservations' => \App\Models\Reservation::count(),
                ];
                
                return view('admin.calendar', compact('stats'));
            })->name('index');
            
            // Calendar event operations
            Route::post('/events/{id}/update-status', function($id, Request $request) {
                try {
                    $reservation = \App\Models\Reservation::findOrFail($id);
                    $newStatus = $request->input('status');
                    
                    if (!in_array($newStatus, ['pending', 'approved', 'rejected', 'cancelled', 'completed'])) {
                        return response()->json(['error' => 'Invalid status'], 400);
                    }
                    
                    $reservation->update([
                        'status' => $newStatus,
                        'approved_by' => $newStatus === 'approved' ? auth()->id() : null,
                        'admin_notes' => $request->input('notes', '')
                    ]);
                    
                    \Log::info('Reservation status updated via calendar', [
                        'reservation_id' => $id,
                        'old_status' => $reservation->getOriginal('status'),
                        'new_status' => $newStatus,
                        'admin_id' => auth()->id()
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Status berhasil diupdate',
                        'new_status' => $newStatus
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to update reservation status via calendar', [
                        'reservation_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                    
                    return response()->json(['error' => 'Gagal mengupdate status'], 500);
                }
            })->name('events.update.status');
        });

        // ===== EXISTING RESOURCE ROUTES - JANGAN UBAH =====
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
            Route::get('/export/excel', [AdminReportController::class, 'exportExcel'])->name('excel');
            Route::get('/export/pdf', [AdminReportController::class, 'exportPdf'])->name('pdf');
            
            Route::prefix('export')->name('export.')->group(function () {
                Route::get('/excel-alt', [AdminReportController::class, 'exportExcel'])->name('excel-alt');
                Route::get('/pdf-alt', [AdminReportController::class, 'exportPdf'])->name('pdf-alt');
            });
        });
    });

   /*
|--------------------------------------------------------------------------
| User Routes (Dosen, Mahasiswa, User) - FIXED AVAILABILITY CHECK
|--------------------------------------------------------------------------
*/
Route::middleware('role:dosen,mahasiswa,user')->prefix('user')->name('user.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/calendar-events', [UserDashboardController::class, 'calendarEvents'])
            ->name('calendar.events')
            ->middleware('throttle:60,1');
        
        Route::get('/reservation-details/{id}', [UserDashboardController::class, 'getReservationDetails'])
            ->name('reservation.details')
            ->where('id', '[0-9]+');
        
        Route::get('/search-laboratories', [UserLaboratoryController::class, 'search'])->name('search.laboratories');
        
        // FIXED: Availability check routes
        Route::get('/availability-check', [UserReservationController::class, 'checkAvailability'])->name('availability-check');
        Route::post('/availability-check', [UserReservationController::class, 'checkAvailability'])->name('availability-check.post');
        
        // Alternative route names for compatibility
        Route::get('/check-availability', [UserReservationController::class, 'checkAvailability'])->name('check.availability');
        Route::post('/check-availability', [UserReservationController::class, 'checkAvailability'])->name('check.availability.post');
        
        Route::get('/laboratory-capacity/{id}', [UserReservationController::class, 'getLaboratoryCapacity'])
            ->name('laboratory.capacity')
            ->where('id', '[0-9]+');
        
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
        Route::delete('/{id}', [UserNotificationController::class, 'destroy'])->name('destroy');
        
        Route::post('/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('/mark-as-read/{id}', [UserNotificationController::class, 'markAsRead'])->name('read');
        
        Route::post('/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('readAll');
        Route::post('/mark-all-read', [UserNotificationController::class, 'markAllAsRead'])->name('read.all');
        
        Route::delete('/read/delete-all', [UserNotificationController::class, 'deleteAllRead'])->name('deleteAllRead');
        Route::delete('/read-all', [UserNotificationController::class, 'deleteAllRead'])->name('delete.all.read');
    });
});
});

/*
|--------------------------------------------------------------------------
| Debug Routes - DIPERBAIKI DAN DIPERLUAS
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
        
        // ===== ADMIN CALENDAR DEBUG ROUTES =====
        Route::prefix('admin-calendar')->name('admin.calendar.')->group(function () {
            
            // Test admin calendar API dengan detail logging
            Route::get('/test-api', function() {
                try {
                    \Log::info('Debug: Testing admin calendar API directly');
                    
                    $request = new \Illuminate\Http\Request();
                    $request->merge([
                        'start' => now()->startOfMonth()->format('Y-m-d'),
                        'end' => now()->endOfMonth()->format('Y-m-d')
                    ]);
                    
                    $controller = app(AdminDashboardController::class);
                    
                    // Check if method exists
                    if (!method_exists($controller, 'calendarEvents')) {
                        throw new \Exception('calendarEvents method not found in AdminDashboardController');
                    }
                    
                    $response = $controller->calendarEvents($request);
                    $data = $response->getData();
                    
                    return response()->json([
                        'success' => true,
                        'route_exists' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.events'),
                        'controller_method_exists' => true,
                        'response_status' => $response->getStatusCode(),
                        'events_count' => is_array($data) ? count($data) : 0,
                        'sample_event' => is_array($data) && count($data) > 0 ? $data[0] : null,
                        'current_user' => auth()->user()->only(['id', 'name', 'role']),
                        'test_params' => $request->all(),
                        'route_url' => route('admin.api.calendar.events')
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Admin calendar API test failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'error' => $e->getMessage(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for full trace',
                        'route_exists' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.events'),
                        'controller_exists' => class_exists(AdminDashboardController::class),
                        'method_exists' => method_exists(AdminDashboardController::class, 'calendarEvents')
                    ], 500);
                }
            })->name('test.api');
            
            // Test direct database query untuk admin calendar
            Route::get('/test-direct', function() {
                try {
                    $user = auth()->user();
                    \Log::info('Debug: Testing direct admin calendar data access', ['admin_id' => $user->id]);
                    
                    // Query database langsung
                    $reservations = \App\Models\Reservation::with(['laboratory', 'user'])
                        ->whereBetween('reservation_date', [
                            now()->startOfMonth()->format('Y-m-d'),
                            now()->endOfMonth()->format('Y-m-d')
                        ])
                        ->get();
                    
                    // Transform data
                    $events = $reservations->map(function ($reservation) {
                        if (!$reservation->laboratory || !$reservation->user) {
                            return null;
                        }
                        
                        $statusColors = [
                            'pending' => '#ffc107',
                            'approved' => '#198754', 
                            'rejected' => '#dc3545',
                            'cancelled' => '#6c757d',
                            'completed' => '#17a2b8'
                        ];

                        $color = $statusColors[$reservation->status] ?? '#6c757d';
                        $textColor = $reservation->status === 'pending' ? '#000000' : '#ffffff';
                        
                        return [
                            'id' => 'debug-reservation-' . $reservation->id,
                            'title' => $reservation->laboratory->name . ' - ' . $reservation->user->name,
                            'start' => $reservation->reservation_date->format('Y-m-d') . 'T' . \Str::substr($reservation->start_time, 0, 8),
                            'end' => $reservation->reservation_date->format('Y-m-d') . 'T' . \Str::substr($reservation->end_time, 0, 8),
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                            'textColor' => $textColor,
                            'extendedProps' => [
                                'laboratory' => $reservation->laboratory->name,
                                'user' => $reservation->user->name,
                                'status' => ucfirst($reservation->status),
                                'reservation_id' => $reservation->id
                            ]
                        ];
                    })->filter()->values();
                    
                    return response()->json([
                        'success' => true,
                        'total_reservations' => $reservations->count(),
                        'valid_events' => $events->count(),
                        'events' => $events->toArray(),
                        'user_info' => $user->only(['id', 'name', 'role']),
                        'date_range' => [
                            'start' => now()->startOfMonth()->format('Y-m-d'),
                            'end' => now()->endOfMonth()->format('Y-m-d')
                        ],
                        'missing_relationships' => $reservations->filter(function($r) {
                            return !$r->laboratory || !$r->user;
                        })->count()
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Direct admin calendar test failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'error' => $e->getMessage(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug for stack trace'
                    ], 500);
                }
            })->name('test.direct');
        });
        
        // ===== GENERAL DEBUG ROUTES - TIDAK DIUBAH =====
        Route::get('/validate-routes', function () {
            $routeChecks = [
                'admin.dashboard' => \Illuminate\Support\Facades\Route::has('admin.dashboard'),
                'admin.api.calendar.events' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.events'),
                'admin.api.calendar.debug' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.debug'),
                'admin.api.calendar.events.alt' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.events.alt'),
                'admin.reservations.index' => \Illuminate\Support\Facades\Route::has('admin.reservations.index'),
            ];
            
            $controllerChecks = [
                'AdminDashboardController exists' => class_exists(AdminDashboardController::class),
                'calendarEvents method exists' => method_exists(AdminDashboardController::class, 'calendarEvents'),
                'debugCalendar method exists' => method_exists(AdminDashboardController::class, 'debugCalendar'),
                'testEvents method exists' => method_exists(AdminDashboardController::class, 'testEvents'),
                'index method exists' => method_exists(AdminDashboardController::class, 'index'),
            ];
            
            return response()->json([
                'route_checks' => $routeChecks,
                'controller_checks' => $controllerChecks,
                'current_user' => auth()->user()->only(['id', 'name', 'role']),
                'middleware_applied' => 'role:admin'
            ]);
        })->name('validate.routes');
        
        // Database status
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
                    'admin_reservations' => \App\Models\Reservation::count(),
                    'sample_reservation' => \App\Models\Reservation::with(['user', 'laboratory'])->first(),
                    'recent_reservations' => \App\Models\Reservation::with(['user', 'laboratory'])->latest()->take(3)->get()
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Database connection failed: ' . $e->getMessage()
                ], 500);
            }
        })->name('db.status');
    });
    
    // User debug routes - TIDAK DIUBAH
    Route::middleware('role:dosen,mahasiswa,user')->prefix('user/debug')->name('user.debug.')->group(function () {
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

/*
|--------------------------------------------------------------------------
| TAMBAHAN: Quick Test Routes untuk Admin Calendar (Production Safe)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin/quick-test')->name('admin.quick.test.')->group(function () {
    
    // Test apakah controller method bisa dipanggil
    Route::get('/controller-method', function() {
        try {
            $controller = app(AdminDashboardController::class);
            
            if (!method_exists($controller, 'calendarEvents')) {
                return response()->json(['error' => 'Method calendarEvents tidak ditemukan'], 404);
            }
            
            // Test dengan request kosong
            $testRequest = new Request();
            $response = $controller->calendarEvents($testRequest);
            
            return response()->json([
                'success' => true,
                'method_exists' => true,
                'response_status' => $response->getStatusCode(),
                'has_data' => !empty($response->getData()),
                'message' => 'Controller method berhasil dipanggil'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'method_exists' => method_exists(AdminDashboardController::class, 'calendarEvents'),
                'controller_exists' => class_exists(AdminDashboardController::class)
            ], 500);
        }
    })->name('controller.method');
    
    // Test route accessibility
    Route::get('/route-access', function() {
        $routes = [
            'admin.dashboard' => \Illuminate\Support\Facades\Route::has('admin.dashboard'),
            'admin.api.calendar.events' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.events'),
            'admin.api.calendar.debug' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.debug'),
            'admin.api.test.json' => \Illuminate\Support\Facades\Route::has('admin.api.test.json'),
        ];
        
        $urls = [
            'calendar_events' => route('admin.api.calendar.events'),
            'calendar_debug' => \Illuminate\Support\Facades\Route::has('admin.api.calendar.debug') ? route('admin.api.calendar.debug') : 'ROUTE_NOT_FOUND',
            'dashboard' => route('admin.dashboard'),
        ];
        
        return response()->json([
            'route_checks' => $routes,
            'generated_urls' => $urls,
            'current_user' => auth()->user()->only(['id', 'name', 'role']),
            'middleware_check' => 'PASSED'
        ]);
    })->name('route.access');
});