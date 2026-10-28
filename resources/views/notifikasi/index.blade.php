@extends('layouts.dashboard', ['title' => 'Notifikasi', 'subtitle' => 'Pemberitahuan status setoran Anda'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Notifikasi</h1>
        <p class="page-sub">Riwayat pemberitahuan tentang tugas dan setoran Anda.</p>
    </div>
    <div class="panel-actions">
        <form method="POST" action="{{ route('notifikasi.readAll') }}" style="max-width:none;margin:0;">
            @csrf
            <button type="submit" class="btn btn-secondary">Tandai Semua Dibaca</button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-body panel-body-flush">
        @if ($notifikasi->isEmpty())
            <x-empty title="Belum ada notifikasi"
                     message="Pemberitahuan tentang tugas dan setoran akan muncul di sini."
                     icon="bell" />
        @else
            <ul class="notif-list">
                @foreach ($notifikasi as $n)
                    @php $data = $n->data ?? []; @endphp
                    <li class="notif-item{{ $n->read_at === null ? ' is-unread' : '' }}">
                        <span class="timeline-dot {{ $n->read_at === null ? '' : 'is-success' }}">
                            <x-icon :name="$n->read_at === null ? 'bell' : 'check'" :size="14" />
                        </span>
                        <div class="timeline-body">
                            <p>
                                @if ($n->read_at === null)
                                    <span class="badge badge-warning">Baru</span>
                                @endif
                                {{ $data['message'] ?? 'Notifikasi' }}
                            </p>
                            <small>{{ $n->created_at->diffForHumans() }}</small>
                        </div>
                        @if ($n->read_at === null)
                            <form method="POST" action="{{ route('notifikasi.read', $n->id) }}" style="max-width:none;margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary">Tandai Dibaca</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($notifikasi->hasPages())
        {{ $notifikasi->links() }}
    @endif
</div>

@endsection
