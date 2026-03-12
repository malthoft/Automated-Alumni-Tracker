<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_alumni_umm_tracker";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Koneksi gagal");
}

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

$conn->query("CREATE TABLE IF NOT EXISTS alumni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150),
    prodi VARCHAR(100),
    tahun_lulus INT,
    status_pelacakan VARCHAR(100) DEFAULT 'Belum Dilacak'
)");

$conn->query("CREATE TABLE IF NOT EXISTS profil_target (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT,
    variasi_nama TEXT,
    kata_kunci TEXT,
    FOREIGN KEY (alumni_id) REFERENCES alumni(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS query_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT,
    query_text TEXT,
    tanggal DATETIME,
    FOREIGN KEY (alumni_id) REFERENCES alumni(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS jejak_bukti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT,
    sumber VARCHAR(100),
    nama_kandidat VARCHAR(150),
    afiliasi VARCHAR(150),
    jabatan VARCHAR(100),
    lokasi VARCHAR(100),
    tahun_aktif INT,
    tautan TEXT,
    skor INT,
    tanggal_ditemukan DATETIME,
    FOREIGN KEY (alumni_id) REFERENCES alumni(id)
)");

$cek = $conn->query("SELECT COUNT(*) AS total FROM alumni")->fetch_assoc();
if ($cek['total'] == 0) {
    $conn->query("INSERT INTO alumni (nama, prodi, tahun_lulus) VALUES 
    ('Aris Sudarsono', 'Informatika', 2021),
    ('Bina Permata', 'Ilmu Komunikasi', 2020),
    ('Candra Wijaya', 'Manajemen', 2019),
    ('Dian Sastro', 'Teknik Sipil', 2022),
    ('Eka Putra', 'Informatika', 2023)");
}
?>