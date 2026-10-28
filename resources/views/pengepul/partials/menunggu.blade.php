<div class="panel" id="kartu-menunggu">
    <div class="panel-head">
        <div>
            <h2>Pengajuan Menunggu <span id="jumlah-menunggu">({{ count($menunggu) }})</span></h2>
            <p class="panel-sub">Ambil satu tugas untuk mulai proses penjemputan</p>
        </div>
    </div>

    <div class="panel-body panel-body-flush">
        @if (count($menunggu) === 0)
            <x-empty title="Tidak ada pengajuan baru"
                     message="Daftar ini diperbarui otomatis setiap 15 detik."
                     icon="inbox" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Warga</th>
                            <th class="num">Perkiraan</th>
                            <th>Alamat</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menunggu as $s)
                            <tr>
                                <td>{{ $s->tanggal_ajukan->format('d M Y') }}</td>
                                <td>
                                    <span class="cell-title">{{ $s->user->name }}</span><br>
                                    <small class="cell-sub">{{ $s->user->no_hp_formatted ?? 'Tanpa nomor HP' }}</small>
                                </td>
                                <td class="num"><x-perkiraan :setoran="$s" /></td>
                                <td style="max-width:200px;">{{ $s->alamat_jemput }}</td>
                                <td style="max-width:180px;">{{ $s->catatan ?? '-' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <form method="POST" action="{{ route('pengepul.ambil', $s) }}" style="max-width:none;margin:0;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm">Ambil</button>
                                        </form>
                                        <form method="POST" action="{{ route('pengepul.tolak', $s) }}" style="max-width:none;margin:0;"
                                              data-konfirmasi="Yakin menolak pengajuan ini? Warga akan diberi tahu." data-judul="Tolak pengajuan" data-ok="Ya, tolak" data-bahaya>
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>