<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/Siswa.php';
require_once __DIR__ . '/../../models/Setting.php';

requireLogin();

$siswaModel = new Siswa($pdo);
$settingModel = new Setting($pdo);

$filter = $_GET['jenis'] ?? 'prestasi';
if (!in_array($filter, ['prestasi', 'kurang_mampu'])) $filter = 'prestasi';

$siswaList = $siswaModel->getAll($filter);
$bobot = $settingModel->getBobot($filter);
$kuota = $settingModel->getKuota($filter);
$flash = getFlash();

$chartData = [];
foreach ($siswaList as $s) {
    if ($s['skor_akhir'] > 0 && $s['ranking']) {
        $chartData[] = [
            'label'  => '#' . $s['ranking'] . ' ' . $s['nama'],
            'skor'   => (float) $s['skor_akhir'],
            'status' => $s['status_penerima'],
        ];
    }
}
$chartData = array_reverse($chartData);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - SPK Beasiswa</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="container">
    <div class="page-header">
        <h2>Hasil Ranking Beasiswa</h2>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <form method="POST" action="<?= BASE_URL ?>controllers/RankingController.php?action=hitung" style="display:inline">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Hitung ulang ranking semua siswa?')">🔄 Hitung Ranking</button>
        </form>
        <?php endif; ?>
    </div>
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>
    <div class="filter-bar">
        <a href="?jenis=prestasi" class="btn btn-sm <?= $filter === 'prestasi' ? 'btn-active' : 'btn-outline' ?>">Prestasi</a>
        <a href="?jenis=kurang_mampu" class="btn btn-sm <?= $filter === 'kurang_mampu' ? 'btn-active' : 'btn-outline' ?>">Kurang Mampu</a>
    </div>
    <?php if ($bobot): ?>
    <div class="info-box">
        <strong>Bobot Kriteria <?= $filter === 'prestasi' ? 'Prestasi' : 'Kurang Mampu' ?>:</strong>
        Nilai Raport: <strong><?= $bobot['bobot_raport'] ?></strong> |
        Penghasilan Ortu: <strong><?= $bobot['bobot_penghasilan'] ?></strong> |
        Tanggungan: <strong><?= $bobot['bobot_tanggungan'] ?></strong> |
        Kuota: <strong><?= $kuota['jumlah_kuota'] ?? 0 ?> orang</strong>
    </div>
    <?php endif; ?>
    <?php if (!empty($chartData)): ?>
    <div class="card ranking-chart-card">
        <h3>Grafik Skor SAW — <?= $filter === 'prestasi' ? 'Beasiswa Prestasi' : 'Beasiswa Kurang Mampu' ?></h3>
        <div class="ranking-chart-wrap">
            <canvas id="rankingChart"></canvas>
        </div>
        <div class="ranking-chart-legend">
            <span><i class="legend-diterima"></i> Diterima</span>
            <span><i class="legend-ditolak"></i> Ditolak</span>
            <span><i class="legend-lainnya"></i> Lainnya</span>
        </div>
    </div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Nilai Raport</th>
                    <th>Penghasilan Ortu</th>
                    <th>Tanggungan</th>
                    <th>Skor SAW</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($siswaList)): ?>
                <tr><td colspan="9" class="text-center">Belum ada data. Tambah siswa dan klik "Hitung Ranking".</td></tr>
            <?php else: ?>
                <?php foreach ($siswaList as $s): ?>
                <tr class="<?= $s['status_penerima'] === 'diterima' ? 'row-diterima' : '' ?>">
                    <td class="text-center"><strong><?= $s['ranking'] ?: '-' ?></strong></td>
                    <td><?= htmlspecialchars($s['nis']) ?></td>
                    <td><?= htmlspecialchars($s['nama']) ?></td>
                    <td><?= htmlspecialchars($s['kelas']) ?></td>
                    <td><?= $s['nilai_raport'] ?></td>
                    <td>Rp <?= number_format($s['penghasilan_ortu'], 0, ',', '.') ?></td>
                    <td><?= $s['tanggungan_ortu'] ?></td>
                    <td><strong><?= $s['skor_akhir'] > 0 ? number_format($s['skor_akhir'], 4) : '-' ?></strong></td>
                    <td><span class="badge badge-<?= $s['status_penerima'] === 'diterima' ? 'green' : ($s['status_penerima'] === 'ditolak' ? 'red' : 'gray') ?>"><?= ucfirst($s['status_penerima']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<?php if (!empty($chartData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var chartData = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var statusColors = {
        diterima: { bg: 'rgba(58, 128, 208, 0.85)', border: '#3a80d0' },
        ditolak:  { bg: 'rgba(198, 40, 40, 0.7)', border: '#c62828' },
        default:  { bg: 'rgba(97, 97, 97, 0.55)', border: '#616161' }
    };

    var labels = chartData.map(function (item) { return item.label; });
    var scores = chartData.map(function (item) { return item.skor; });
    var colors = chartData.map(function (item) {
        var palette = statusColors[item.status] || statusColors.default;
        return palette.bg;
    });
    var borders = chartData.map(function (item) {
        var palette = statusColors[item.status] || statusColors.default;
        return palette.border;
    });
    var maxSkor = Math.max.apply(null, scores);
    var xMax = maxSkor > 0 ? Math.ceil(maxSkor * 100) / 100 : 1;

    var wrap = document.querySelector('.ranking-chart-wrap');
    if (wrap) {
        wrap.style.height = Math.max(280, chartData.length * 36) + 'px';
    }

    var ctx = document.getElementById('rankingChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Skor SAW',
                data: scores,
                backgroundColor: colors,
                borderColor: borders,
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return 'Skor SAW: ' + ctx.parsed.x.toFixed(4);
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    suggestedMax: xMax,
                    title: { display: true, text: 'Skor SAW', color: '#555', font: { weight: '600' } },
                    grid: { color: 'rgba(58, 128, 208, 0.12)' },
                    ticks: { color: '#666' }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#333', font: { size: 12 } }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
