<?php
db()->exec("
create table settings(k text primary key, v text);
create table users(id integer primary key, username text unique, pass text);
create table mitra(id integer primary key, nama text, jenis text, bidang text, kota text, status_mou text default 'Belum MOU', mou_mulai text, mou_selesai text, kerjasama text, kontrak text, kontak text);
create table lowongan(id integer primary key, judul text, mitra_id integer, bidang text, tipe text, lokasi text, gaji text, deadline text, aktif integer default 1, deskripsi text, syarat text);
create table struktur(id integer primary key, jabatan text, nama text, parent_id integer, urut integer default 0);
create table keunggulan(id integer primary key, ikon text, judul text, isi text);
create table pengumuman(id integer primary key, judul text, isi text, tanggal text);
create table testimoni(id integer primary key, nama text, angkatan text, posisi text, isi text);
create table lamaran(id integer primary key, lowongan_id integer, nama text, email text, wa text, sekolah text, jurusan text, pesan text, cv text, status text default 'Baru', created text default (datetime('now','localtime')));
");
function ins($t, $rows) {
    foreach ($rows as $r) {
        $k = array_keys($r);
        q("insert into $t(" . implode(',', $k) . ") values(" . implode(',', array_fill(0, count($k), '?')) . ")", array_values($r));
    }
}
$d = fn($n) => date('Y-m-d', strtotime("$n days"));

foreach ([
    'nama_sekolah' => 'SMK Negeri 1 Nusantara', 'singkatan' => 'BKK', 'kota' => 'Kota Nusantara',
    'hero_judul' => 'Dari bangku SMK ke meja kerja.',
    'hero_teks' => 'Lowongan dari mitra industri yang sudah bekerja sama dengan sekolah. Terbuka untuk alumni sekolah kami dan lulusan SMK lain.',
    'tentang' => 'Bursa Kerja Khusus (BKK) adalah unit sekolah yang menyalurkan lulusan ke dunia usaha dan dunia industri. Kami menjembatani pencari kerja, perusahaan mitra, dan sekolah dalam satu layanan.',
    'alamat' => 'Jl. Pendidikan No. 1, Kota Nusantara', 'telepon' => '(021) 555-0101', 'wa' => '6281234567890',
    'email' => 'bkk@smkn1nusantara.sch.id', 'jam' => 'Senin sampai Jumat, 07.30 sampai 15.00', 'instagram' => '@bkk.smkn1nusantara',
] as $k => $v) q('insert into settings values(?,?)', [$k, $v]);

q('insert into users(username,pass) values(?,?)', ['admin', password_hash('admin123', PASSWORD_DEFAULT)]);

ins('mitra', [
 ['nama'=>'PT Nusantara Digital Solusi','jenis'=>'PT','bidang'=>'RPL','kota'=>'Jakarta','status_mou'=>'Sudah MOU','mou_mulai'=>'2025-01-10','mou_selesai'=>'2028-01-10','kerjasama'=>'Rekrutmen, magang, guru tamu','kontrak'=>'PKWT 12 bulan, evaluasi menjadi karyawan tetap','kontak'=>'hrd@nds.example'],
 ['nama'=>'CV Mitra Jaya Otomotif','jenis'=>'CV','bidang'=>'Otomotif','kota'=>'Bekasi','status_mou'=>'Sudah MOU','mou_mulai'=>'2024-07-01','mou_selesai'=>'2027-07-01','kerjasama'=>'Rekrutmen dan PKL','kontrak'=>'Masa percobaan 3 bulan, lalu PKWT 12 bulan','kontak'=>'0812-0000-1111'],
 ['nama'=>'PT Samudra Logistik Prima','jenis'=>'PT','bidang'=>'Pemasaran','kota'=>'Cikarang','status_mou'=>'Sudah MOU','mou_mulai'=>'2025-03-15','mou_selesai'=>'2028-03-15','kerjasama'=>'Rekrutmen massal tahunan','kontrak'=>'PKWT 6 bulan, dapat diperpanjang','kontak'=>'recruit@samudra.example'],
 ['nama'=>'Hotel Grand Cempaka','jenis'=>'PT','bidang'=>'Perhotelan','kota'=>'Bandung','status_mou'=>'Sudah MOU','mou_mulai'=>'2024-11-20','mou_selesai'=>'2027-11-20','kerjasama'=>'Rekrutmen, PKL, pelatihan','kontrak'=>'Training 3 bulan, lalu PKWT 12 bulan','kontak'=>'hr@grandcempaka.example'],
 ['nama'=>'PT Garuda Manufaktur Indonesia','jenis'=>'PT','bidang'=>'Otomotif','kota'=>'Karawang','status_mou'=>'Sudah MOU','mou_mulai'=>'2025-06-01','mou_selesai'=>'2028-06-01','kerjasama'=>'Rekrutmen operator dan teknisi','kontrak'=>'PKWT 12 bulan, kontrak diperbarui sesuai kinerja','kontak'=>'talent@garuda.example'],
 ['nama'=>'KAP Sentosa dan Rekan','jenis'=>'Tempat usaha','bidang'=>'Akuntansi','kota'=>'Jakarta','status_mou'=>'Belum MOU','mou_mulai'=>'','mou_selesai'=>'','kerjasama'=>'Penjajakan rekrutmen staf junior','kontrak'=>'Ditentukan saat MOU ditandatangani','kontak'=>'info@sentosa.example'],
 ['nama'=>'Toko Elektronik Maju Bersama','jenis'=>'Tempat usaha','bidang'=>'Pemasaran','kota'=>'Bekasi','status_mou'=>'Belum MOU','mou_mulai'=>'','mou_selesai'=>'','kerjasama'=>'Lowongan langsung ke BKK','kontrak'=>'Perjanjian kerja langsung dengan pemilik','kontak'=>'0813-0000-2222'],
 ['nama'=>'CV Kreasi Visual Studio','jenis'=>'CV','bidang'=>'RPL','kota'=>'Bandung','status_mou'=>'Belum MOU','mou_mulai'=>'','mou_selesai'=>'','kerjasama'=>'Penjajakan magang desain dan web','kontrak'=>'Magang 3 bulan, uang saku bulanan','kontak'=>'halo@kreasivisual.example'],
]);
ins('lowongan', [
 ['judul'=>'Junior Web Developer','mitra_id'=>1,'bidang'=>'RPL','tipe'=>'Kontrak','lokasi'=>'Jakarta','gaji'=>'Rp 4,5 juta sampai 5,5 juta','deadline'=>$d(+21),'deskripsi'=>'Mengembangkan dan merawat aplikasi web internal bersama tim produk.','syarat'=>"Lulusan SMK RPL atau TKJ\nMenguasai PHP atau JavaScript dasar\nMemahami Git\nBersedia ditempatkan di Jakarta"],
 ['judul'=>'Teknisi Jaringan','mitra_id'=>1,'bidang'=>'TKJ','tipe'=>'Kontrak','lokasi'=>'Jakarta','gaji'=>'Rp 4 juta sampai 5 juta','deadline'=>$d(+14),'deskripsi'=>'Instalasi dan perawatan jaringan di kantor klien.','syarat'=>"Lulusan SMK TKJ\nMemahami Mikrotik dan konfigurasi dasar\nMemiliki SIM C"],
 ['judul'=>'Mekanik Kendaraan Ringan','mitra_id'=>2,'bidang'=>'Otomotif','tipe'=>'Penuh waktu','lokasi'=>'Bekasi','gaji'=>'Rp 3,8 juta sampai 4,8 juta','deadline'=>$d(+30),'deskripsi'=>'Servis berkala dan perbaikan kendaraan roda empat di bengkel resmi.','syarat'=>"Lulusan SMK Teknik Kendaraan Ringan\nMemahami sistem engine dan kelistrikan\nSiap kerja dengan sistem shift"],
 ['judul'=>'Staf Gudang dan Admin Pengiriman','mitra_id'=>3,'bidang'=>'Pemasaran','tipe'=>'Kontrak','lokasi'=>'Cikarang','gaji'=>'UMK Cikarang','deadline'=>$d(+10),'deskripsi'=>'Pencatatan barang masuk dan keluar serta penjadwalan pengiriman.','syarat'=>"Lulusan SMK semua jurusan\nMampu mengoperasikan Excel dasar\nBersedia kerja shift"],
 ['judul'=>'Front Office Trainee','mitra_id'=>4,'bidang'=>'Perhotelan','tipe'=>'Magang','lokasi'=>'Bandung','gaji'=>'Uang saku Rp 2,5 juta','deadline'=>$d(+18),'deskripsi'=>'Melayani tamu check-in dan check-out serta reservasi.','syarat'=>"Lulusan SMK Perhotelan atau Pariwisata\nBerpenampilan menarik\nKomunikasi bahasa Inggris dasar"],
 ['judul'=>'Operator Produksi','mitra_id'=>5,'bidang'=>'Otomotif','tipe'=>'Kontrak','lokasi'=>'Karawang','gaji'=>'UMK Karawang','deadline'=>$d(+25),'deskripsi'=>'Mengoperasikan mesin lini perakitan sesuai SOP keselamatan kerja.','syarat'=>"Lulusan SMK Teknik\nSehat jasmani, tinggi badan minimal 160 cm\nBersedia kerja shift"],
 ['judul'=>'Staf Akuntansi Junior','mitra_id'=>6,'bidang'=>'Akuntansi','tipe'=>'Penuh waktu','lokasi'=>'Jakarta','gaji'=>'Rp 4 juta sampai 4,8 juta','deadline'=>$d(+12),'deskripsi'=>'Menyusun jurnal, rekonsiliasi, dan laporan pajak sederhana klien.','syarat'=>"Lulusan SMK Akuntansi\nMemahami Accurate atau Zahir\nTeliti dan rapi"],
 ['judul'=>'Desainer Grafis Magang','mitra_id'=>8,'bidang'=>'RPL','tipe'=>'Magang','lokasi'=>'Bandung','gaji'=>'Uang saku Rp 1,5 juta','deadline'=>$d(+20),'deskripsi'=>'Membuat materi visual media sosial dan desain web untuk klien.','syarat'=>"Menguasai Canva atau Figma\nMemiliki portofolio sederhana\nBersedia kerja hybrid"],
]);
ins('struktur', [
 ['jabatan'=>'Kepala Sekolah / Pembina','nama'=>'Nama Kepala Sekolah','parent_id'=>null,'urut'=>1],
 ['jabatan'=>'Wakasek Hubungan Industri','nama'=>'Nama Wakasek','parent_id'=>1,'urut'=>1],
 ['jabatan'=>'Ketua BKK','nama'=>'Nama Ketua BKK','parent_id'=>2,'urut'=>1],
 ['jabatan'=>'Sekretaris','nama'=>'Nama Sekretaris','parent_id'=>3,'urut'=>1],
 ['jabatan'=>'Bendahara','nama'=>'Nama Bendahara','parent_id'=>3,'urut'=>2],
 ['jabatan'=>'Seksi Penyaluran','nama'=>'Nama Koordinator','parent_id'=>3,'urut'=>3],
 ['jabatan'=>'Seksi Kemitraan','nama'=>'Nama Koordinator','parent_id'=>3,'urut'=>4],
 ['jabatan'=>'Seksi Data dan IT','nama'=>'Nama Koordinator','parent_id'=>3,'urut'=>5],
 ['jabatan'=>'Seksi Bimbingan Karier','nama'=>'Nama Koordinator','parent_id'=>3,'urut'=>6],
]);
ins('keunggulan', [
 ['ikon'=>'🤝','judul'=>'Perusahaan sudah terverifikasi','isi'=>'Mitra berstatus MOU telah kami kunjungi dan kami cek legalitasnya, sehingga pelamar terhindar dari lowongan palsu.'],
 ['ikon'=>'📝','judul'=>'Kontrak kerja yang jelas','isi'=>'Skema kontrak, masa percobaan, dan peluang pengangkatan tercantum di profil mitra sebelum Anda melamar.'],
 ['ikon'=>'🎯','judul'=>'Lowongan sesuai kompetensi','isi'=>'Posisi disusun bersama perusahaan agar cocok dengan kompetensi lulusan SMK.'],
 ['ikon'=>'🧭','judul'=>'Pendampingan sampai penempatan','isi'=>'Tim BKK membantu CV, simulasi wawancara, dan memantau proses seleksi.'],
 ['ikon'=>'🌐','judul'=>'Terbuka untuk lulusan SMK lain','isi'=>'Alumni sekolah mana pun boleh melamar lewat portal ini.'],
 ['ikon'=>'📈','judul'=>'Ada jalur karier','isi'=>'Banyak mitra mengangkat karyawan kontrak menjadi tetap setelah evaluasi kinerja.'],
]);
ins('pengumuman', [
 ['judul'=>'Job fair SMK dibuka','isi'=>'Pendaftaran perusahaan dan peserta dibuka di ruang BKK atau lewat WhatsApp resmi.','tanggal'=>$d(-2)],
 ['judul'=>'Pelatihan wawancara kerja untuk kelas 12','isi'=>'Simulasi wawancara bersama praktisi HRD mitra. Bawa CV cetak.','tanggal'=>$d(-9)],
 ['judul'=>'MOU baru dengan PT Garuda Manufaktur Indonesia','isi'=>'Kerja sama rekrutmen operator dan teknisi untuk tiga tahun ke depan.','tanggal'=>$d(-20)],
]);
ins('testimoni', [
 ['nama'=>'Rizky Pratama','angkatan'=>'2023','posisi'=>'Web Developer, PT Nusantara Digital Solusi','isi'=>'Saya tahu lowongannya dari BKK, lalu dibantu merapikan CV dan latihan wawancara. Dua minggu kemudian saya diterima.'],
 ['nama'=>'Siti Aulia','angkatan'=>'2022','posisi'=>'Front Office, Hotel Grand Cempaka','isi'=>'Kontraknya dijelaskan jelas sejak awal, jadi saya tenang waktu menandatangani.'],
 ['nama'=>'Dimas Saputra','angkatan'=>'2024','posisi'=>'Teknisi, CV Mitra Jaya Otomotif','isi'=>'Saya lulusan sekolah lain, dan tetap dilayani BKK dengan baik.'],
]);
