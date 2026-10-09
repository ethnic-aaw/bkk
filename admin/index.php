<?php
require __DIR__ . '/../config.php'; require __DIR__ . '/../inc/layout.php';
$p = $_GET['p'] ?? 'home';

if ($p === 'logout') { session_destroy(); header('Location: index.php?p=login'); exit; }
if ($p === 'login') {
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $u = row('select * from users where username=?', [$_POST['u'] ?? '']);
        if ($u && password_verify($_POST['pw'] ?? '', $u['pass'])) { session_regenerate_id(true); $_SESSION['adm'] = $u['id']; header('Location: index.php'); exit; }
        $err = 'Username atau kata sandi salah.';
    }
    top('Masuk admin'); ?>
    <main class="min-h-screen grid place-items-center px-4"><form method="post" class="bg-white rounded-2xl p-8 w-full max-w-sm border border-tinta/10 space-y-4">
      <h1 class="font-judul font-bold text-2xl">Masuk admin BKK</h1>
      <?php if ($err) echo '<p class="text-bata text-sm">' . e($err) . '</p>'; ?>
      <input name="u" placeholder="Username" required class="w-full rounded-lg border border-tinta/20 px-3 py-2">
      <input name="pw" type="password" placeholder="Kata sandi" required class="w-full rounded-lg border border-tinta/20 px-3 py-2">
      <button class="w-full rounded-lg bg-tinta text-white font-bold py-3">Masuk</button></form></main>
    <?php bottom(); exit;
}
if (empty($_SESSION['adm'])) { header('Location: index.php?p=login'); exit; }
if ($p === 'cv') { $f = basename($_GET['f'] ?? ''); $path = __DIR__ . "/../uploads/$f"; if (is_file($path)) { header('Content-Type: application/pdf'); readfile($path); } exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(csrf(), $_POST['t'] ?? '')) die('Sesi kedaluwarsa. Muat ulang halaman.');

// Definisi tabel: [label, field (kolom => [label, tipe]), kolom daftar (kolom => label), query daftar]
$T = [
 'lowongan' => ['Lowongan', ['judul'=>['Judul','text'],'mitra_id'=>['Perusahaan mitra','mitra'],'bidang'=>['Bidang / jurusan','text'],'tipe'=>['Tipe kerja','sel:Penuh waktu,Kontrak,Magang,Paruh waktu'],'lokasi'=>['Lokasi','text'],'gaji'=>['Gaji','text'],'deadline'=>['Batas lamaran','date'],'aktif'=>['Tayangkan','check'],'deskripsi'=>['Deskripsi','area'],'syarat'=>['Persyaratan (satu per baris)','area']],
   ['judul'=>'Judul','mitra'=>'Mitra','bidang'=>'Bidang','deadline'=>'Batas','aktif'=>'Tayang'], 'select l.*, m.nama mitra from lowongan l left join mitra m on m.id=l.mitra_id order by l.id desc'],
 'mitra' => ['Mitra', ['nama'=>['Nama perusahaan','text'],'jenis'=>['Jenis','sel:PT,CV,Tempat usaha'],'bidang'=>['Bidang','text'],'kota'=>['Kota','text'],'status_mou'=>['Status MOU','sel:Sudah MOU,Belum MOU'],'mou_mulai'=>['MOU mulai','date'],'mou_selesai'=>['MOU berakhir','date'],'kerjasama'=>['Bentuk kerja sama','text'],'kontrak'=>['Skema kontrak kerja','area'],'kontak'=>['Kontak','text']],
   ['nama'=>'Nama','jenis'=>'Jenis','kota'=>'Kota','status_mou'=>'Status MOU','mou_selesai'=>'MOU berakhir'], 'select * from mitra order by id desc'],
 'struktur' => ['Struktur BKK', ['jabatan'=>['Jabatan','text'],'nama'=>['Nama pejabat','text'],'parent_id'=>['Atasan langsung','parent'],'urut'=>['Urutan','number']],
   ['jabatan'=>'Jabatan','nama'=>'Nama','atasan'=>'Atasan','urut'=>'Urutan'], 'select s.*, a.jabatan atasan from struktur s left join struktur a on a.id=s.parent_id order by s.id'],
 'sambutan' => ['Sambutan pengurus', ['foto'=>['Foto pengurus (JPG/PNG/WebP, maks 2 MB)','img'],'jabatan'=>['Jabatan','text'],'nama'=>['Nama','text'],'urut'=>['Urutan','number'],'isi'=>['Isi sambutan','area']],
   ['jabatan'=>'Jabatan','nama'=>'Nama','urut'=>'Urutan'], 'select * from sambutan order by urut,id'],
 'keunggulan' => ['Keunggulan', ['ikon'=>['Ikon (emoji)','text'],'judul'=>['Judul','text'],'isi'=>['Isi','area']], ['ikon'=>'Ikon','judul'=>'Judul','isi'=>'Isi'], 'select * from keunggulan order by id'],
 'pengumuman' => ['Pengumuman', ['foto'=>['Foto pengumuman (JPG/PNG/WebP, maks 2 MB, opsional)','img'],'judul'=>['Judul','text'],'tanggal'=>['Tanggal','date'],'isi'=>['Isi','area']], ['judul'=>'Judul','tanggal'=>'Tanggal'], 'select * from pengumuman order by tanggal desc'],
 'testimoni' => ['Testimoni alumni', ['nama'=>['Nama','text'],'angkatan'=>['Angkatan','text'],'posisi'=>['Posisi dan tempat kerja','text'],'isi'=>['Testimoni','area']], ['nama'=>'Nama','angkatan'=>'Angkatan','posisi'=>'Posisi'], 'select * from testimoni order by id desc'],
 'lamaran' => ['Lamaran masuk', ['status'=>['Status','sel:Baru,Diproses,Diterima,Ditolak']],
   ['created'=>'Masuk','nama'=>'Pelamar','wa'=>'WhatsApp','lowongan'=>'Lowongan','sekolah'=>'Sekolah','cv'=>'CV','status'=>'Status'], 'select a.*, l.judul lowongan from lamaran a left join lowongan l on l.id=a.lowongan_id order by a.id desc'],
];

function opts($f) {
    if ($f === 'mitra') return array_column(rows('select id,nama from mitra order by nama'), 'nama', 'id');
    if ($f === 'parent') return ['' => '(puncak struktur)'] + array_column(rows('select id,jabatan from struktur order by id'), 'jabatan', 'id');
    return array_combine($o = explode(',', substr($f, 4)), $o);
}
function field($k, $f, $v) {
    [$lb, $t] = $f; $cl = 'w-full rounded-lg border border-tinta/20 px-3 py-2';
    echo '<label class="block text-sm font-semibold">' . e($lb);
    if ($t === 'area') echo "<textarea name=\"$k\" rows=\"4\" class=\"$cl font-normal\">" . e($v) . '</textarea>';
    elseif ($t === 'img') {
        if ($v) echo '<img src="../media/' . e($v) . '" alt="" class="mt-1 h-24 w-24 rounded-lg object-cover border border-tinta/10">';
        echo '<input type="file" name="' . $k . '" accept="image/jpeg,image/png,image/webp" class="mt-1 block text-sm font-normal"><input type="hidden" name="cur_' . $k . '" value="' . e($v) . '">';
    }
    elseif ($t === 'check') echo "<input type=\"checkbox\" name=\"$k\" class=\"ml-2\"" . ($v ?? 1 ? ' checked' : '') . '>';
    elseif ($t === 'mitra' || $t === 'parent' || str_starts_with($t, 'sel:')) {
        echo "<select name=\"$k\" class=\"$cl font-normal\">";
        foreach (opts($t) as $ov => $ol) echo '<option value="' . e($ov) . '"' . ((string)$ov === (string)$v ? ' selected' : '') . '>' . e($ol) . '</option>';
        echo '</select>';
    } else echo "<input type=\"$t\" name=\"$k\" value=\"" . e($v) . "\" class=\"$cl font-normal\">";
    echo '</label>';
}

// Simpan / hapus
function upload_img($k) {
    if (empty($_FILES[$k]['tmp_name'])) return '';
    $f = $_FILES[$k]; $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || $f['size'] > 2 * 1024 * 1024) return '';
    $dir = __DIR__ . '/../media'; if (!is_dir($dir)) mkdir($dir, 0775, true);
    $n = bin2hex(random_bytes(8)) . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], "$dir/$n") ? $n : '';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($T[$p])) {
    [, $F] = $T[$p];
    if (($_POST['act'] ?? '') === 'del') q("delete from $p where id=?", [(int)$_POST['id']]);
    else {
        $v = [];
        foreach ($F as $k => $f) {
            if ($f[1] === 'img') { $v[$k] = upload_img($k) ?: ($_POST['cur_' . $k] ?? ''); continue; }
            $x = $_POST[$k] ?? ''; if ($f[1] === 'check') $x = isset($_POST[$k]) ? 1 : 0; if ($k === 'parent_id' && $x === '') $x = null; $v[$k] = $x;
        }
        if ($id = (int)($_POST['id'] ?? 0)) q("update $p set " . implode(',', array_map(fn($k) => "$k=?", array_keys($v))) . " where id=?", [...array_values($v), $id]);
        else q("insert into $p(" . implode(',', array_keys($v)) . ") values(" . implode(',', array_fill(0, count($v), '?')) . ")", array_values($v));
    }
    header("Location: index.php?p=$p"); exit;
}
if ($p === 'set' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) if (!in_array($k, ['t', 'pw'])) q('insert or replace into settings(k,v) values(?,?)', [$k, $v]);
    if (strlen($_POST['pw'] ?? '') >= 6) q('update users set pass=? where id=?', [password_hash($_POST['pw'], PASSWORD_DEFAULT), $_SESSION['adm']]);
    header('Location: index.php?p=set&ok=1'); exit;
}

top('Admin'); echo '<main class="max-w-6xl mx-auto px-4 py-8">';

if ($p === 'home') {
    $c = fn($s) => row($s)['c'];
    echo '<h1 class="font-judul font-bold text-3xl">Dashboard</h1><div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">';
    foreach ([[$c("select count(*) c from lowongan where aktif=1"), 'Lowongan tayang'], [$c("select count(*) c from mitra"), 'Mitra'], [$c("select count(*) c from mitra where status_mou='Sudah MOU'"), 'Sudah MOU'], [$c("select count(*) c from lamaran where status='Baru'"), 'Lamaran baru']] as [$n, $l])
        echo "<div class=\"bg-white rounded-2xl p-5 border border-tinta/10\"><div class=\"font-judul font-extrabold text-4xl text-daun\">$n</div><div class=\"text-sm\">$l</div></div>";
    echo '</div><h2 class="font-judul font-bold text-xl mt-10">Lamaran terbaru</h2><div class="mt-3 bg-white rounded-2xl border border-tinta/10 divide-y divide-tinta/10">';
    $r = rows('select a.*, l.judul from lamaran a left join lowongan l on l.id=a.lowongan_id order by a.id desc limit 6');
    foreach ($r as $a) echo '<a href="index.php?p=lamaran&id=' . $a['id'] . '" class="flex justify-between gap-3 p-4 hover:bg-kertas"><span><b>' . e($a['nama']) . '</b> melamar ' . e($a['judul']) . '</span><span class="text-sm text-tinta/60">' . e($a['status']) . '</span></a>';
    if (!$r) echo '<p class="p-4 text-sm text-tinta/60">Belum ada lamaran. Lamaran dari halaman lowongan akan muncul di sini.</p>';
    echo '</div>';
} elseif ($p === 'set') {
    echo '<h1 class="font-judul font-bold text-3xl">Pengaturan tampilan dan kontak</h1>' . (isset($_GET['ok']) ? '<p class="mt-3 text-daun font-semibold">Perubahan disimpan.</p>' : '');
    echo '<form method="post" class="mt-6 bg-white rounded-2xl border border-tinta/10 p-6 grid md:grid-cols-2 gap-4"><input type="hidden" name="t" value="' . csrf() . '">';
    foreach (['nama_sekolah'=>['Nama sekolah','text'],'singkatan'=>['Singkatan logo (maks 3 huruf)','text'],'hero_judul'=>['Judul utama beranda','text'],'hero_teks'=>['Teks pengantar beranda','area'],'tentang'=>['Tentang BKK','area'],'alamat'=>['Alamat','text'],'telepon'=>['Telepon','text'],'wa'=>['WhatsApp (format 628xxx)','text'],'email'=>['Email','text'],'jam'=>['Jam layanan','text'],'instagram'=>['Instagram','text']] as $k => $f) field($k, $f, cfg($k));
    echo '<label class="block text-sm font-semibold md:col-span-2">Ganti kata sandi admin (kosongkan jika tidak diganti, minimal 6 karakter)<input type="password" name="pw" class="mt-1 w-full rounded-lg border border-tinta/20 px-3 py-2 font-normal"></label><button class="md:col-span-2 rounded-lg bg-tinta text-white font-bold py-3">Simpan pengaturan</button></form>';
} elseif (isset($T[$p])) {
    [$label, $F, $L, $sql] = $T[$p]; $ro = $p === 'lamaran';
    if (isset($_GET['id'])) {   // form tambah / ubah
        $id = (int)$_GET['id']; $cur = $id ? row("select * from $p where id=?", [$id]) : [];
        echo '<a href="index.php?p=' . $p . '" class="text-sm underline">Kembali</a><h1 class="font-judul font-bold text-3xl mt-2">' . ($id ? 'Ubah' : 'Tambah') . ' ' . e($label) . '</h1>';
        if ($ro && $cur) echo '<div class="mt-4 bg-white rounded-2xl border border-tinta/10 p-5 text-sm space-y-1"><p><b>' . e($cur['nama']) . '</b>, ' . e($cur['email']) . ', ' . e($cur['wa']) . '</p><p>' . e($cur['sekolah']) . ', ' . e($cur['jurusan']) . '</p><p>' . nl2br(e($cur['pesan'])) . '</p>' . ($cur['cv'] ? '<p><a class="underline text-daun" target="_blank" href="index.php?p=cv&f=' . e($cur['cv']) . '">Buka CV (PDF)</a></p>' : '') . '</div>';
        echo '<form method="post" enctype="multipart/form-data" class="mt-6 bg-white rounded-2xl border border-tinta/10 p-6 grid md:grid-cols-2 gap-4"><input type="hidden" name="t" value="' . csrf() . '"><input type="hidden" name="id" value="' . $id . '">';
        foreach ($F as $k => $f) { echo in_array($f[1], ['area', 'img']) ? '<div class="md:col-span-2">' : '<div>'; field($k, $f, $cur[$k] ?? ($k === 'tanggal' ? date('Y-m-d') : null)); echo '</div>'; }
        echo '<button class="md:col-span-2 rounded-lg bg-tinta text-white font-bold py-3">Simpan</button></form>';
    } else {   // daftar
        echo '<div class="flex items-center justify-between"><h1 class="font-judul font-bold text-3xl">' . e($label) . '</h1>' . ($ro ? '' : '<a href="index.php?p=' . $p . '&id=0" class="rounded-lg bg-daun text-white font-semibold px-4 py-2">Tambah data</a>') . '</div>';
        echo '<div class="mt-6 bg-white rounded-2xl border border-tinta/10 overflow-x-auto"><table class="w-full text-sm"><thead class="text-left bg-tinta/5"><tr>';
        foreach ($L as $lb) echo '<th class="p-3">' . $lb . '</th>';
        echo '<th class="p-3"></th></tr></thead><tbody class="divide-y divide-tinta/10">';
        foreach (rows($sql) as $r) {
            echo '<tr>';
            foreach ($L as $k => $_) {
                $v = $r[$k] ?? '';
                echo '<td class="p-3 align-top max-w-xs truncate">' . ($k === 'aktif' ? ($v ? 'Ya' : 'Tidak') : ($k === 'cv' ? ($v ? '<a class="underline text-daun" target="_blank" href="index.php?p=cv&f=' . e($v) . '">PDF</a>' : '-') : ($k === 'status_mou' ? badge($v) : e($v)))) . '</td>';
            }
            echo '<td class="p-3 whitespace-nowrap text-right"><a class="underline mr-3" href="index.php?p=' . $p . '&id=' . $r['id'] . '">' . ($ro ? 'Detail' : 'Ubah') . '</a><form method="post" class="inline" onsubmit="return confirm(\'Hapus data ini?\')"><input type="hidden" name="t" value="' . csrf() . '"><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="text-bata underline">Hapus</button></form></td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
echo '</main>'; bottom();
