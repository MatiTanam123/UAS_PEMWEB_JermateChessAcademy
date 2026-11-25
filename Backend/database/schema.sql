CREATE DATABASE IF NOT EXISTS jermate_chess_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jermate_chess_academy;

CREATE TABLE admin (
 id_admin VARCHAR(30) PRIMARY KEY, nama_admin VARCHAR(100) NOT NULL,
 username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL, foto VARCHAR(255) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE peserta (
 id_peserta VARCHAR(30) PRIMARY KEY, nama_lengkap VARCHAR(100) NOT NULL,
 username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL, no_hp VARCHAR(20), tanggal_lahir DATE NULL,
 jenis_kelamin ENUM('Laki-laki','Perempuan') NULL, alamat TEXT, foto_profil VARCHAR(255),
 status_akun ENUM('aktif','nonaktif','ditangguhkan') NOT NULL DEFAULT 'aktif',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE program (
 id_program VARCHAR(30) PRIMARY KEY, id_admin VARCHAR(30) NULL,
 nama_program VARCHAR(120) NOT NULL,
 tingkat ENUM('Beginner','Intermediate','Professional') NOT NULL,
 deskripsi TEXT, biaya DECIMAL(12,2) NOT NULL DEFAULT 0.00, durasi_minggu INT NOT NULL DEFAULT 4, kuota INT NOT NULL DEFAULT 10,
 status_aktif TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pelatih (
 id_pelatih VARCHAR(30) PRIMARY KEY, id_admin VARCHAR(30) NULL, nama_pelatih VARCHAR(100) NOT NULL,
 email VARCHAR(100) UNIQUE, no_hp VARCHAR(20), spesialisasi VARCHAR(120), biografi TEXT, foto VARCHAR(255),
 pengalaman_tahun INT NOT NULL DEFAULT 0, status_aktif TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE jadwal (
 id_jadwal VARCHAR(30) PRIMARY KEY, id_program VARCHAR(30) NOT NULL, id_pelatih VARCHAR(30) NULL,
 id_admin VARCHAR(30) NULL, tanggal_latihan DATE NOT NULL, waktu_mulai TIME NOT NULL,
 waktu_selesai TIME NOT NULL, lokasi VARCHAR(180), kapasitas INT NOT NULL DEFAULT 10,
 status_jadwal ENUM('Tersedia','Penuh','Selesai','Dibatalkan') NOT NULL DEFAULT 'Tersedia',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (id_program) REFERENCES program(id_program),
 FOREIGN KEY (id_pelatih) REFERENCES pelatih(id_pelatih) ON DELETE SET NULL,
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pendaftaran (
 id_pendaftaran VARCHAR(30) PRIMARY KEY, id_peserta VARCHAR(30) NOT NULL,
 id_program VARCHAR(30) NOT NULL, id_jadwal VARCHAR(30) NULL,
 tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 status_pendaftaran ENUM('menunggu','disetujui','ditolak','selesai') NOT NULL DEFAULT 'menunggu',
 catatan TEXT,
 FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta),
 FOREIGN KEY (id_program) REFERENCES program(id_program),
 FOREIGN KEY (id_jadwal) REFERENCES jadwal(id_jadwal) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pembayaran (
 id_pembayaran VARCHAR(30) PRIMARY KEY, id_pendaftaran VARCHAR(30) NOT NULL,
 id_admin VARCHAR(30) NULL, kode_transaksi VARCHAR(60) NOT NULL UNIQUE,
 jumlah_pembayaran DECIMAL(12,2) NOT NULL,
 metode_pembayaran ENUM('Transfer','Tunai','QRIS') NOT NULL DEFAULT 'Transfer',
 bukti_pembayaran VARCHAR(255),
 status_pembayaran ENUM('menunggu','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu',
 tanggal_bayar DATETIME NULL, tanggal_verifikasi DATETIME NULL, catatan_verifikasi TEXT,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_pendaftaran) REFERENCES pendaftaran(id_pendaftaran),
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE kehadiran (
 id_kehadiran VARCHAR(30) PRIMARY KEY, id_pendaftaran VARCHAR(30) NOT NULL,
 id_jadwal VARCHAR(30) NOT NULL, id_peserta VARCHAR(30) NOT NULL,
 status_kehadiran ENUM('Hadir','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
 waktu_absen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, keterangan TEXT,
 FOREIGN KEY (id_pendaftaran) REFERENCES pendaftaran(id_pendaftaran),
 FOREIGN KEY (id_jadwal) REFERENCES jadwal(id_jadwal),
 FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta)
) ENGINE=InnoDB;

CREATE TABLE galeri (
 id_galeri VARCHAR(30) PRIMARY KEY, id_admin VARCHAR(30) NULL,
 judul VARCHAR(150) NOT NULL, deskripsi TEXT, lokasi_url VARCHAR(255),
 kategori_kegiatan VARCHAR(100), tanggal_kegiatan DATE NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE testimoni (
 id_testimoni VARCHAR(30) PRIMARY KEY, id_peserta VARCHAR(30) NOT NULL,
 id_program VARCHAR(30) NULL, rating TINYINT(3) UNSIGNED NOT NULL,
 isi_testimoni TEXT NOT NULL,
 status ENUM('menunggu','ditampilkan','ditolak') NOT NULL DEFAULT 'menunggu',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta),
 FOREIGN KEY (id_program) REFERENCES program(id_program) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifikasi (
 id_notifikasi VARCHAR(30) PRIMARY KEY, id_peserta VARCHAR(30) NULL,
 id_admin VARCHAR(30) NULL, judul VARCHAR(150) NOT NULL, pesan TEXT NOT NULL,
 tipe VARCHAR(50) DEFAULT 'informasi', status_baca TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta) ON DELETE CASCADE,
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pengaturan (
 id_pengaturan VARCHAR(30) PRIMARY KEY, id_admin VARCHAR(30) NULL,
 kunci VARCHAR(100) NOT NULL UNIQUE, nilai TEXT, deskripsi VARCHAR(255),
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Akun admin awal: username admin, password admin123
-- Password disimpan dalam bentuk hash password_hash() PHP.
INSERT INTO admin(id_admin,nama_admin,username,email,password)
VALUES('ADM-INITIAL','Administrator JerMate','admin','admin@jermate.com',
'$2y$10$8ipp21ZkoP43b9WeGoOwQuDYeBpe2Ba5NiTsU2PhyfyspxNCwtT/O');
