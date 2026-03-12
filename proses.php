<?php
require 'db.php';

if(isset($_POST['jalankan'])) {
    $alumni = $conn->query("SELECT * FROM alumni WHERE status_pelacakan = 'Belum Dilacak' OR status_pelacakan = 'Belum ditemukan di sumber publik'");
    
    while($row = $alumni->fetch_assoc()) {
        $id = $row['id'];
        $nama = $row['nama'];
        $prodi = $row['prodi'];
        $tahun = $row['tahun_lulus'];
        
        $parts = explode(" ", $nama);
        $variasi = [];
        $variasi[] = $nama;
        if(count($parts) > 1) {
            $variasi[] = $parts[0] . " " . substr($parts[1], 0, 1) . ".";
            $variasi[] = substr($parts[0], 0, 1) . ". " . $parts[1];
        }
        $var_str = implode(", ", $variasi);
        $kw_str = "Universitas Muhammadiyah Malang, UMM, " . $prodi . ", " . $tahun;
        
        $conn->query("INSERT INTO profil_target (alumni_id, variasi_nama, kata_kunci) VALUES ('$id', '$var_str', '$kw_str')");
        
        $queries = [
            "\"$nama\" + \"Universitas Muhammadiyah Malang\"",
            "\"$nama\" + \"$prodi\" + \"UMM\"",
            "\"$nama\" + site:linkedin.com",
            "\"$nama\" + ORCID"
        ];
        
        foreach($queries as $q) {
            $dt = date('Y-m-d H:i:s');
            $conn->query("INSERT INTO query_log (alumni_id, query_text, tanggal) VALUES ('$id', '$q', '$dt')");
        }
        
        $sumber_izin = ["LinkedIn", "Google Scholar", "ResearchGate", "Situs Perusahaan"];
        $jabatan_list = ["Software Engineer", "Data Analyst", "Dosen", "Network Admin"];
        
        $kandidat_list = [];
        $rand_count = rand(0, 3);
        
        for($i=0; $i<$rand_count; $i++) {
            $kandidat_list[] = [
                "sumber" => $sumber_izin[array_rand($sumber_izin)],
                "nama_kandidat" => $variasi[array_rand($variasi)],
                "afiliasi" => (rand(0,1) == 1) ? "UMM" : "Universitas Brawijaya",
                "jabatan" => $jabatan_list[array_rand($jabatan_list)],
                "lokasi" => (rand(0,1) == 1) ? "Malang" : "Jakarta",
                "tahun_aktif" => $tahun + rand(1, 3),
                "tautan" => "https://kandidat-publik.com/profil/" . rand(1000,9999)
            ];
        }
        
        $best_score = 0;
        $best_status = "Belum ditemukan di sumber publik";
        $saved_kandidat = [];
        
        foreach($kandidat_list as $k) {
            $skor = 0;
            if(in_array($k['nama_kandidat'], $variasi)) $skor += 40;
            if(strpos($kw_str, $k['afiliasi']) !== false) $skor += 30;
            if($k['tahun_aktif'] >= $tahun) $skor += 10;
            if($k['lokasi'] == "Malang") $skor += 10;
            
            $k['temp_score'] = $skor;
            $saved_kandidat[] = $k;
        }
        
        if(count($saved_kandidat) > 1) {
            $lokasi_sama = true;
            $jabatan_sama = true;
            $first_lok = $saved_kandidat[0]['lokasi'];
            $first_jab = $saved_kandidat[0]['jabatan'];
            
            foreach($saved_kandidat as &$sk) {
                if($sk['lokasi'] != $first_lok) $lokasi_sama = false;
                if($sk['jabatan'] != $first_jab) $jabatan_sama = false;
            }
            
            foreach($saved_kandidat as &$sk) {
                if($lokasi_sama && $jabatan_sama) {
                    $sk['temp_score'] += 15; 
                } else {
                    $sk['temp_score'] -= 10;
                }
            }
        }
        
        foreach($saved_kandidat as $sk) {
            $final_score = $sk['temp_score'];
            if($final_score > 100) $final_score = 100;
            if($final_score < 0) $final_score = 0;
            
            if($final_score > $best_score) {
                $best_score = $final_score;
            }
            
            $dt = date('Y-m-d H:i:s');
            $conn->query("INSERT INTO jejak_bukti (alumni_id, sumber, nama_kandidat, afiliasi, jabatan, lokasi, tahun_aktif, tautan, skor, tanggal_ditemukan) 
            VALUES ('$id', '{$sk['sumber']}', '{$sk['nama_kandidat']}', '{$sk['afiliasi']}', '{$sk['jabatan']}', '{$sk['lokasi']}', '{$sk['tahun_aktif']}', '{$sk['tautan']}', '$final_score', '$dt')");
        }
        
        if($best_score >= 80) {
            $best_status = "Teridentifikasi dari sumber publik";
        } else if($best_score >= 50) {
            $best_status = "Perlu Verifikasi Manual";
        } else if(count($kandidat_list) == 0) {
            $best_status = "Belum ditemukan di sumber publik";
        }
        
        $conn->query("UPDATE alumni SET status_pelacakan = '$best_status' WHERE id = '$id'");
    }
    header("Location: index.php");
    exit;
}

if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $act = $_GET['action'];
    
    if($act == 'approve') {
        $conn->query("UPDATE alumni SET status_pelacakan = 'Teridentifikasi dari sumber publik' WHERE id = '$id'");
    } else if($act == 'reject') {
        $conn->query("DELETE FROM jejak_bukti WHERE alumni_id = '$id'");
        $conn->query("UPDATE alumni SET status_pelacakan = 'Belum ditemukan di sumber publik' WHERE id = '$id'");
    }
    header("Location: index.php");
    exit;
}

if(isset($_POST['reset'])) {
    $conn->query("DELETE FROM jejak_bukti");
    $conn->query("DELETE FROM query_log");
    $conn->query("DELETE FROM profil_target");
    $conn->query("UPDATE alumni SET status_pelacakan = 'Belum Dilacak'");
    header("Location: index.php");
    exit;
}
?>