<?php
require __DIR__ . '/config.php'; require __DIR__ . '/inc/layout.php';
$bidang = array_column(rows("select distinct bidang from lowongan where aktif=1 order by 1"), 'bidang');
top('Lowongan kerja');
?>
<section class="bg-tinta text-white"><div class="max-w-6xl mx-auto px-4 py-10"><h1 class="font-judul font-extrabold text-3xl md:text-5xl">Lowongan kerja</h1><p class="mt-2 text-white/75">Saring, baca detailnya, lalu kirim lamaran dari halaman ini.</p></div></section>
<section class="max-w-6xl mx-auto px-4 py-8">
  <form id="flt" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-white rounded-2xl p-4 border border-tinta/10">
    <input name="q" placeholder="Posisi, perusahaan, atau kota" class="lg:col-span-2 rounded-lg border border-tinta/20 px-3 py-2" aria-label="Kata kunci" value="<?= e($_GET['q'] ?? '') ?>">
    <select name="bidang" class="rounded-lg border border-tinta/20 px-3 py-2" aria-label="Bidang"><option value="">Semua bidang</option><?php foreach ($bidang as $b) echo '<option' . (($_GET['bidang'] ?? '') === $b ? ' selected' : '') . '>' . e($b) . '</option>'; ?></select>
    <select name="tipe" class="rounded-lg border border-tinta/20 px-3 py-2" aria-label="Tipe kerja"><option value="">Semua tipe</option><option>Penuh waktu</option><option>Kontrak</option><option>Magang</option><option>Paruh waktu</option></select>
    <select name="mou" class="rounded-lg border border-tinta/20 px-3 py-2" aria-label="Status mitra"><option value="">Semua status mitra</option><option>Sudah MOU</option><option>Belum MOU</option></select>
  </form>
  <p id="jobCount" class="mt-4 text-sm text-tinta/70"></p>
  <div id="jobList" class="mt-4 grid md:grid-cols-2 gap-4"></div>
</section>

<dialog id="dlg" class="rounded-2xl p-0 w-[min(680px,94vw)] max-h-[92vh] text-tinta"><div class="p-6" id="dlgBody"></div></dialog>
<?php bottom();
