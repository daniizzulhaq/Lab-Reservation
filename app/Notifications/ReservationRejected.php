<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reservation;

class ReservationRejected extends Notification
{
    use Queueable;

    protected $reservation;
    protected $reason;

    public function __construct(Reservation $reservation, $reason = null)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Reservasi Ditolak',
            'message' => 'Reservasi Anda untuk laboratorium ' . $this->reservation->laboratory->name . ' ditolak.' . 
                        ($this->reason ? ' Alasan: ' . $this->reason : ''),
            'reservation_id' => $this->reservation->id,
            'laboratory_name' => $this->reservation->laboratory->name,
            'reservation_date' => $this->reservation->reservation_date,
            'reason' => $this->reason,
            'type' => 'reservation_rejected'
        ];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Reservasi Laboratorium Ditolak')
            ->line('Maaf, reservasi Anda ditolak.')
            ->line('Detail Reservasi:')
            ->line('Laboratorium: ' . $this->reservation->laboratory->name)
            ->line('Tanggal: ' . $this->reservation->reservation_date);

        if ($this->reason) {
            $mail->line('Alasan: ' . $this->reason);
        }

        return $mail->line('Anda dapat membuat reservasi baru dengan waktu yang berbeda.');
    }
}