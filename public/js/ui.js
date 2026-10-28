/* =========================================================
   SetorJelantah — komponen interaktif (tanpa library)
   1. Event handling   : UI.on() — listener delegasi
   2. setState()       : UI.buatState() — state kecil + render ulang
   3. Dialog           : UI.konfirmasi() dan atribut data-konfirmasi
   4. Snackbar         : UI.snackbar() dan atribut data-snackbar
   5. Switch           : input[data-switch]
   6. Checkbox         : checkbox [data-gerbang] mengatur tombol kirim
   7. Dropdown         : select[data-dropdown]
   ========================================================= */
(function () {
    'use strict';

    /* ---------- 1. Event handling ---------- */
    // Satu listener di document untuk banyak elemen (event delegation),
    // jadi elemen yang dibuat belakangan tetap ikut tertangani.
    function on(jenis, selector, handler) {
        document.addEventListener(jenis, function (e) {
            const target = e.target.closest ? e.target.closest(selector) : null;
            if (target) handler(e, target);
        });
    }

    /* ---------- 2. setState() ---------- */
    // Pola seperti setState di Flutter/React: ubah state lewat setState(),
    // lalu fungsi render dipanggil lagi supaya tampilan ikut berubah.
    function buatState(awal, render) {
        let state = Object.assign({}, awal);
        const api = {
            get: function () { return state; },
            setState: function (perubahan) {
                const patch = typeof perubahan === 'function' ? perubahan(state) : perubahan;
                state = Object.assign({}, state, patch);
                render(state);
            }
        };
        render(state);
        return api;
    }

    /* ---------- 4. Snackbar ---------- */
    function snackbar(pesan, jenis, durasi) {
        let wadah = document.getElementById('snackbar-wadah');
        if (!wadah) {
            wadah = document.createElement('div');
            wadah.id = 'snackbar-wadah';
            wadah.className = 'snackbar-wadah';
            wadah.setAttribute('aria-live', 'polite');
            document.body.appendChild(wadah);
        }

        const el = document.createElement('div');
        el.className = 'snackbar snackbar-' + (jenis || 'info');
        el.setAttribute('role', 'status');

        const teks = document.createElement('span');
        teks.className = 'snackbar-pesan';
        teks.textContent = pesan;

        const tutup = document.createElement('button');
        tutup.type = 'button';
        tutup.className = 'snackbar-tutup';
        tutup.setAttribute('aria-label', 'Tutup pesan');
        tutup.innerHTML = '&times;';

        el.appendChild(teks);
        el.appendChild(tutup);
        wadah.appendChild(el);
        requestAnimationFrame(function () { el.classList.add('tampil'); });

        let pengingat = null;
        function hilang() {
            clearTimeout(pengingat);
            el.classList.remove('tampil');
            setTimeout(function () { el.remove(); }, 220);
        }
        tutup.addEventListener('click', hilang);
        pengingat = setTimeout(hilang, durasi || 4500);
    }

    /* ---------- 3. Dialog ---------- */
    let latar = null;
    let elJudul = null;
    let elPesan = null;
    let tombolBatal = null;
    let tombolOk = null;
    let penyelesai = null;
    let pemicuFokus = null;

    function siapkanDialog() {
        if (latar) return;

        latar = document.createElement('div');
        latar.className = 'dialog-latar';
        latar.hidden = true;
        latar.innerHTML =
            '<div class="dialog" role="alertdialog" aria-modal="true" ' +
            'aria-labelledby="dialog-judul" aria-describedby="dialog-pesan">' +
            '<h3 id="dialog-judul"></h3>' +
            '<p id="dialog-pesan"></p>' +
            '<div class="dialog-aksi">' +
            '<button type="button" class="btn btn-secondary" data-aksi="batal">Batal</button>' +
            '<button type="button" class="btn" data-aksi="ok">Ya</button>' +
            '</div></div>';
        document.body.appendChild(latar);

        elJudul = latar.querySelector('#dialog-judul');
        elPesan = latar.querySelector('#dialog-pesan');
        tombolBatal = latar.querySelector('[data-aksi="batal"]');
        tombolOk = latar.querySelector('[data-aksi="ok"]');

        tombolBatal.addEventListener('click', function () { tutupDialog(false); });
        tombolOk.addEventListener('click', function () { tutupDialog(true); });

        // Klik area gelap di luar kotak = batal.
        latar.addEventListener('click', function (e) {
            if (e.target === latar) tutupDialog(false);
        });

        latar.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                tutupDialog(false);
            } else if (e.key === 'Tab') {
                // Fokus dikunci di dalam dialog.
                const urutan = [tombolBatal, tombolOk];
                const i = urutan.indexOf(document.activeElement);
                e.preventDefault();
                urutan[(i + (e.shiftKey ? -1 : 1) + urutan.length) % urutan.length].focus();
            }
        });
    }

    function tutupDialog(hasil) {
        if (!latar || latar.hidden) return;
        latar.classList.remove('tampil');
        setTimeout(function () { latar.hidden = true; }, 180);

        const selesai = penyelesai;
        penyelesai = null;
        if (pemicuFokus && pemicuFokus.focus) pemicuFokus.focus();
        if (selesai) selesai(hasil);
    }

    // Mengembalikan Promise<boolean>: true bila pengguna menekan tombol OK.
    function konfirmasi(opsi) {
        siapkanDialog();
        opsi = opsi || {};

        return new Promise(function (selesai) {
            penyelesai = selesai;
            pemicuFokus = document.activeElement;

            elJudul.textContent = opsi.judul || 'Konfirmasi';
            elPesan.textContent = opsi.pesan || 'Lanjutkan tindakan ini?';
            tombolOk.textContent = opsi.teksOk || 'Ya, lanjutkan';
            tombolOk.className = 'btn' + (opsi.bahaya ? ' btn-danger' : '');

            latar.hidden = false;
            requestAnimationFrame(function () { latar.classList.add('tampil'); });
            // Fokus awal di "Batal" supaya tidak terhapus karena tekan Enter.
            tombolBatal.focus();
        });
    }

    // Form dengan data-konfirmasi="..." menampilkan dialog sebelum dikirim.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.hasAttribute || !form.hasAttribute('data-konfirmasi')) return;

        const lolos = form.dataset.terkonfirmasi === '1';
        delete form.dataset.terkonfirmasi;
        // Validasi form (validasi.js) yang sudah membatalkan submit kita hormati.
        if (lolos || e.defaultPrevented) return;

        e.preventDefault();
        const pengirim = e.submitter || null;

        konfirmasi({
            judul: form.dataset.judul,
            pesan: form.dataset.konfirmasi,
            teksOk: form.dataset.ok,
            bahaya: form.hasAttribute('data-bahaya')
        }).then(function (ya) {
            if (!ya) return;
            form.dataset.terkonfirmasi = '1';
            if (form.requestSubmit) {
                form.requestSubmit(pengirim);
            } else {
                form.submit();
            }
        });
    });

    /* ---------- 5. Switch ---------- */
    // <input type="checkbox" data-switch data-label-on=".." data-label-off=".."
    //        data-pesan-on=".." data-pesan-off=".."> di dalam <form>.
    // Kalau data-pesan-* ada, dialog konfirmasi tampil dulu; form dikirim
    // hanya bila pengguna setuju, kalau batal switch kembali ke posisi semula.
    function mulaiSwitch(input) {
        const bungkus = input.closest('.switch');
        const label = bungkus ? bungkus.querySelector('.switch-label') : null;
        const form = input.form;

        const state = buatState({ aktif: input.checked }, function (s) {
            input.checked = s.aktif;
            input.setAttribute('aria-checked', String(s.aktif));
            if (label) {
                label.textContent = s.aktif ? input.dataset.labelOn : input.dataset.labelOff;
            }
        });

        input.addEventListener('change', function () {
            const aktifBaru = input.checked;
            state.setState({ aktif: aktifBaru });

            const pesan = aktifBaru ? input.dataset.pesanOn : input.dataset.pesanOff;
            if (!form) return;

            function kirim() {
                if (form.requestSubmit) form.requestSubmit(); else form.submit();
            }

            if (!pesan) {
                kirim();
                return;
            }

            konfirmasi({
                judul: input.dataset.judul || 'Ubah pengaturan',
                pesan: pesan
            }).then(function (ya) {
                if (ya) {
                    kirim();
                } else {
                    state.setState({ aktif: !aktifBaru });
                }
            });
        });
    }

    /* ---------- 6. Checkbox ---------- */
    // <input type="checkbox" data-gerbang="#id-tombol"> — tombol kirim baru
    // aktif kalau semua checkbox yang menunjuk tombol itu dicentang.
    function mulaiGerbang() {
        const kotak = Array.prototype.slice.call(document.querySelectorAll('input[data-gerbang]'));
        const tombolUnik = {};

        kotak.forEach(function (k) { tombolUnik[k.dataset.gerbang] = true; });

        Object.keys(tombolUnik).forEach(function (selector) {
            const tombol = document.querySelector(selector);
            if (!tombol) return;

            const kelompok = kotak.filter(function (k) { return k.dataset.gerbang === selector; });
            function semuaTercentang() {
                return kelompok.every(function (k) { return k.checked; });
            }

            const state = buatState({ setuju: semuaTercentang() }, function (s) {
                tombol.disabled = !s.setuju;
                tombol.setAttribute('aria-disabled', String(!s.setuju));
            });

            kelompok.forEach(function (k) {
                k.addEventListener('change', function () {
                    state.setState({ setuju: semuaTercentang() });
                });
            });
        });
    }

    /* ---------- 7. Dropdown ---------- */
    // <select data-dropdown> diganti tampilannya jadi menu kustom. <select>
    // aslinya tetap ada (tersembunyi) supaya nilainya ikut terkirim bersama form.
    // Tambahkan data-auto-submit untuk langsung mengirim form saat memilih.
    function mulaiDropdown(select) {
        const bungkus = document.createElement('div');
        bungkus.className = 'dropdown-ui';
        select.parentNode.insertBefore(bungkus, select);
        bungkus.appendChild(select);
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const tombol = document.createElement('button');
        tombol.type = 'button';
        tombol.className = 'dropdown-tombol';
        tombol.setAttribute('aria-haspopup', 'listbox');
        tombol.setAttribute('aria-expanded', 'false');
        const teks = document.createElement('span');
        const caret = document.createElement('span');
        caret.className = 'dropdown-caret';
        caret.setAttribute('aria-hidden', 'true');
        tombol.appendChild(teks);
        tombol.appendChild(caret);

        const daftar = document.createElement('ul');
        daftar.className = 'dropdown-menu';
        daftar.setAttribute('role', 'listbox');
        daftar.hidden = true;

        const opsiEl = Array.prototype.map.call(select.options, function (o, i) {
            const li = document.createElement('li');
            li.className = 'dropdown-opsi';
            li.setAttribute('role', 'option');
            li.dataset.index = String(i);
            li.textContent = o.textContent;
            daftar.appendChild(li);
            return li;
        });

        bungkus.appendChild(tombol);
        bungkus.appendChild(daftar);

        // Klik label (for=id select) memfokuskan tombol, bukan <select> yang tersembunyi.
        select.addEventListener('focus', function () { tombol.focus(); });

        const state = buatState(
            { buka: false, terpilih: select.selectedIndex, fokus: select.selectedIndex },
            function (s) {
                const opsi = select.options[s.terpilih];
                teks.textContent = opsi ? opsi.textContent : '';
                bungkus.classList.toggle('buka', s.buka);
                tombol.setAttribute('aria-expanded', String(s.buka));
                daftar.hidden = !s.buka;
                opsiEl.forEach(function (li, i) {
                    li.setAttribute('aria-selected', String(i === s.terpilih));
                    li.classList.toggle('fokus', s.buka && i === s.fokus);
                });
                if (s.buka && opsiEl[s.fokus]) {
                    opsiEl[s.fokus].scrollIntoView({ block: 'nearest' });
                }
            }
        );

        function buka() {
            state.setState(function (s) { return { buka: true, fokus: s.terpilih }; });
        }
        function tutup() {
            state.setState({ buka: false });
        }
        function pilih(i) {
            if (i < 0 || i >= select.options.length) return;
            const berubah = select.selectedIndex !== i;
            select.selectedIndex = i;
            state.setState({ terpilih: i, fokus: i, buka: false });
            tombol.focus();

            if (berubah) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
                if (select.hasAttribute('data-auto-submit') && select.form) {
                    if (select.form.requestSubmit) select.form.requestSubmit(); else select.form.submit();
                }
            }
        }

        tombol.addEventListener('click', function () {
            if (state.get().buka) tutup(); else buka();
        });

        tombol.addEventListener('keydown', function (e) {
            const s = state.get();
            const maks = select.options.length - 1;

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!s.buka) { buka(); return; }
                const geser = e.key === 'ArrowDown' ? 1 : -1;
                state.setState({ fokus: Math.min(maks, Math.max(0, s.fokus + geser)) });
            } else if (e.key === 'Home' && s.buka) {
                e.preventDefault();
                state.setState({ fokus: 0 });
            } else if (e.key === 'End' && s.buka) {
                e.preventDefault();
                state.setState({ fokus: maks });
            } else if ((e.key === 'Enter' || e.key === ' ') && s.buka) {
                e.preventDefault();
                pilih(s.fokus);
            } else if (e.key === 'Escape' && s.buka) {
                e.preventDefault();
                tutup();
            } else if (e.key === 'Tab' && s.buka) {
                tutup();
            }
        });

        daftar.addEventListener('click', function (e) {
            const li = e.target.closest('.dropdown-opsi');
            if (li) pilih(Number(li.dataset.index));
        });

        daftar.addEventListener('mousemove', function (e) {
            const li = e.target.closest('.dropdown-opsi');
            if (li) {
                const i = Number(li.dataset.index);
                if (state.get().fokus !== i) state.setState({ fokus: i });
            }
        });

        // Klik di luar dropdown menutup menu.
        document.addEventListener('click', function (e) {
            if (state.get().buka && !bungkus.contains(e.target)) tutup();
        });

        // Bila nilai <select> diubah dari luar (mis. skrip lain), tampilan ikut.
        select.addEventListener('change', function () {
            state.setState({ terpilih: select.selectedIndex });
        });
    }

    /* ---------- Inisialisasi ---------- */
    function mulai() {
        // Pesan flash dari server tampil sebagai snackbar.
        document.querySelectorAll('[data-snackbar]').forEach(function (el) {
            const pesan = el.textContent.trim();
            if (pesan) snackbar(pesan, el.dataset.snackbar);
            el.remove();
        });

        document.querySelectorAll('input[data-switch]').forEach(mulaiSwitch);
        mulaiGerbang();
        document.querySelectorAll('select[data-dropdown]').forEach(mulaiDropdown);
    }

    window.UI = { on: on, buatState: buatState, snackbar: snackbar, konfirmasi: konfirmasi };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai);
    } else {
        mulai();
    }
})();
