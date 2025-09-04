<?php

// 1. FIXED: ReservationApproved.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reservation;

class ReservationApproved extends Notification
{
    use Queueable;

    protected $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable)
    {
        return ['database']; // Fokus database notification dulu
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Reservasi Disetujui',
            'message' => 'Reservasi Anda untuk laboratorium ' . $this->reservation->laboratory->name . ' telah disetujui.',
            'reservation_id' => $this->reservation->id,
            'laboratory_name' => $this->reservation->laboratory->name,
            'reservation_date' => $this->reservation->reservation_date->format('Y-m-d'),
            'start_time' => $this->reservation->start_time,
            'end_time' => $this->reservation->end_time,
            'purpose' => $this->reservation->purpose ?? 'Tidak ada tujuan',
            'type' => 'reservation_approved',
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'admin_notes' => $this->reservation->admin_notes ?? ''
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reservasi Laboratorium Disetujui')
            ->line('Reservasi Anda telah disetujui!')
            ->line('Detail Reservasi:')
            ->line('Laboratorium: ' . $this->reservation->laboratory->name)
            ->line('Tanggal: ' . $this->reservation->reservation_date->format('d F Y'))
            ->line('Waktu: ' . $this->reservation->start_time . ' - ' . $this->reservation->end_time)
            ->action('Lihat Reservasi', url('/user/reservations/' . $this->reservation->id))
            ->line('Terima kasih telah menggunakan sistem reservasi laboratorium.');
    }
}