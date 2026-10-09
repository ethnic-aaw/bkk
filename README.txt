WEB BKK - cara pakai (Laragon)
1. Ekstrak folder ini ke C:\laragon\www\bkk lalu start Laragon (PHP 8.0 ke atas, ekstensi pdo_sqlite aktif secara bawaan).
2. Buka http://localhost/bkk  - tabel dan data contoh dibuat otomatis pada akses pertama.
3. Admin: http://localhost/bkk/admin  | username: admin | sandi: admin123 (segera ganti di menu Pengaturan).
4. Ganti semua data contoh lewat admin. Folder data/ dan uploads/ sudah dilindungi .htaccess (Apache).
   Jika memakai Nginx, blokir akses langsung ke kedua folder tersebut.
5. Tailwind dan font dimuat dari CDN, jadi komputer perlu internet saat menjalankan situs.

Menjalankan dengan Docker
1. Pastikan Docker Desktop terpasang dan menyala.
2. Di folder ini jalankan: docker compose up --build
3. Buka http://localhost:8080  (admin: http://localhost:8080/admin, admin/admin123)
4. Data tersimpan di folder data/, uploads/, dan media/ pada komputer (bind mount), jadi tidak hilang saat container dimatikan.
5. Ganti port 8080 di docker-compose.yml bila sudah dipakai aplikasi lain.
