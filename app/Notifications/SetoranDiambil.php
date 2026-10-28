<?php

namespace App\Notifications;

use App\Models\Setoran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetoranDiambil extends Notification
{
    use Queueable;

    public function __construct(public Setoran $setoran)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $setoran = $this->setoran;

        return (new MailMessage)
            ->subject('Setoran Anda Telah Diambil Pengepul')
            ->view('emails.setoran-diambil', [
                'name' => $notifiable->name,
                'pengepul' => $setoran->pengepul->name ?? 'Pengepul',
                'perkiraanKg' => number_format($setoran->perkiraanTotalKg(), 2, ',', '.'),
                'urlSetoran' => route('user.riwayat'),
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'setoran_id' => $this->setoran->id,
            // Satuan sudah kilogram sejak perubahan liter ke kg, dan
            // perkiraan_kg kini menyimpan total minyak + endapan.
            'message' => 'Setoran Anda sekira ' . number_format($this->setoran->perkiraanTotalKg(), 2, ',', '.') . ' kg telah diambil oleh ' . ($this->setoran->pengepul->name ?? 'pengepul') . '.',
            'url' => route('user.riwayat'),
            'target' => 'user.riwayat',
        ];
    }
}