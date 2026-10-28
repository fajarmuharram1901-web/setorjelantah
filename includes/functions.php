<?php

function rupiah($angka) {
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
}

function liter($angka) {
    return number_format((float)$angka, 2, ',', '.') . ' L';
}

function hargaAktif() {
    $stmt = getDB()->query("SELECT harga_per_liter FROM harga ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch();
    return $row ? (float)$row['harga_per_liter'] : 0;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function statusBadge($status) {
    $map = [
        'menunggu'   => 'badge-warning',
        'diproses'   => 'badge-info',
        'selesai'    => 'badge-success',
        'dibatalkan' => 'badge-danger',
    ];
    $class = $map[$status] ?? 'badge-secondary';
    return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
