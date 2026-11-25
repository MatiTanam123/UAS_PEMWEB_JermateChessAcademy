# Pemetaan kolom dari ERD

- admin: id_admin, nama_admin, username, email, password, foto, created_at, updated_at
- peserta: id_peserta, nama_lengkap, username, email, password, no_hp, tanggal_lahir, jenis_kelamin, alamat, foto_profil, status_akun, created_at, updated_at
- program: id_program, id_admin, nama_program, tingkat, deskripsi, biaya, durasi_minggu, kuota, status_aktif, created_at, updated_at
- pelatih: id_pelatih, id_admin, nama_pelatih, email, no_hp, spesialisasi, biografi, foto, pengalaman_tahun, status_aktif, created_at, updated_at
- jadwal: id_jadwal, id_program, id_pelatih, id_admin, tanggal_latihan, waktu_mulai, waktu_selesai, lokasi, kapasitas, status_jadwal, created_at, updated_at
- pendaftaran: id_pendaftaran, id_peserta, id_program, id_jadwal, tanggal_daftar, status_pendaftaran, catatan
- pembayaran: id_pembayaran, id_pendaftaran, id_admin, kode_transaksi, jumlah_pembayaran, metode_pembayaran, bukti_pembayaran, status_pembayaran, tanggal_bayar, tanggal_verifikasi, catatan_verifikasi, created_at
- kehadiran: id_kehadiran, id_pendaftaran, id_jadwal, id_peserta, status_kehadiran, waktu_absen, keterangan
- galeri: id_galeri, id_admin, judul, deskripsi, lokasi_url, kategori_kegiatan, tanggal_kegiatan, created_at
- testimoni: id_testimoni, id_peserta, id_program, rating, isi_testimoni, status, created_at
- notifikasi: id_notifikasi, id_peserta, id_admin, judul, pesan, tipe, status_baca, created_at
- pengaturan: id_pengaturan, id_admin, kunci, nilai, deskripsi, updated_at
