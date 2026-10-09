<?php
function top($title = '') {
    $s = cfg('nama_sekolah'); $ab = !empty($_SESSION['adm']);
    ?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ? "$title - BKK $s" : "BKK $s") ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{tinta:'#0E2F3A',daun:'#1F8A70',kunyit:'#F5B335',kertas:'#F4F6F3',bata:'#C8553D'},fontFamily:{judul:['Bricolage Grotesque','sans-serif'],sans:['Plus Jakarta Sans','sans-serif']}}}}</script>
<link rel="stylesheet" href="<?= ROOT ?>assets/style.css">
</head><body class="bg-kertas text-tinta font-sans antialiased">
<?php if (ADMIN): if ($ab): ?>
<header class="bg-tinta text-white"><div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-3">
<b class="font-judul shrink-0">Admin BKK</b>
<nav class="flex gap-1 overflow-x-auto text-sm whitespace-nowrap flex-1">
<?php foreach (['home'=>'Dashboard','lowongan'=>'Lowongan','mitra'=>'Mitra','lamaran'=>'Lamaran','struktur'=>'Struktur','sambutan'=>'Sambutan','keunggulan'=>'Keunggulan','pengumuman'=>'Pengumuman','testimoni'=>'Testimoni','set'=>'Pengaturan'] as $k=>$v): ?>
<a class="px-3 py-1.5 rounded-lg hover:bg-white/10 <?= ($_GET['p'] ?? 'home')===$k?'bg-white/15':'' ?>" href="index.php?p=<?= $k ?>"><?= $v ?></a>
<?php endforeach; ?>
</nav>
<div class="flex items-center gap-1 shrink-0">
<a class="px-3 py-1.5 rounded-lg text-kunyit hover:bg-white/10" href="../index.php" target="_blank">Lihat situs</a>
<a class="px-3 py-1.5 rounded-lg bg-bata font-semibold hover:brightness-110" href="index.php?p=logout">Keluar</a>
</div></div></header>
<?php endif; else: ?>
<header class="sticky top-0 z-40 bg-tinta/95 backdrop-blur text-white"><div class="max-w-6xl mx-auto px-4 h-16 flex items-center gap-4">
<a href="index.php" class="flex items-center gap-2 font-judul font-bold text-lg"><span class="grid place-items-center h-9 w-9 rounded-lg bg-kunyit text-tinta"><?= e(substr(cfg('singkatan', 'BKK'), 0, 3)) ?></span><span class="leading-tight"><?= e($s) ?></span></a>
<nav id="menu" class="hidden md:flex absolute md:static top-16 inset-x-0 bg-tinta md:bg-transparent flex-col md:flex-row gap-1 md:gap-5 p-4 md:p-0 md:ml-auto text-sm font-medium">
<?php foreach (['index.php#keunggulan'=>'Keunggulan','index.php#mitra'=>'Mitra dan MOU','lowongan.php'=>'Lowongan','index.php#struktur'=>'Struktur BKK','index.php#kontak'=>'Kontak'] as $h=>$l): ?>
<a class="py-2 hover:text-kunyit" href="<?= $h ?>"><?= $l ?></a><?php endforeach; ?></nav>
<button id="menuBtn" class="md:hidden ml-auto p-2" aria-label="Menu"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button></div></header>
<?php endif;
}
function bottom() {
    if (ADMIN) { echo '</body></html>'; return; }
    ?>
<footer class="bg-tinta text-white/80 mt-20"><div class="max-w-6xl mx-auto px-4 py-10 grid gap-8 md:grid-cols-3 text-sm">
<div><b class="font-judul text-white text-lg">BKK <?= e(cfg('nama_sekolah')) ?></b><p class="mt-2"><?= e(cfg('tentang')) ?></p></div>
<div><b class="text-white">Kontak</b><p class="mt-2"><?= e(cfg('alamat')) ?><br><?= e(cfg('telepon')) ?><br><?= e(cfg('email')) ?></p></div>
<div><b class="text-white">Jam layanan</b><p class="mt-2"><?= e(cfg('jam')) ?><br>Instagram <?= e(cfg('instagram')) ?></p></div></div>
<div class="border-t border-white/10 text-center text-xs py-4">&copy; <?= date('Y') ?> BKK <?= e(cfg('nama_sekolah')) ?>. Dikembangkan oleh <b class="text-white">UP Jurusan PPLG SMKN 1 Leuwimunding</b>. <a class="underline" href="admin/">Admin</a></div></footer>
<script src="assets/app.js"></script></body></html>
<?php }
