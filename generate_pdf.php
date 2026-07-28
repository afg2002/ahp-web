<?php
/**
 * AHP Calculator — Export PDF
 * Menghasilkan laporan PDF untuk lampiran skripsi
 * Menggunakan browser print-to-PDF dengan layout optimal
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'db_helpers.php';

initSession();

$source = $_GET['source'] ?? 'session';
$analysisId = intval($_GET['id'] ?? 0);
$analysis = null;
$data = $_SESSION['ahp'];

// Load from database if specified
if ($source === 'db' && $analysisId > 0) {
    try {
        $db = Database::getInstance();
        $dbAnalysis = dbGetAnalysis($analysisId);
        if ($dbAnalysis) {
            dbLoadAnalysisIntoSession($analysisId);
            $data = $_SESSION['ahp'];
            $analysis = $dbAnalysis;
        }
    } catch (Exception $e) {}
}

// Get results
$results = $data['results'] ?? [];
$criteriaPriorities = $results['criteria']['priorities'] ?? [];
$criteriaResult = $results['criteria'] ?? [];
$altPriorities = $results['alternatives'] ?? [];
$globalPriorities = $results['globalPriorities'] ?? [];
$criteriaLabels = $data['criteria_labels'] ?? [];
$altLabels = $data['alternative_labels'] ?? [];
$criteriaIds = array_keys($data['criteria']);
$altIds = array_keys($data['alternatives']);
$goal = $data['goal'] ?? DEFAULT_GOAL;
$ranked = getRankedAlternatives($data);

// Check if data is valid
$hasData = !empty($criteriaPriorities) && !empty($globalPriorities);

// Date
$reportDate = date('d F Y');
$reportTime = date('H:i');

// Criteria codes mapping
$criteriaCodes = ['C01','C02','C03','C04','C05','C06'];

// Custom CSS for the report
function reportCSS() {
    ?>
<style>
    @page { margin: 2.5cm 2cm; }
    * { box-sizing: border-box; }
    body {
        font-family: 'Georgia', 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        color: #1a1a1a;
    }
    .report-header {
        text-align: center;
        margin-bottom: 36px;
        padding-bottom: 24px;
        border-bottom: 2px solid #1a5c5a;
    }
    .report-header h1 {
        font-family: Georgia, serif;
        font-size: 16pt;
        font-weight: bold;
        margin: 0 0 6px 0;
        letter-spacing: 0.02em;
        color: #1a5c5a;
    }
    .report-header h2 {
        font-family: Georgia, serif;
        font-size: 12pt;
        font-weight: normal;
        margin: 0 0 4px 0;
        color: #444;
    }
    .report-header .meta {
        font-size: 10pt;
        color: #666;
        margin-top: 8px;
    }
    .report-section {
        margin-bottom: 24px;
    }
    .report-section h3 {
        font-family: Georgia, serif;
        font-size: 13pt;
        font-weight: bold;
        margin: 24px 0 12px 0;
        border-bottom: 1px solid #1a5c5a;
        padding-bottom: 6px;
        color: #1a5c5a;
    }
    .report-section h4 {
        font-size: 11pt;
        font-weight: bold;
        margin: 18px 0 8px 0;
        color: #333;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 10.5pt;
    }
    table th {
        background-color: #e8e8e8;
        font-weight: bold;
        text-align: center;
        padding: 6px 8px;
        border: 1px solid #999;
    }
    table td {
        padding: 5px 8px;
        border: 1px solid #ccc;
        text-align: center;
    }
    table td.left { text-align: left; }
    table td.right { text-align: right; }
    .rank-1 { background-color: #e6f7ee; }
    .rank-2 { background-color: #e6f3f5; }
    .rank-3 { background-color: #f0eaf7; }
    .consistency-pass {
        background-color: #e6f7ee;
        padding: 6px 12px;
        border-left: 4px solid #1a5c5a;
        margin: 8px 0;
        font-weight: bold;
        font-size: 10pt;
    }
    .consistency-fail {
        background-color: #fde8e8;
        padding: 6px 12px;
        border-left: 4px solid #be123c;
        margin: 8px 0;
        font-weight: bold;
        font-size: 10pt;
    }
    .signature-area {
        margin-top: 60px;
        text-align: right;
    }
    .signature-area .name {
        margin-top: 80px;
        font-weight: bold;
    }
    .print-button {
        text-align: center;
        margin: 20px 0;
    }

    @media print {
        .no-print { display: none; }
        button { display: none; }
        body { font-size: 11pt; }
    }
    .bar-container {
        width: 100%;
        background: #e8e8e8;
        height: 20px;
        position: relative;
        margin: 5px 0;
    }
    .bar-fill {
        height: 20px;
        background: #1a5c5a;
        color: white;
        text-align: right;
        padding-right: 6px;
        line-height: 20px;
        font-size: 9pt;
    }
    .bar-fill.gold { background: #b45309; }
    .bar-fill.silver { background: #78716c; }
    .bar-fill.bronze { background: #92400e; }
    .info-text {
        font-size: 10pt;
        color: #555;
        margin: 5px 0;
    }
    .page-break {
        page-break-before: always;
    }
    .conclusion-box {
        background: #f0fdf9;
        border-left: 4px solid #1a5c5a;
        padding: 12px 16px;
        margin: 12px 0;
    }
    .print-button button { background: #1a5c5a; color: white; border: none; padding: 12px 30px; font-size: 14px; cursor: pointer; transition: background 0.2s; }
    .print-button button:hover { background: #134e4a; }
</style>
    <?php
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan AHP - <?= htmlspecialchars($goal) ?></title>
    <?php reportCSS(); ?>
</head>
<body>
    <?php if (!$hasData): ?>
    <div class="report-header">
        <h1>Tidak Ada Data</h1>
        <p>Silakan selesaikan analisis terlebih dahulu.</p>
    </div>
    <?php else: ?>

    <!-- COVER / HEADER -->
    <div class="report-header">
        <h1>Laporan Hasil Analisis</h1>
        <h2>Sistem Pendukung Keputusan dengan Metode Analytical Hierarchy Process (AHP)</h2>
        <p class="meta">
            <?= htmlspecialchars(APP_INSTITUTION) ?> | 
            Tanggal: <?= $reportDate ?> | 
            Waktu: <?= $reportTime ?>
        </p>
    </div>

    <!-- 1. GOAL -->
    <div class="report-section">
        <h3>1. Tujuan Analisis (Goal)</h3>
        <p><?= htmlspecialchars($goal) ?></p>
        <?php if ($analysis && $analysis['client_name']): ?>
        <p><strong>Klien:</strong> <?= htmlspecialchars($analysis['client_name']) ?></p>
        <?php endif; ?>
    </div>

    <!-- 2. CRITERIA -->
    <div class="report-section">
        <h3>2. Kriteria Penilaian</h3>
        <table>
            <thead>
                <tr>
                    <th width="10%">Kode</th>
                    <th width="40%">Kriteria</th>
                    <th width="25%">Bobot</th>
                    <th width="25%">Prioritas</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sortedCriteria = [];
                foreach ($criteriaIds as $idx => $cid) {
                    $code = $criteriaCodes[$idx] ?? 'C' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                    $sortedCriteria[] = [
                        'code' => $code,
                        'name' => $criteriaLabels[$cid] ?? $cid,
                        'priority' => $criteriaPriorities[$idx] ?? 0,
                    ];
                }
                // Sort by priority descending
                usort($sortedCriteria, fn($a, $b) => $b['priority'] <=> $a['priority']);
                foreach ($sortedCriteria as $c):
                ?>
                <tr>
                    <td><?= $c['code'] ?></td>
                    <td class="left"><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= number_format($c['priority'] * 100, 2) ?>%</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= ($c['priority'] / max($criteriaPriorities)) * 100 ?>%">
                                <?= number_format($c['priority'] * 100, 1) ?>%
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($criteriaResult): ?>
        <h4>Uji Konsistensi</h4>
        <table>
            <tr>
                <td class="left"><strong>λmax (Lambda Max)</strong></td>
                <td><?= number_format($criteriaResult['lambdaMax'] ?? 0, 6) ?></td>
            </tr>
            <tr>
                <td class="left"><strong>CI (Consistency Index)</strong></td>
                <td><?= number_format($criteriaResult['ci'] ?? 0, 6) ?></td>
            </tr>
            <tr>
                <td class="left"><strong>RI (Random Index, n=<?= $criteriaResult['n'] ?? count($criteriaPriorities) ?>)</strong></td>
                <td><?= number_format($criteriaResult['ri'] ?? 0, 6) ?></td>
            </tr>
            <tr>
                <td class="left"><strong>CR (Consistency Ratio)</strong></td>
                <td>
                    <?= number_format($criteriaResult['cr'] ?? 0, 6) ?>
                    (<?= ($criteriaResult['consistent'] ?? false) ? 'Konsisten ✓' : 'Tidak Konsisten ✗' ?>)
                </td>
            </tr>
        </table>
        <p class="info-text">
            <?php if ($criteriaResult['consistent'] ?? false): ?>
            ✅ Nilai CR = <?= number_format($criteriaResult['cr'] ?? 0, 4) ?> &lt; 0.1, maka matriks perbandingan <strong>KONSISTEN</strong>.
            <?php else: ?>
            ❌ Nilai CR = <?= number_format($criteriaResult['cr'] ?? 0, 4) ?> &ge; 0.1, maka matriks perbandingan <strong>TIDAK KONSISTEN</strong>. Disarankan untuk mengulang perbandingan.
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- 3. ALTERNATIVES -->
    <div class="report-section">
        <h3>3. Alternatif yang Dievaluasi</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Alternatif</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($altLabels as $id => $name): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="left"><?= htmlspecialchars($name) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 4. PAIRWISE COMPARISON MATRIX - CRITERIA -->
    <div class="report-section page-break">
        <h3>4. Matriks Perbandingan Berpasangan — Kriteria</h3>
        <table>
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <?php foreach ($criteriaIds as $cid): ?>
                    <th><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></th>
                    <?php endforeach; ?>
                    <th>Prioritas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($criteriaIds as $i => $cid): ?>
                <tr>
                    <td class="left"><strong><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></strong></td>
                    <?php foreach ($criteriaIds as $j => $cid2): 
                        $val = $_SESSION['ahp']['pairwise_criteria'][$i][$j] ?? 1;
                    ?>
                    <td><?= $val >= 1 ? number_format($val, 2) : number_format($val, 4) ?></td>
                    <?php endforeach; ?>
                    <td><strong><?= number_format($criteriaPriorities[$i] ?? 0, 4) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 5. LOCAL PRIORITIES -->
    <div class="report-section">
        <h3>5. Prioritas Lokal Alternatif per Kriteria</h3>
        <table>
            <thead>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($criteriaIds as $cid): ?>
                    <th><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($altIds as $aIdx => $aid): ?>
                <tr>
                    <td class="left"><?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                    <?php foreach ($criteriaIds as $cIdx => $cid): 
                        $score = $altPriorities[$cid][$aIdx] ?? 0;
                    ?>
                    <td><?= number_format($score, 4) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 6. FINAL RANKING -->
    <div class="report-section page-break">
        <h3>6. Perankingan Global Alternatif</h3>
        <table>
            <thead>
                <tr>
                    <th width="8%">Ranking</th>
                    <th width="30%">Alternatif</th>
                    <?php foreach ($criteriaIds as $cid): ?>
                    <th><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?> (<?= number_format($criteriaPriorities[array_search($cid, $criteriaIds)] * 100, 1) ?>%)</th>
                    <?php endforeach; ?>
                    <th width="15%">Skor Global</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $maxGlobal = !empty($globalPriorities) ? max($globalPriorities) : 1;
                foreach ($ranked as $rankIdx => $item):
                    $rowClass = $rankIdx === 0 ? 'rank-1' : ($rankIdx === 1 ? 'rank-2' : ($rankIdx === 2 ? 'rank-3' : ''));
                    $altIdx = array_search($item['id'], $altIds);
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><strong>#<?= $rankIdx + 1 ?></strong></td>
                    <td class="left"><?= htmlspecialchars($item['name']) ?></td>
                    <?php foreach ($criteriaIds as $cIdx => $cid): 
                        $score = $altPriorities[$cid][$altIdx] ?? 0;
                    ?>
                    <td><?= number_format($score, 4) ?></td>
                    <?php endforeach; ?>
                    <td><strong><?= number_format($item['score'] * 100, 2) ?>%</strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Visualisasi Ranking</h4>
        <?php foreach ($ranked as $rankIdx => $item): 
            $barWidth = $maxGlobal > 0 ? ($item['score'] / $maxGlobal) * 100 : 0;
            $barColor = $rankIdx === 0 ? 'gold' : ($rankIdx === 1 ? 'silver' : ($rankIdx === 2 ? 'bronze' : ''));
        ?>
        <div style="margin: 3px 0; display: flex; align-items: center;">
            <div style="width: 30px; font-weight: bold;">#<?= $rankIdx + 1 ?></div>
            <div style="flex: 1; margin: 0 10px;"><?= htmlspecialchars($item['name']) ?></div>
            <div style="width: 60%;">
                <div class="bar-container" style="height: 22px;">
                    <div class="bar-fill <?= $barColor ?>" style="width: <?= $barWidth ?>%; height: 22px; line-height: 22px;">
                        <?= number_format($item['score'] * 100, 1) ?>%
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <h4 style="margin-top: 20px;">Kesimpulan</h4>
        <?php if (!empty($ranked)): ?>
        <p>
            Berdasarkan hasil analisis AHP, alternatif terbaik adalah 
            <strong><?= htmlspecialchars($ranked[0]['name']) ?></strong> 
            dengan skor prioritas global sebesar <strong><?= number_format($ranked[0]['score'] * 100, 2) ?>%</strong>.
        </p>
        <?php if ($criteriaResult['consistent'] ?? false): ?>
        <p>
            Hasil perbandingan berpasangan menunjukkan konsistensi yang baik (CR = <?= number_format($criteriaResult['cr'] ?? 0, 4) ?> &lt; 0.1), 
            sehingga hasil analisis ini dapat diandalkan.
        </p>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- SIGNATURE -->
    <div class="signature-area">
        <p><?= htmlspecialchars(APP_INSTITUTION) ?>, <?= $reportDate ?></p>
        <p class="name">( _______________________ )</p>
    </div>

    <?php endif; ?>

    <div class="no-print print-button">
        <button onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>
        <p style="margin-top:10px;font-size:11px;color:#666;">
            Gunakan "Save as PDF" di dialog cetak browser untuk menyimpan file PDF.
        </p>
    </div>

    <script>
        window.onload = function() {
            // Auto-resize bar text
            document.querySelectorAll('.bar-fill').forEach(function(el) {
                if (el.offsetWidth < 40) {
                    el.style.fontSize = '7pt';
                    el.style.paddingRight = '2px';
                }
            });
        };
    </script>
</body>
</html>
