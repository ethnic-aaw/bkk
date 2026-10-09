<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
$a = $_GET['a'] ?? '';
function out($d, $code = 200) { http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

if ($a === 'jobs') {
    $w = ['l.aktif=1', "(l.deadline='' or l.deadline>=date('now','localtime'))"]; $p = [];
    if (($v = trim($_GET['q'] ?? '')) !== '') { $w[] = '(l.judul like ? or m.nama like ? or l.lokasi like ?)'; array_push($p, "%$v%", "%$v%", "%$v%"); }
    foreach (['bidang' => 'l.bidang', 'tipe' => 'l.tipe', 'mou' => 'm.status_mou'] as $k => $col)
        if (($v = $_GET[$k] ?? '') !== '') { $w[] = "$col=?"; $p[] = $v; }
    out(rows("select l.*, m.nama mitra, m.status_mou mou, m.kontrak from lowongan l join mitra m on m.id=l.mitra_id where " . implode(' and ', $w) . " order by l.id desc limit 60", $p));
}

if ($a === 'apply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['web'])) out(['ok' => true]);              // honeypot untuk bot
    $f = [];
    foreach (['lowongan_id', 'nama', 'email', 'wa', 'sekolah', 'jurusan', 'pesan'] as $k) $f[$k] = trim($_POST[$k] ?? '');
    if (!$f['nama'] || !filter_var($f['email'], FILTER_VALIDATE_EMAIL) || !$f['wa'])
        out(['ok' => false, 'msg' => 'Nama, email yang valid, dan nomor WhatsApp wajib diisi.'], 422);
    if (!row('select id from lowongan where id=? and aktif=1', [$f['lowongan_id']])) out(['ok' => false, 'msg' => 'Lowongan tidak ditemukan.'], 404);
    $f['cv'] = '';
    if (!empty($_FILES['cv']['tmp_name'])) {
        $okf = $_FILES['cv']['size'] <= 2 * 1024 * 1024 && (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['cv']['tmp_name']) === 'application/pdf';
        if (!$okf) out(['ok' => false, 'msg' => 'CV harus berupa PDF dengan ukuran maksimal 2 MB.'], 422);
        $f['cv'] = bin2hex(random_bytes(8)) . '.pdf';
        move_uploaded_file($_FILES['cv']['tmp_name'], __DIR__ . '/uploads/' . $f['cv']);
    }
    $k = array_keys($f);
    q('insert into lamaran(' . implode(',', $k) . ') values(' . implode(',', array_fill(0, count($k), '?')) . ')', array_values($f));
    out(['ok' => true, 'msg' => 'Lamaran terkirim. Tim BKK akan menghubungi lewat WhatsApp atau email.']);
}
out(['ok' => false, 'msg' => 'Permintaan tidak dikenal.'], 400);
