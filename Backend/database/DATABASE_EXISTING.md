# Database JerMate

Kode final mengikuti database `jermate_chess_academy` yang dikirim sebelumnya.

- Server acuan: MariaDB 10.4.32
- PHP acuan: 8.2.12
- Charset: utf8mb4
- Tabel: admin, peserta, program, pelatih, jadwal, pendaftaran, pembayaran, kehadiran, galeri, testimoni, notifikasi, pengaturan
- Status pembayaran: `menunggu`, `terverifikasi`, `ditolak`
- Status pendaftaran: `menunggu`, `disetujui`, `ditolak`, `selesai`

Tidak ada tabel akun pelatih, audit log, atau riwayat pembayaran tambahan.

Jika database dari dump sebelumnya sudah terpasang, gunakan database tersebut. Jangan import `schema.sql` ulang karena file itu ditujukan untuk instalasi database kosong.
