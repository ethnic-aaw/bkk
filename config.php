<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
define('ADMIN', str_contains(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/admin'));
define('ROOT', ADMIN ? '../' : '');

function db(): PDO {
    static $p;
    if ($p) return $p;
    $f = __DIR__ . '/data/bkk.sqlite';
    $new = !is_file($f);
    $p = new PDO('sqlite:' . $f);
    $p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $p->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    if ($new) require __DIR__ . '/inc/seed.php';   // buat tabel + data contoh saat pertama dijalankan
    // migrasi ringan: tabel sambutan pengurus (aman dijalankan tiap akses)
    $p->exec("create table if not exists sambutan(id integer primary key, jabatan text, nama text, isi text, urut integer default 0)");
    if (!$p->query("select count(*) from sambutan")->fetchColumn()) {
        $s = $p->prepare('insert into sambutan(jabatan,nama,isi,urut) values(?,?,?,?)');
        foreach ([
            ['Kepala Sekolah', 'Nama Kepala Sekolah', 'Selamat datang di portal BKK sekolah kami. Kami berkomitmen menjembatani lulusan SMK dengan dunia kerja yang layak dan terpercaya.', 1],
            ['Wakasek Hubungan Industri', 'Nama Wakasek', 'Kerja sama dengan industri adalah kunci. Kami terus menambah mitra ber-MOU agar peluang kerja lulusan semakin luas.', 2],
            ['Ketua BKK', 'Nama Ketua BKK', 'Layanan BKK gratis untuk semua lulusan SMK. Manfaatkan portal ini untuk mencari lowongan, menyiapkan CV, dan mengikuti seleksi.', 3],
            ['Admin BKK', 'Nama Admin', 'Butuh bantuan pendaftaran atau ada kendala saat melamar? Hubungi kami lewat WhatsApp resmi, kami siap membantu.', 4],
        ] as $r) $s->execute($r);
    }
    // migrasi: kolom foto untuk sambutan dan pengumuman
    foreach (['sambutan', 'pengumuman'] as $t) {
        try { $p->exec("alter table $t add column foto text"); } catch (Throwable $e) {}
    }
    return $p;
}
function q($sql, $a = []) { $s = db()->prepare($sql); $s->execute($a); return $s; }
function rows($sql, $a = []) { return q($sql, $a)->fetchAll(); }
function row($sql, $a = []) { return q($sql, $a)->fetch(); }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function cfg($k, $d = '') { $c = array_column(rows('select k,v from settings'), 'v', 'k'); return $c[$k] ?? $d; }
function csrf() { return $_SESSION['t'] ??= bin2hex(random_bytes(16)); }
function tgl($d) {
    if (!$d) return '-';
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function badge($m) {
    return $m === 'Sudah MOU'
        ? '<span class="inline-flex items-center gap-1 rounded-full bg-daun/10 text-daun px-2.5 py-0.5 text-xs font-semibold">&#10003; Sudah MOU</span>'
        : '<span class="inline-flex items-center gap-1 rounded-full bg-bata/10 text-bata px-2.5 py-0.5 text-xs font-semibold">Belum MOU</span>';
}
