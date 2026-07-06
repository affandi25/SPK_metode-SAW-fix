<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Siswa.php';
require_once __DIR__ . '/../models/Setting.php';

requireLogin();

$siswaModel = new Siswa($pdo);
$settingModel = new Setting($pdo);

function hitungSAW($siswaList, $bobot) {
    if (empty($siswaList)) return [];

    // Step 1: Konversi penghasilan (dibagi 100.000)
    foreach ($siswaList as &$s) {
        $s['penghasilan_konversi'] = $s['penghasilan_ortu'] / 100000;
    }
    unset($s);

    // Step 2: Cari nilai max/min untuk normalisasi
    $maxRaport     = max(array_column($siswaList, 'nilai_raport'));
    $minPenghasilan = min(array_column($siswaList, 'penghasilan_konversi'));
    $maxTanggungan = max(array_column($siswaList, 'tanggungan_ortu'));

    // Step 3: Normalisasi dan hitung skor akhir
    foreach ($siswaList as &$s) {
        // Normalisasi (TIDAK dibulatkan)
        $r_raport      = $maxRaport > 0 ? $s['nilai_raport'] / $maxRaport : 0;
        $r_penghasilan = $s['penghasilan_konversi'] > 0 ? $minPenghasilan / $s['penghasilan_konversi'] : 0;
        $r_tanggungan  = $maxTanggungan > 0 ? $s['tanggungan_ortu'] / $maxTanggungan : 0;

        // Step 4: Hitung skor akhir langsung, baru bulatkan ke 4 desimal
        $skor = ($r_raport * $bobot['bobot_raport']) +
                ($r_penghasilan * $bobot['bobot_penghasilan']) +
                ($r_tanggungan * $bobot['bobot_tanggungan']);
        
        $s['skor_akhir'] = round($skor, 4);
    }
    unset($s);

    // Step 5: Urutkan dari skor tertinggi ke terendah
    usort($siswaList, function($a, $b) {
        // Utamakan skor SAW
        if ($b['skor_akhir'] !== $a['skor_akhir']) {
            return $b['skor_akhir'] <=> $a['skor_akhir'];
        }

        // Jika skor sama, prioritaskan nilai raport lebih tinggi
        if ($b['nilai_raport'] !== $a['nilai_raport']) {
            return $b['nilai_raport'] <=> $a['nilai_raport'];
        }

        // Jika nilai raport juga sama, pilih yang penghasilan ortunya lebih kecil
        if ($a['penghasilan_ortu'] !== $b['penghasilan_ortu']) {
            return $a['penghasilan_ortu'] <=> $b['penghasilan_ortu'];
        }

        // Jika masih sama, pilih yang tanggungannya lebih besar
        if ($b['tanggungan_ortu'] !== $a['tanggungan_ortu']) {
            return $b['tanggungan_ortu'] <=> $a['tanggungan_ortu'];
        }

        // Sebagai fallback, gunakan ID untuk hasil deterministic
        return $a['id'] <=> $b['id'];
    });
    return $siswaList;
}

function prosesRanking($pdo, $siswaModel, $settingModel, $jenis) {
    $siswaList = $siswaModel->getAllByJenisOrderedByNilai($jenis);
    if (empty($siswaList)) return;

    $bobot = $settingModel->getBobot($jenis);
    $kuota = $settingModel->getKuota($jenis);
    $jumlahKuota = $kuota ? (int)$kuota['jumlah_kuota'] : 0;

    $ranked = hitungSAW($siswaList, $bobot);

    $minimalPrestasi = 70;
    foreach ($ranked as $i => $s) {
        $ranking = $i + 1;
        if ($jenis === 'prestasi' && $s['nilai_raport'] < $minimalPrestasi) {
            $status = 'ditolak';
        } else {
            $status = ($jumlahKuota > 0 && $ranking <= $jumlahKuota) ? 'diterima' : 'ditolak';
        }
        $siswaModel->updateRankingStatus($s['id'], $s['skor_akhir'], $ranking, $status);
    }
}

$action = $_GET['action'] ?? '';
if ($action === 'hitung' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    prosesRanking($pdo, $siswaModel, $settingModel, 'prestasi');
    prosesRanking($pdo, $siswaModel, $settingModel, 'kurang_mampu');
    setFlash('success', 'Perhitungan ranking berhasil diperbarui.');
    header('Location: ' . BASE_URL . 'views/ranking/index.php');
    exit();
}
