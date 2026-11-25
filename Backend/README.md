# Backend JerMate Chess Academy

Folder ini berisi seluruh proses PHP dan koneksi database.

## Konfigurasi
Edit `config/koneksi.php` sesuai kredensial database hosting. Konfigurasi mendukung variabel lingkungan `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME`.

## Database
Database menggunakan MariaDB/MySQL dengan 12 tabel sesuai ERD:
`admin`, `peserta`, `program`, `pelatih`, `jadwal`, `pendaftaran`, `pembayaran`, `kehadiran`, `galeri`, `testimoni`, `notifikasi`, dan `pengaturan`.

Untuk instalasi baru, import `database/schema.sql`.
Untuk database yang sudah berisi data, jangan import ulang schema. Backup terlebih dahulu.

## Alur utama
1. Peserta membuat akun dan login.
2. Peserta memilih program dan mengirim pendaftaran.
3. Admin meninjau pendaftaran dan menyetujuinya.
4. Admin membuat jadwal pada menu Jadwal, memilih program, lalu memilih peserta yang sudah disetujui untuk mengikuti jadwal tersebut.
5. Peserta melihat tanggal, waktu, pelatih, dan lokasi pada menu Jadwal Latihan.
6. Peserta mengirim bukti pembayaran penuh sesuai biaya program.
7. Admin memverifikasi atau menolak pembayaran.
8. Admin mencatat kehadiran sesuai pendaftaran dan jadwal.

## Pembayaran
Status database: `menunggu`, `terverifikasi`, `ditolak`.
Pembayaran harus satu kali penuh sesuai biaya program. Pembayaran menunggu/terverifikasi mengunci pendaftaran agar tidak dikirim ulang. Jika ditolak, peserta dapat mengirim bukti baru.

## Keamanan dasar
- Password disimpan menggunakan `password_hash()`.
- Query menggunakan prepared statement.
- Form POST menggunakan token CSRF.
- File upload diperiksa MIME, ukuran, dan ekstensi.
- File upload tidak dapat dieksekusi sebagai PHP.
