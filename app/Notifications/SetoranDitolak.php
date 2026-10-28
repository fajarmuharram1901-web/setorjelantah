<?php

namespace App\Notifications;

use App\Models\Setoran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetoranDitolak extends Notification
{
    use Queueable;

    public function __construct(public Setoran $setoran) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $setoran = $this->setoran;

        return (new MailMessage)
            ->subject('Pengajuan Setoran Ditolak')
            ->view('emails.setoran-ditolak', [
                'name' => $notifiable->name,
                'perkiraanKg' => number_format($setoran->perkiraan_kg, 2, ',', '.'),
                'urlSetoran' => route('user.riwayat'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'setoran_id' => $this->setoran->id,
            'message' => 'Pengajuan Anda ditolak pengepul. Silakan ajukan kembali jika masih memiliki minyak jelantah.',
            'url' => route('user.riwayat'),
            'target' => 'user.riwayat',
        ];
    }
}