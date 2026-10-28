/**
 * Validasi langsung di peramban.
 *
 * Aturan diambil dari PHP (AturanValidasi::untukPeramban) supaya pesan di
 * layar sama persis dengan pesan dari server, tidak ada dua daftar terpisah
 * yang bisa berbeda.
 *
 * Cara pakai di Blade:
 *   <form data-validasi data-form="daftar"> ... </form>
 *   <x-form-validasi :form="'daftar'" />
 */
(function () {
    'use strict';

    const POLA_NAMA = /^[\p{L}\s\-’'.]+$/u;
    const POLA_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    /** Angka berformat Indonesia (1.000,50) diubah menjadi angka biasa. */
    function keAngka(nilai) {
        if (typeof nilai === 'number') return nilai;
        const teks = String(nilai).trim().replace(/\s/g, '').replace(/\./g, '').replace(',', '.');
        const hasil = parseFloat(teks);
        return isNaN(hasil) ? NaN : hasil;
    }

    function rupiah(nilai) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(nilai);
    }

    /** Ambil pesan yang tersedia, dengan teks cadangan bila AturanValidasi tidak mengirim. */
    function pesan(aturan, jenis, bawaan) {
        const p = (aturan && aturan.pesan && aturan.pesan[jenis]) || bawaan;
        return p;
    }

    /**
     * Periksa satu kolom. Mengembalikan pesan galat, atau string kosong
     * bila sudah benar.
     */
    function periksaKolom(nama, aturan, form) {
        const el = form.querySelector('[name="' + nama + '"]');
        if (!el) return '';

        const nilai = (el.value || '').trim();
        const wajib = !!aturan.wajib;

        // Kolom opsional yang kosong tidak perlu dicek apa pun.
        if (nilai === '') {
            return wajib ? pesan(aturan, 'kosong', ':attribute wajib diisi.') : '';
        }

        switch (aturan.tipe) {
            case 'nama':
                if (!POLA_NAMA.test(nilai)) {
                    return pesan(aturan, 'pola', 'Nama hanya boleh berisi huruf.');
                }
                break;

            case 'email':
                if (!POLA_EMAIL.test(nilai)) {
                    return pesan(aturan, 'format', 'Format email tidak valid.');
                }
                break;

            case 'angka': {
                const angka = keAngka(nilai);
                if (isNaN(angka)) {
                    return pesan(aturan, 'angka', 'Isi hanya dengan angka.');
                }
                if (aturan.bulat && angka !== Math.floor(angka)) {
                    return (aturan.label || 'Nilai') + ' harus berupa bilangan bulat.';
                }
                if (aturan.minAngka !== undefined && angka < aturan.minAngka) {
                    return pesan(aturan, 'minAngka', 'Minimal ' + aturan.minAngka + '.');
                }
                if (aturan.maksAngka !== undefined && angka > aturan.maksAngka) {
                    return pesan(aturan, 'maksAngka', 'Maksimal ' + rupiah(aturan.maksAngka) + '.');
                }
                break;
            }

            case 'sandi':
                // Perbandingan dengan kolom konfirmasi ditangani terpisah,
                // jadi di sini cukup lanjut ke cek panjang di bawah.
                break;
        }

        // Batas jumlah karakter berlaku untuk semua kolom teks.
        if (aturan.maks !== undefined && nilai.length > aturan.maks) {
            return pesan(aturan, 'maks', 'Maksimal ' + aturan.maks + ' karakter.');
        }
        if (aturan.min !== undefined && nilai.length < aturan.min) {
            return pesan(aturan, 'min', 'Minimal ' + aturan.min + ' karakter.');
        }


        return '';
    }

    /** Tampilkan atau bersihkan pesan galat di bawah sebuah kolom. */
    function tampil(form, nama, teks) {
        const el = form.querySelector('[name="' + nama + '"]');
        if (!el) return;

        let kotak = form.querySelector('[galat-for="' + nama + '"]');

        if (!teks) {
            if (kotak) {
                kotak.textContent = '';
                kotak.hidden = true;
            }
            el.classList.remove('is-invalid');
            el.removeAttribute('aria-invalid');
            return;
        }

        if (!kotak) {
            kotak = document.createElement('small');
            kotak.className = 'galat-lapangan';
            kotak.setAttribute('galat-for', nama);
            kotak.setAttribute('role', 'alert');
            (el.closest('div') || el.parentNode).appendChild(kotak);
        }

        kotak.textContent = teks;
        kotak.hidden = false;
        el.classList.add('is-invalid');
        el.setAttribute('aria-invalid', 'true');
    }

    /** Periksa seluruh kolom dalam satu formulir. */
    function periksaForm(form, aturan) {
        let adaGalat = false;

        Object.keys(aturan).forEach(function (nama) {
            const galat = periksaKolom(nama, aturan[nama], form);
            tampil(form, nama, galat);
            if (galat) adaGalat = true;
        });

        // Kolom konfirmasi kata sandi tidak punya aturannya sendiri, jadi
        // diperiksa terpisah memakai aturan dari kolom password.
        const konfirmasi = form.querySelector('[name="password_confirmation"]');
        if (konfirmasi && aturan.password) {
            const nilai = (konfirmasi.value || '').trim();
            let galat = '';
            if (nilai !== '') {
                if (nilai !== (form.querySelector('[name="password"]') || {}).value) {
                    galat = pesan(aturan.password, 'koncoc', 'Konfirmasi kata sandi tidak sama.');
                } else if (nilai.length < (aturan.password.min || 0)) {
                    galat = pesan(aturan.password, 'min', 'Minimal ' + (aturan.password.min || 0) + ' karakter.');
                }
            } else if (form.dataset.sandiWajib === '1') {
                galat = 'Konfirmasi kata sandi wajib diisi.';
            }
            tampil(form, 'password_confirmation', galat);
            if (galat) adaGalat = true;
        }

        return adaGalat;
    }

    /** Cek ke server untuk aturan yang tidak bisa dipastikan peramban (mis. email unik). */
    function cekServer(el, nama, form) {
        const nilai = (el.value || '').trim();
        if (nilai === '' || !POLA_EMAIL.test(nilai)) return;

        const daftar = window.AturanValidasiServer || {};
        const panggil = daftar[nama];
        if (!panggil) return;

        el.classList.add('is-checking');

        panggil(nilai, function (sudahDipakai) {
            el.classList.remove('is-checking');
            const galat = sudahDipakai
                ? (form.__aturan[nama].pesan.server || 'Nilai ini sudah dipakai.')
                : '';

            // Jangan menimpa galat format yang sudah tampil lebih dulu.
            const sekarang = form.querySelector('[galat-for="' + nama + '"]');
            if (!sekarang || sekarang.hidden) {
                tampil(form, nama, galat);
            }
        });
    }

    /** Pesan galat untuk kolom konfirmasi kata sandi. */
    function galatKonfirmasi(form, aturan) {
        const konfirmasi = form.querySelector('[name="password_confirmation"]');
        const asli = form.querySelector('[name="password"]');
        if (!konfirmasi || !asli) return '';

        const nilai = (konfirmasi.value || '').trim();
        if (nilai === '') {
            return form.dataset.sandiWajib === '1' ? 'Konfirmasi kata sandi wajib diisi.' : '';
        }

        if (nilai !== asli.value) {
            return pesan(aturan, 'koncoc', 'Konfirmasi kata sandi tidak sama.');
        }

        if (aturan.min !== undefined && nilai.length < aturan.min) {
            return pesan(aturan, 'min', 'Minimal ' + aturan.min + ' karakter.');
        }

        return '';
    }
    /** Pasang mesin validasi pada satu formulir. */
    function pasang(form) {
        const aturan = form.__aturan;
        if (!aturan) return;

        const kolomServer = Object.keys(aturan).filter(function (n) { return aturan[n].server; });

        Object.keys(aturan).forEach(function (nama) {
            const el = form.querySelector('[name="' + nama + '"]');
            if (!el) return;

            // Tampilkan galat saat meninggalkan kolom, atau saat sudah pernah
            // diketik dan dikoreksi. Jangan mengganggu saat baru mengetik.
            let sudahDisinggahi = false;

            el.addEventListener('blur', function () {
                sudahDisinggahi = true;
                const galat = periksaKolom(nama, aturan[nama], form);
                if (!galat && kolomServer.indexOf(nama) !== -1) cekServer(el, nama, form);
                else tampil(form, nama, galat);
            });

            el.addEventListener('input', function () {
                if (!sudahDisinggahi) return;
                // Hanya perbaiki pesan kalau kolomnya sudah pernah bermasalah,
                // supaya galat tidak berkedip saat pengguna baru mengetik.
                const kotak = form.querySelector('[galat-for="' + nama + '"]');
                if (kotak && !kotak.hidden) {
                    tampil(form, nama, periksaKolom(nama, aturan[nama], form));
                }
            });
        });

        // Konfirmasi kata sandi tidak punya aturan sendiri, jadi disambungkan
        // ke aturan kolom password.
        const kolomKonfirmasi = form.querySelector('[name="password_confirmation"]');
        if (kolomKonfirmasi && aturan.password) {
            let sudahDisinggahi = false;

            kolomKonfirmasi.addEventListener('blur', function () {
                sudahDisinggahi = true;
                tampil(form, 'password_confirmation', galatKonfirmasi(form, aturan.password));
            });

            kolomKonfirmasi.addEventListener('input', function () {
                if (!sudahDisinggahi) return;
                const kotak = form.querySelector('[galat-for="password_confirmation"]');
                if (kotak && !kotak.hidden) {
                    tampil(form, 'password_confirmation', galatKonfirmasi(form, aturan.password));
                }
            });
        }
        // Jangan biarkan halaman terkirim kalau masih ada yang salah.
        form.addEventListener('submit', function (event) {
            if (periksaForm(form, aturan)) {
                event.preventDefault();
                const pertama = form.querySelector('.is-invalid');
                if (pertama) {
                    pertama.focus();
                    pertama.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    // ---------------------------------------------------------------------
    // Mulai setelah seluruh halaman siap.
    // ---------------------------------------------------------------------
    function mulai() {
        document.querySelectorAll('form[data-validasi]').forEach(function (form) {
            // Aturan dikirim lewat <script type="application/json" data-aturan="nama-form">
            const sumber = document.querySelector('script[data-aturan="' + form.dataset.form + '"]');
            if (!sumber) return;

            try {
                form.__aturan = JSON.parse(sumber.textContent);
            } catch (e) {
                return;
            }

            pasang(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai);
    } else {
        mulai();
    }
})();
