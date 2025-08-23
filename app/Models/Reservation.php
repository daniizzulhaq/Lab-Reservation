<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'laboratory_id',
        'reservation_date',
        'start_time',
        'end_time',
        'participant_count',
        'purpose',
        'notes',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'reservation_code'
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Generate reservation code automatically
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (!$reservation->reservation_code) {
                $reservation->reservation_code = 'RSV-' . date('Ymd') . '-' . str_pad(
                    static::whereDate('created_at', today())->count() + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reservation_date', $date);
    }

    public function scopeForLaboratory($query, $laboratoryId)
    {
        return $query->where('laboratory_id', $laboratoryId);
    }

    // Accessors & Mutators
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Menunggu</span>',
            'approved' => '<span class="badge badge-success">Disetujui</span>',
            'rejected' => '<span class="badge badge-danger">Ditolak</span>',
            'cancelled' => '<span class="badge badge-secondary">Dibatalkan</span>',
            'completed' => '<span class="badge badge-info">Selesai</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-light">' . ucfirst($this->status) . '</span>';
    }

    public function getFormattedDateAttribute()
    {
        return $this->reservation_date->format('d/m/Y');
    }

    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('H:i') . ' - ' . Carbon::parse($this->end_time)->format('H:i');
    }

    public function getDurationInHoursAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        // Handle cases where end time is next day (crosses midnight)
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        return $end->diffInHours($start);
    }

    // Helper Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeModified()
    {
        return in_array($this->status, ['pending']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    /**
     * ENHANCED: Check if reservation conflicts with another reservation - improved overlap detection
     */
    public function conflictsWith($laboratoryId, $date, $startTime, $endTime, $excludeId = null)
    {
        // Convert time strings to proper format for comparison
        $newStart = Carbon::createFromFormat('H:i', $startTime);
        $newEnd = Carbon::createFromFormat('H:i', $endTime);
        
        // Handle overnight reservations
        if ($newEnd->lt($newStart)) {
            $newEnd->addDay();
        }
        
        return static::where('laboratory_id', $laboratoryId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved']) // Include both pending and approved reservations
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->where(function ($query) use ($newStart, $newEnd) {
                $query->where(function ($q) use ($newStart, $newEnd) {
                    // Convert stored time format for comparison
                    $startTimeFormatted = $newStart->format('H:i:s');
                    $endTimeFormatted = $newEnd->format('H:i:s');
                    
                    // ENHANCED: More comprehensive overlap detection
                    // Case 1: New reservation starts during existing reservation
                    $q->where(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '<=', $startTimeFormatted)
                             ->where('end_time', '>', $startTimeFormatted);
                    })
                    // Case 2: New reservation ends during existing reservation
                    ->orWhere(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '<', $endTimeFormatted)
                             ->where('end_time', '>=', $endTimeFormatted);
                    })
                    // Case 3: New reservation completely contains existing reservation
                    ->orWhere(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '>=', $startTimeFormatted)
                             ->where('end_time', '<=', $endTimeFormatted);
                    })
                    // Case 4: Existing reservation completely contains new reservation
                    ->orWhere(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '<=', $startTimeFormatted)
                             ->where('end_time', '>=', $endTimeFormatted);
                    });
                });
            })
            ->exists();
    }

    /**
     * NEW: Get conflicting reservations with details
     */
    public function getConflictingReservations($laboratoryId, $date, $startTime, $endTime, $excludeId = null)
    {
        $newStart = Carbon::createFromFormat('H:i', $startTime);
        $newEnd = Carbon::createFromFormat('H:i', $endTime);
        
        if ($newEnd->lt($newStart)) {
            $newEnd->addDay();
        }
        
        return static::where('laboratory_id', $laboratoryId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->where(function ($query) use ($newStart, $newEnd) {
                $startTimeFormatted = $newStart->format('H:i:s');
                $endTimeFormatted = $newEnd->format('H:i:s');
                
                $query->where(function ($q) use ($startTimeFormatted, $endTimeFormatted) {
                    $q->where(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '<=', $startTimeFormatted)
                             ->where('end_time', '>', $startTimeFormatted);
                    })
                    ->orWhere(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '<', $endTimeFormatted)
                             ->where('end_time', '>=', $endTimeFormatted);
                    })
                    ->orWhere(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '>=', $startTimeFormatted)
                             ->where('end_time', '<=', $endTimeFormatted);
                    })
                    ->orWhere(function ($subQ) use ($startTimeFormatted, $endTimeFormatted) {
                        $subQ->where('start_time', '<=', $startTimeFormatted)
                             ->where('end_time', '>=', $endTimeFormatted);
                    });
                });
            })
            ->with(['user:id,name', 'laboratory:id,name'])
            ->get();
    }

    /**
     * NEW: Check if current time slot is available
     */
    public static function isTimeSlotAvailable($laboratoryId, $date, $startTime, $endTime, $excludeId = null)
    {
        $reservation = new static();
        return !$reservation->conflictsWith($laboratoryId, $date, $startTime, $endTime, $excludeId);
    }

    /**
     * NEW: Get available time slots for a specific date and laboratory
     */
    public static function getAvailableTimeSlots($laboratoryId, $date, $minDuration = 30, $excludeId = null)
    {
        $existingReservations = static::where('laboratory_id', $laboratoryId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $availableSlots = [];
        $dayStart = Carbon::createFromFormat('H:i', '00:00');
        $dayEnd = Carbon::createFromFormat('H:i', '23:59');
        $currentTime = $dayStart->copy();

        foreach ($existingReservations as $reservation) {
            $reservationStart = Carbon::createFromFormat('H:i:s', $reservation->start_time);
            $reservationEnd = Carbon::createFromFormat('H:i:s', $reservation->end_time);

            // Check if there's a gap before this reservation
            if ($currentTime->lt($reservationStart)) {
                $gapMinutes = $currentTime->diffInMinutes($reservationStart);
                
                if ($gapMinutes >= $minDuration) {
                    $availableSlots[] = [
                        'start' => $currentTime->format('H:i'),
                        'end' => $reservationStart->format('H:i'),
                        'duration' => $gapMinutes
                    ];
                }
            }

            // Move current time to after this reservation
            if ($reservationEnd->gt($currentTime)) {
                $currentTime = $reservationEnd->copy();
            }
        }

        // Check for availability after the last reservation
        if ($currentTime->lt($dayEnd)) {
            $remainingMinutes = $currentTime->diffInMinutes($dayEnd);
            
            if ($remainingMinutes >= $minDuration) {
                $availableSlots[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $dayEnd->format('H:i'),
                    'duration' => $remainingMinutes
                ];
            }
        }

        return $availableSlots;
    }
}