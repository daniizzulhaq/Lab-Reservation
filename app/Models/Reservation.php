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

    // Check if reservation conflicts with another reservation
    public function conflictsWith($laboratoryId, $date, $startTime, $endTime, $excludeId = null)
    {
        return static::where('laboratory_id', $laboratoryId)
            ->where('reservation_date', $date)
            ->where('status', 'approved')
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Check if times overlap
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();
    }
}