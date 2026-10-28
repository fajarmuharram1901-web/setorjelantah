<?php

namespace App\Notifications;

use App\Models\Setoran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetoranSelesai extends Notification
{
    use Queueable;

    public function __construct(public Setoran $setoran)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minyak = number_format((float) $this->setoran->minyak_kg, 2, ',', '.');
        $endapan = number_format((float) $this->setoran->endapan_kg, 2, ',', '.');
        $total = number_format((float) $this->setoran->total_saldo, 0, ',', '.');

        return (new MailMessage)
            ->subject('Setoran minyak jelantah Anda selesai')
            ->view('emails.setoran-selesai', [
                'minyakKg' => $minyak,
                'endapanKg' => $endapan,
                'totalKg' => $minyak + $endapan,
                'total' => $total,
                'name' => $notifiable->name,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $s = $this->setoran;

        // Angka adjuvant harus lewat number_format, kalau tidak PHP menulis
        // desimal dengan titik (4.50) yang membingungkan dinotifikasi Bahasa
        // Indonesia yang memakai koma.
        $minyak = number_format((float) $s->minyak_kg, 2, ',', '.');
        $endapan = number_format((float) $s->endapan_kg, 2, ',', '.');
        $total = number_format((float) $s->total_saldo, 0, ',', '.');

        return [
            'minyakKg' => (float) $s->minyak_kg,
            'endapanKg' => (float) $s->endapan_kg,
            'totalSaldo' => (float) $s->total_saldo,
            'message' => "Setoran Anda (minyak {$minyak} kg, endapan {$endapan} kg) sudah selesai. Saldo bertambah Rp{$total}.",
            'url' => route('user.saldo'),
            'target' => 'user.saldo',
        ];
    }
}
