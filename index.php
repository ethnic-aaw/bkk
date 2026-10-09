<?php
require __DIR__ . '/config.php'; require __DIR__ . '/inc/layout.php';
$c = fn($sql) => row($sql)['c'];
$st = [
  [$c("select count(*) c from lowongan where aktif=1"), 'Lowongan terbuka'],
  [$c("select count(*) c from mitra"), 'Perusahaan mitra'],
  [$c("select count(*) c from mitra where status_mou='Sudah MOU'"), 'Sudah ber-MOU'],
  [$c("select count(*) c from lamaran"), 'Lamaran masuk'],
];
$bidang = array_column(rows("select distinct bidang from lowongan where aktif=1 order by 1"), 'bidang');
function tree($pid) {
    $r = $pid ? rows('select * from struktur where parent_id=? order by urut,id', [$pid]) : rows('select * from struktur where parent_id is null order by urut,id');
    if (!$r) return;
    echo '<ul>';
    foreach ($r as $n) { echo '<li><div class="node"><b>' . e($n['jabatan']) . '</b><span>' . e($n['nama']) . '</span></div>'; tree($n['id']); echo '</li>'; }
    echo '</ul>';
}
top();
?>
<section class="bg-tinta text-white"><div class="max-w-6xl mx-auto px-4 py-14 md:py-20 grid lg:grid-cols-2 gap-10 items-center">
  <div>
    <p class="text-kunyit font-semibold">Bursa Kerja Khusus <?= e(cfg('nama_sekolah')) ?></p>
    <h1 class="font-judul font-extrabold text-4xl md:text-6xl leading-[1.05] mt-3"><?= e(cfg('hero_judul')) ?></h1>
    <p class="mt-5 text-white/80 max-w-lg"><?= e(cfg('hero_teks')) ?></p>
    <form id="heroSearch" action="lowongan.php" class="mt-7 grid sm:grid-cols-[1fr_auto_auto] gap-2">
      <input name="q" id="hq" placeholder="Cari posisi atau perusahaan" class="rounded-xl px-4 py-3 text-tinta" aria-label="Cari lowongan">
      <select name="bidang" id="hb" class="rounded-xl px-3 py-3 text-tinta" aria-label="Bidang"><option value="">Semua bidang</option><?php foreach ($bidang as $b) echo '<option>' . e($b) . '</option>'; ?></select>
      <button class="rounded-xl bg-kunyit text-tinta font-bold px-5 py-3 hover:brightness-95">Cari lowongan</button>
    </form>
    <div class="mt-4 flex flex-wrap gap-2 text-sm"><?php foreach ($bidang as $b): ?><button type="button" data-chip="<?= e($b) ?>" class="rounded-full border border-white/25 px-3 py-1 hover:bg-white/10"><?= e($b) ?></button><?php endforeach; ?></div>
  </div>
  <div class="bg-white text-tinta rounded-2xl p-3 shadow-xl">
    <div class="flex items-center justify-between px-3 py-2"><b class="font-judul text-lg">Lowongan terbuka</b><span id="heroCount" class="text-sm text-tinta/60"></span></div>
    <div id="heroJobs" class="divide-y divide-tinta/10 max-h-[380px] overflow-y-auto">
      <div id="heroSkeleton" class="space-y-3 p-3">
        <div class="animate-pulse"><div class="h-4 bg-tinta/10 rounded w-3/4 mb-2"></div><div class="h-3 bg-tinta/10 rounded w-1/2"></div><div class="h-3 bg-tinta/10 rounded w-1/3 mt-1"></div></div>
        <div class="animate-pulse"><div class="h-4 bg-tinta/10 rounded w-3/4 mb-2"></div><div class="h-3 bg-tinta/10 rounded w-1/2"></div><div class="h-3 bg-tinta/10 rounded w-1/3 mt-1"></div></div>
        <div class="animate-pulse"><div class="h-4 bg-tinta/10 rounded w-3/4 mb-2"></div><div class="h-3 bg-tinta/10 rounded w-1/2"></div><div class="h-3 bg-tinta/10 rounded w-1/3 mt-1"></div></div>
      </div>
    </div>
  </div>
</div></section>

<section class="max-w-6xl mx-auto px-4 -mt-8 relative"><div class="grid grid-cols-2 md:grid-cols-4 bg-white rounded-2xl shadow-lg divide-x divide-y md:divide-y-0 divide-tinta/10">
<?php foreach ($st as [$n, $l]): ?><div class="p-5 text-center"><div class="font-judul font-extrabold text-4xl text-daun"><?= $n ?></div><div class="text-sm text-tinta/70"><?= $l ?></div></div><?php endforeach; ?>
</div></section>

<section id="keunggulan" class="max-w-6xl mx-auto px-4 pt-20 grid lg:grid-cols-[1fr_1.4fr] gap-10">
  <div class="lg:sticky lg:top-24 self-start"><h2 class="font-judul font-bold text-3xl md:text-4xl">Mengapa melamar lewat BKK sekolah</h2><p class="mt-4 text-tinta/75"><?= e(cfg('tentang')) ?></p></div>
  <div class="divide-y divide-tinta/15 border-y border-tinta/15">
  <?php foreach (rows('select * from keunggulan order by id') as $k): ?>
    <div class="py-5 flex gap-4"><span class="text-3xl" aria-hidden="true"><?= e($k['ikon']) ?></span><div><h3 class="font-bold text-lg"><?= e($k['judul']) ?></h3><p class="text-tinta/75 mt-1"><?= e($k['isi']) ?></p></div></div>
  <?php endforeach; ?></div>
</section>

<section id="mitra" class="max-w-6xl mx-auto px-4 pt-20">
  <div class="flex flex-wrap items-end justify-between gap-4"><div><h2 class="font-judul font-bold text-3xl md:text-4xl">Mitra dan status kerja sama</h2><p class="mt-2 text-tinta/75 max-w-xl">Perusahaan ber-MOU sudah diverifikasi sekolah. Perusahaan yang belum ber-MOU masih dalam penjajakan, jadi cek skema kontraknya dengan teliti.</p></div>
  <div class="flex gap-2 text-sm" id="mitraTabs"><?php foreach (['' => 'Semua', 'Sudah MOU' => 'Sudah MOU', 'Belum MOU' => 'Belum MOU'] as $v => $l): ?><button data-f="<?= $v ?>" class="px-4 py-2 rounded-full border border-tinta/20 <?= $v === '' ? 'bg-tinta text-white' : '' ?>"><?= $l ?></button><?php endforeach; ?></div></div>
  <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php foreach (rows('select * from mitra order by status_mou desc, nama') as $m): ?>
    <article data-mou="<?= e($m['status_mou']) ?>" class="bg-white rounded-2xl p-5 border border-tinta/10 flex flex-col">
      <div class="flex justify-between gap-2"><span class="text-xs text-tinta/60"><?= e($m['jenis']) ?>, <?= e($m['kota']) ?></span><?= badge($m['status_mou']) ?></div>
      <h3 class="font-judul font-bold text-xl mt-2"><?= e($m['nama']) ?></h3>
      <p class="text-sm text-tinta/70">Bidang <?= e($m['bidang']) ?></p>
      <dl class="mt-4 text-sm space-y-2">
        <div><dt class="font-semibold">Bentuk kerja sama</dt><dd class="text-tinta/75"><?= e($m['kerjasama']) ?></dd></div>
        <div><dt class="font-semibold">Skema kontrak kerja</dt><dd class="text-tinta/75"><?= e($m['kontrak']) ?></dd></div>
        <div><dt class="font-semibold">Masa MOU</dt><dd class="text-tinta/75"><?= $m['status_mou'] === 'Sudah MOU' ? tgl($m['mou_mulai']) . ' sampai ' . tgl($m['mou_selesai']) : 'Dalam penjajakan' ?></dd></div>
      </dl>
      <p class="mt-auto pt-4 text-xs text-tinta/60">Kontak: <?= e($m['kontak']) ?></p>
    </article>
  <?php endforeach; ?></div>
</section>

<section class="max-w-6xl mx-auto px-4 pt-20">
  <div class="flex items-end justify-between"><h2 class="font-judul font-bold text-3xl md:text-4xl">Lowongan terbaru</h2><a href="lowongan.php" class="font-semibold text-daun underline">Lihat semua</a></div>
  <div class="mt-8 grid md:grid-cols-2 gap-4">
  <?php foreach (rows("select l.*, m.nama mitra, m.status_mou mou from lowongan l join mitra m on m.id=l.mitra_id where l.aktif=1 order by l.id desc limit 6") as $j): ?>
    <a href="lowongan.php?id=<?= $j['id'] ?>" class="bg-white rounded-2xl p-5 border border-tinta/10 hover:border-daun flex gap-4 items-start">
      <div class="min-w-0 flex-1"><h3 class="font-bold text-lg"><?= e($j['judul']) ?></h3><p class="text-sm text-tinta/70"><?= e($j['mitra']) ?>, <?= e($j['lokasi']) ?></p>
      <p class="text-sm mt-2"><?= e($j['gaji']) ?></p><p class="text-xs text-tinta/60 mt-1">Tutup <?= tgl($j['deadline']) ?></p></div>
      <div class="text-right space-y-2"><span class="block text-xs font-semibold bg-kunyit/30 rounded-full px-2.5 py-0.5"><?= e($j['tipe']) ?></span><?= badge($j['mou']) ?></div>
    </a>
  <?php endforeach; ?></div>
</section>

<section class="max-w-6xl mx-auto px-4 pt-20">
  <h2 class="font-judul font-bold text-3xl md:text-4xl">Empat langkah sampai diterima kerja</h2>
  <ol class="mt-8 grid md:grid-cols-4 gap-4">
  <?php $i = 0; foreach (['Pilih lowongan' => 'Saring berdasarkan bidang, tipe kerja, dan status MOU perusahaan.', 'Kirim lamaran' => 'Isi formulir dan unggah CV dalam PDF langsung dari halaman lowongan.', 'Seleksi' => 'BKK meneruskan berkas ke perusahaan dan memberi kabar jadwal tes.', 'Penempatan' => 'Tanda tangani kontrak. Tim BKK mendampingi sampai hari pertama.'] as $t => $d): $i++; ?>
    <li class="bg-white rounded-2xl p-5 border border-tinta/10"><span class="grid place-items-center h-9 w-9 rounded-full bg-tinta text-kunyit font-bold"><?= $i ?></span><h3 class="font-bold mt-3"><?= $t ?></h3><p class="text-sm text-tinta/75 mt-1"><?= $d ?></p></li>
  <?php endforeach; ?></ol>
</section>

<section id="struktur" class="max-w-6xl mx-auto px-4 pt-20">
  <h2 class="font-judul font-bold text-3xl md:text-4xl">Struktur organisasi BKK</h2>
  <div class="mt-8 bg-white rounded-2xl border border-tinta/10 p-6 overflow-x-auto"><div class="tree min-w-[900px]"><?php tree(0); ?></div></div>
</section>

<section id="sambutan" class="max-w-6xl mx-auto px-4 pt-20">
  <h2 class="font-judul font-bold text-3xl md:text-4xl">Sambutan pengurus BKK</h2>
  <p class="mt-2 text-tinta/75 max-w-2xl">Kenali tim yang mengelola layanan BKK dan mendampingi Anda sampai diterima kerja.</p>
  <div class="mt-8 grid md:grid-cols-2 gap-5">
  <?php foreach (rows('select * from sambutan order by urut,id') as $s): ?>
    <article class="reveal bg-white rounded-2xl p-6 border border-tinta/10 flex gap-4 hover:border-daun hover:shadow-lg transition">
      <?php if (!empty($s['foto'])): ?><img src="media/<?= e($s['foto']) ?>" alt="Foto <?= e($s['nama']) ?>" class="h-16 w-16 shrink-0 rounded-full object-cover border border-tinta/10" loading="lazy">
      <?php else: ?><div class="grid place-items-center h-14 w-14 shrink-0 rounded-full bg-tinta text-kunyit font-judul font-extrabold text-xl" aria-hidden="true"><?= e(strtoupper(substr(trim($s['nama']), 0, 1))) ?></div><?php endif; ?>
      <div class="min-w-0"><h3 class="font-bold text-lg"><?= e($s['jabatan']) ?></h3><p class="text-xs text-tinta/60"><?= e($s['nama']) ?></p><p class="mt-3 text-sm text-tinta/80">&ldquo;<?= e($s['isi']) ?>&rdquo;</p></div>
    </article>
  <?php endforeach; ?></div>
</section>

<section class="max-w-6xl mx-auto px-4 pt-20 grid lg:grid-cols-2 gap-10">
  <div><h2 class="font-judul font-bold text-3xl">Pengumuman</h2>
    <div class="mt-6 divide-y divide-tinta/15 border-y border-tinta/15">
    <?php foreach (rows('select * from pengumuman order by tanggal desc limit 5') as $p): ?><div class="py-4 flex gap-4"><?php if (!empty($p['foto'])): ?><img src="media/<?= e($p['foto']) ?>" alt="" class="h-20 w-20 shrink-0 rounded-xl object-cover border border-tinta/10" loading="lazy"><?php endif; ?><div><p class="text-xs text-tinta/60"><?= tgl($p['tanggal']) ?></p><h3 class="font-bold"><?= e($p['judul']) ?></h3><p class="text-sm text-tinta/75"><?= e($p['isi']) ?></p></div></div><?php endforeach; ?></div></div>
  <div><h2 class="font-judul font-bold text-3xl">Cerita alumni</h2>
    <div class="mt-6 space-y-4">
    <?php foreach (rows('select * from testimoni order by id desc limit 3') as $t): ?><figure class="bg-white rounded-2xl p-5 border border-tinta/10"><blockquote class="text-tinta/85">"<?= e($t['isi']) ?>"</blockquote><figcaption class="mt-3 text-sm"><b><?= e($t['nama']) ?></b>, angkatan <?= e($t['angkatan']) ?><br><span class="text-tinta/60"><?= e($t['posisi']) ?></span></figcaption></figure><?php endforeach; ?></div></div>
</section>

<section class="max-w-3xl mx-auto px-4 pt-20">
  <h2 class="font-judul font-bold text-3xl">Pertanyaan yang sering diajukan</h2>
  <div class="mt-6 space-y-3">
  <?php foreach (['Apakah lulusan sekolah lain boleh melamar?' => 'Boleh. Portal ini terbuka untuk semua lulusan SMK.', 'Apakah melamar lewat BKK dipungut biaya?' => 'Tidak. Semua layanan BKK gratis. Laporkan ke kami jika ada pihak yang meminta bayaran.', 'Apa bedanya perusahaan Sudah MOU dan Belum MOU?' => 'Sudah MOU berarti perusahaan telah menandatangani kerja sama resmi dan diverifikasi sekolah. Belum MOU berarti masih penjajakan.', 'Berapa lama lamaran diproses?' => 'Rata-rata satu sampai dua minggu, tergantung jadwal seleksi perusahaan.'] as $qq => $a): ?>
    <details class="bg-white rounded-xl border border-tinta/10 p-4"><summary class="font-semibold cursor-pointer"><?= $qq ?></summary><p class="mt-2 text-tinta/75"><?= $a ?></p></details>
  <?php endforeach; ?></div>
</section>

<section id="kontak" class="max-w-6xl mx-auto px-4 pt-20"><div class="bg-daun text-white rounded-3xl p-8 md:p-12 grid md:grid-cols-[1.4fr_1fr] gap-8 items-center">
  <div><h2 class="font-judul font-bold text-3xl md:text-4xl">Perusahaan ingin membuka lowongan?</h2><p class="mt-3 text-white/85">Hubungi tim BKK untuk membahas MOU, kebutuhan tenaga kerja, dan jadwal rekrutmen di sekolah.</p></div>
  <div class="space-y-2 text-sm"><a class="block rounded-xl bg-kunyit text-tinta font-bold text-center py-3" href="https://wa.me/<?= e(cfg('wa')) ?>">Chat WhatsApp BKK</a><p class="text-center"><?= e(cfg('email')) ?><br><?= e(cfg('telepon')) ?></p></div>
</div></section>
<?php bottom();
