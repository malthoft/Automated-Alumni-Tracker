<?php
require 'db.php';
$alumni = $conn->query("SELECT * FROM alumni");
$queries = $conn->query("SELECT q.*, a.nama FROM query_log q JOIN alumni a ON q.alumni_id = a.id ORDER BY q.id DESC LIMIT 10");
$jejak = $conn->query("SELECT j.*, a.nama FROM jejak_bukti j JOIN alumni a ON j.alumni_id = a.id ORDER BY j.skor DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pelacakan Alumni - Universitas Muhammadiyah Malang</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #8e1015; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 0; }
        h3 { color: #555; margin-top: 30px; border-left: 4px solid #8e1015; padding-left: 10px; }
        .action-bar { display: flex; gap: 10px; margin-bottom: 25px; background: #fafafa; padding: 15px; border-radius: 6px; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        th, td { padding: 12px; border: 1px solid #e0e0e0; text-align: left; }
        th { background-color: #8e1015; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .btn { padding: 8px 16px; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; font-weight: bold; }
        .btn-primary { background: #0056b3; }
        .btn-warning { background: #ff9800; color: #fff; }
        .btn-success { background: #2e7d32; }
        .btn-danger { background: #d32f2f; }
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; color: white; display: inline-block; text-align: center; }
        .bg-success { background: #2e7d32; }
        .bg-warning { background: #f57c00; }
        .bg-danger { background: #d32f2f; }
        .bg-secondary { background: #757575; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard Pelacakan Alumni Publik UMM</h1>
        
        <div class="action-bar">
            <form action="proses.php" method="POST">
                <button type="submit" name="jalankan" class="btn btn-primary">Jalankan Job Pelacakan Berkala (Scheduler)</button>
            </form>
            <form action="proses.php" method="POST">
                <button type="submit" name="reset" class="btn btn-warning">Reset Ulang Semua Data</button>
            </form>
        </div>

        <h3>1. Status Target Alumni</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama Target</th>
                <th>Afiliasi / Prodi</th>
                <th>Tahun</th>
                <th>Status Final</th>
                <th>Tindakan (Disambiguasi)</th>
            </tr>
            <?php while($row = $alumni->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><strong><?= $row['nama'] ?></strong></td>
                <td>UMM - <?= $row['prodi'] ?></td>
                <td><?= $row['tahun_lulus'] ?></td>
                <td>
                    <?php 
                        $status = $row['status_pelacakan'];
                        $bg = "bg-secondary";
                        if($status == "Teridentifikasi dari sumber publik") $bg = "bg-success";
                        else if($status == "Perlu Verifikasi Manual") $bg = "bg-warning";
                        else if($status == "Belum ditemukan di sumber publik") $bg = "bg-danger";
                    ?>
                    <span class="badge <?= $bg ?>"><?= $status ?></span>
                </td>
                <td>
                    <?php if($status == "Perlu Verifikasi Manual"): ?>
                        <a href="proses.php?action=approve&id=<?= $row['id'] ?>" class="btn btn-success">Valid</a>
                        <a href="proses.php?action=reject&id=<?= $row['id'] ?>" class="btn btn-danger">Bukan</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>2. Log Query Pencarian (Search Queries)</h3>
        <table>
            <tr>
                <th width="20%">Target Nama</th>
                <th>String Query yang Dihasilkan</th>
                <th width="20%">Waktu Eksekusi</th>
            </tr>
            <?php while($row = $queries->fetch_assoc()): ?>
            <tr>
                <td><?= $row['nama'] ?></td>
                <td><code><?= htmlspecialchars($row['query_text']) ?></code></td>
                <td><?= $row['tanggal'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h3>3. Sinyal Identitas & Jejak Bukti yang Diekstrak</h3>
        <table>
            <tr>
                <th>Nama Target</th>
                <th>Sumber URL</th>
                <th>Sinyal: Nama & Afiliasi</th>
                <th>Sinyal: Konteks & Lokasi</th>
                <th>Confidence Score</th>
            </tr>
            <?php while($row = $jejak->fetch_assoc()): ?>
            <tr>
                <td><strong><?= $row['nama'] ?></strong></td>
                <td><?= $row['sumber'] ?><br><small><a href="#"><?= $row['tautan'] ?></a></small></td>
                <td><?= $row['nama_kandidat'] ?><br><small><?= $row['afiliasi'] ?></small></td>
                <td><?= $row['jabatan'] ?> (<?= $row['tahun_aktif'] ?>)<br><small><?= $row['lokasi'] ?></small></td>
                <td>
                    <?php $color = $row['skor'] >= 80 ? '#2e7d32' : ($row['skor'] >= 50 ? '#f57c00' : '#d32f2f'); ?>
                    <strong style="color: <?= $color ?>; font-size: 16px;"><?= $row['skor'] ?>%</strong>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>