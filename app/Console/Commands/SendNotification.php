<?php

namespace App\Console\Commands;

use App\Models\Setoran;
use App\Models\User;
use App\Notifications\SetoranDiambil;
use App\Notifications\SetoranDitolak;
use App\Notifications\SetoranSelesai;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    protected $signature = 'notif:send {userId} {setoranId} {type}';
    protected $description = 'Kirim notifikasi setoran (ambil/tolak) melalui database dan email';

    public function handle(): int
    {
        $user = User::find($this->argument('userId'));
        $setoran = Setoran::find($this->argument('setoranId'));

        if (!$user || !$setoran) {
            $this->error('User atau setoran tidak ditemukan.');
            return 1;
        }

        $setoran->load('user', 'pengepul');

        switch ($this->argument('type')) {
            case 'ambil':
                $setoran->user->notify(new SetoranDiambil($setoran));
                break;
            case 'tolak':
                $setoran->user->notify(new SetoranDitolak($setoran));
                break;
            case 'selesai':
                $setoran->user->notify(new SetoranSelesai($setoran));
                break;
            default:
                $this->error('Type tidak dikenal.');
                return 1;
        }

        $this->info('Notifikasi terkirim: ' . $this->argument('type'));
        return 0;
    }
}