<?php
/**
 * AHP Calculator — Export PDF (Laporan Lengkap)
 * Tahapan AHP: Matriks perbandingan, normalisasi, bobot, konsistensi, alternatif per kriteria, ranking global
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
$results         = $data['results'] ?? [];
$criteriaResult  = $results['criteria'] ?? [];
$criteriaPriorities = $criteriaResult['priorities'] ?? [];
$altPriorities   = $results['alternatives'] ?? [];
$globalPriorities = $results['globalPriorities'] ?? [];
$criteriaLabels  = $data['criteria_labels'] ?? [];
$altLabels       = $data['alternative_labels'] ?? [];
$criteriaIds     = array_keys($data['criteria'] ?? []);
$altIds          = array_keys($data['alternatives'] ?? []);
$goal            = $data['goal'] ?? DEFAULT_GOAL;
$ranked          = getRankedAlternatives($data);

// Computed from ahpCalculate return
$criteriaMatrix    = $data['pairwise_criteria'] ?? [];
$normalizedCriteria = $criteriaResult['normalized'] ?? [];
$weightedSumCriteria = $criteriaResult['weightedSum'] ?? [];
$consVecCriteria   = $criteriaResult['consistencyVector'] ?? [];
$n = count($criteriaIds);

// Check if data is valid
$hasData = !empty($criteriaPriorities) && !empty($globalPriorities);

// Date & Settings
$reportDate  = date('d F Y');
$reportTime  = date('H:i');
$institution = dbGetSetting('app_institution', APP_INSTITUTION);
$logoText    = dbGetSetting('app_logo_text', 'A');
$logoUrl     = dbGetSetting('app_logo_url', '');
$signerTitle = dbGetSetting('report_signer_title', 'Hormat Kami,');
$signerName  = dbGetSetting('report_signer_name', 'Widya Corietania Basri, S.H., M.Kn.');
$headerAlign = dbGetSetting('report_header_align', 'center');
$appName     = dbGetSetting('app_name', APP_NAME);

// Criteria codes mapping
$criteriaCodes = ['C01','C02','C03','C04','C05','C06','C07','C08','C09','C10'];

// Helper: format comparison value (whole numbers as-is, else decimal)
function fmtVal($v) {
    if ($v == 0) return '0';
    if (abs($v - round($v)) < 0.0001) return number_format($v, 0);
    return number_format($v, 4);
}

// Custom CSS for the report
function reportCSS() { ?>
<style>
    @page {
        margin: 0;
        size: A4 portrait;
    }
    * { box-sizing: border-box; }
    body {
        font-family: 'Georgia', 'Times New Roman', Times, serif;
        font-size: 11pt;
        line-height: 1.55;
        color: #1a1a1a;
        padding: 1.2cm 1.8cm;
    }

    /* ── Kop Surat ── */
    .kop {
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 3px double #1a5c5a;
    }
    .kop.center {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .kop.left-layout {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 18px;
        text-align: left;
    }
    .kop .logo-box {
        flex-shrink: 0;
        width: 64px; height: 64px;
        border: 2px solid #1a5c5a;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 6px;
    }
    .kop.left-layout .logo-box { margin-bottom: 0; }
    .kop .logo-box img { max-width: 56px; max-height: 56px; object-fit: contain; }
    .kop .logo-box span {
        font-family: Georgia, serif; font-size: 24pt;
        font-weight: bold; font-style: italic; color: #1a5c5a;
    }
    .kop .kop-text h1 {
        font-size: 14pt; font-weight: bold; margin: 0 0 2px 0;
        text-transform: uppercase; letter-spacing: 0.03em; color: #1a5c5a;
    }
    .kop .kop-text .sub { font-size: 10pt; color: #444; margin: 0; }
    .kop .kop-text .addr { font-size: 9pt; color: #777; margin: 0; }

    /* ── Report title ── */
    .report-title {
        text-align: center;
        margin: 18px 0 14px;
    }
    .report-title h2 {
        font-size: 12.5pt; font-weight: bold;
        text-transform: uppercase; letter-spacing: 0.03em;
        margin: 0 0 3px 0;
    }
    .report-title .goal-label {
        font-size: 10pt; color: #444; margin: 2px 0;
        font-style: italic;
    }

    /* ── Section headings ── */
    .section {
        margin-bottom: 20px;
    }
    .section h3 {
        font-family: Georgia, serif;
        font-size: 11.5pt;
        font-weight: bold;
        color: #1a5c5a;
        border-bottom: 1.5px solid #1a5c5a;
        padding-bottom: 4px;
        margin: 18px 0 8px 0;
    }
    .section h4 {
        font-size: 10.5pt;
        font-weight: bold;
        margin: 14px 0 6px 0;
        color: #333;
    }
    .info-text {
        font-size: 9.5pt;
        color: #555;
        margin: 4px 0;
    }

    /* ── Tables ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
        font-size: 9.5pt;
    }
    table th {
        background-color: #1a5c5a;
        color: #fff;
        font-weight: bold;
        text-align: center;
        padding: 5px 6px;
        border: 1px solid #134e4a;
    }
    table th.th-light {
        background-color: #e8f5f4;
        color: #1a5c5a;
        border: 1px solid #aaccca;
    }
    table td {
        padding: 4px 6px;
        border: 1px solid #ccc;
        text-align: center;
        vertical-align: middle;
    }
    table td.left { text-align: left; }
    table td.right { text-align: right; }
    table tbody tr:nth-child(even) { background: #f9f9f9; }
    table td.diag { background: #eef; color: #999; }
    table td.bold { font-weight: bold; }
    table tfoot td { background: #e8f5f4; font-weight: bold; border: 1px solid #aaccca; }
    .rank-1 { background-color: #d4edda !important; }
    .rank-2 { background-color: #d1ecf1 !important; }
    .rank-3 { background-color: #e8d5f5 !important; }

    /* ── Consistency box ── */
    .cr-box {
        border: 1px solid #ccc;
        padding: 8px 12px;
        margin: 8px 0;
        font-size: 9.5pt;
    }
    .cr-box.pass { border-left: 4px solid #1a5c5a; background: #e6f7ee; }
    .cr-box.fail { border-left: 4px solid #be123c; background: #fde8e8; }

    /* ── Bar chart ── */
    .bar-container {
        width: 100%;
        background: #e0e0e0;
        height: 16px;
        position: relative;
    }
    .bar-fill {
        height: 16px;
        background: #1a5c5a;
        color: #fff;
        font-size: 7.5pt;
        text-align: right;
        padding-right: 4px;
        line-height: 16px;
        white-space: nowrap;
    }
    .bar-fill.gold   { background: #b45309; }
    .bar-fill.silver { background: #78716c; }
    .bar-fill.bronze { background: #92400e; }

    /* ── Signature ── */
    .signature-wrapper {
        margin-top: 40px;
        width: 100%;
        display: flex;
        justify-content: flex-end;
        page-break-inside: avoid;
    }
    .signature-box { text-align: center; width: 270px; }
    .signature-box .city-date { font-size: 10.5pt; margin-bottom: 3px; white-space: nowrap; }
    .signature-box .title-label { font-size: 9.5pt; color: #555; margin-bottom: 70px; }
    .signature-box .name-container {
        display: flex; align-items: center; justify-content: center;
        font-size: 10.5pt; font-weight: bold; color: #1a1a1a; white-space: nowrap;
    }
    .signature-box .name-line {
        display: inline-block;
        min-width: 210px;
        border-bottom: 1.5px dotted #1a1a1a;
        text-align: center;
        padding-bottom: 2px;
        margin: 0 4px;
    }

    /* ── Page break ── */
    .page-break { page-break-before: always; }
    .avoid-break { page-break-inside: avoid; }

    /* ── Print button ── */
    .no-print { text-align: center; margin: 28px 0; }
    .no-print button {
        background: #1a5c5a; color: white; border: none;
        padding: 12px 32px; font-size: 13px; cursor: pointer;
        font-family: Georgia, serif; box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
    .no-print button:hover { background: #134e4a; }
    .no-print .note { margin-top: 8px; font-size: 10px; color: #888; }

    @media print {
        .no-print { display: none !important; }
        body { padding: 1.2cm 1.8cm; font-size: 10.5pt; }
        table th { background-color: #1a5c5a !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table th.th-light { background-color: #e8f5f4 !important; color: #1a5c5a !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table tbody tr:nth-child(even) { background: #f9f9f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        table tfoot td { background: #e8f5f4 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .rank-1, .rank-2, .rank-3 { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .cr-box.pass, .cr-box.fail { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bar-fill { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        thead { display: table-header-group; }
    }
</style>
<?php } ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan AHP — <?= htmlspecialchars($goal) ?></title>
    <?php reportCSS(); ?>
</head>
<body>

<?php if (!$hasData): ?>
<div class="section" style="text-align:center;padding:40px;">
    <h3>Tidak Ada Data</h3>
    <p>Silakan selesaikan analisis terlebih dahulu.</p>
</div>
<?php else: ?>

<!-- ══════════════════════════════════════════════
     KOP SURAT
══════════════════════════════════════════════ -->
<div class="kop <?= $headerAlign === 'center' ? 'center' : 'left-layout' ?>">
    <div class="logo-box">
        <?php if (!empty($logoUrl)): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
        <?php else: ?>
            <span><?= htmlspecialchars($logoText) ?></span>
        <?php endif; ?>
    </div>
    <div class="kop-text">
        <h1><?= htmlspecialchars($institution) ?></h1>
        <p class="sub">Sistem Pendukung Keputusan — Metode Analytical Hierarchy Process (AHP)</p>
        <p class="addr"><?= htmlspecialchars($appName) ?> | <?= $reportDate ?> | <?= $reportTime ?></p>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     JUDUL LAPORAN
══════════════════════════════════════════════ -->
<div class="report-title">
    <h2>Laporan Hasil Analisis AHP</h2>
    <p class="goal-label">Tujuan: <?= htmlspecialchars($goal) ?></p>
    <?php if ($analysis && !empty($analysis['client_name'])): ?>
    <p class="goal-label">Klien: <?= htmlspecialchars($analysis['client_name']) ?></p>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 1 — KRITERIA & ALTERNATIF
══════════════════════════════════════════════ -->
<div class="section avoid-break">
    <h3>1. Kriteria Penilaian</h3>
    <table>
        <thead>
            <tr>
                <th width="8%">Kode</th>
                <th>Nama Kriteria</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criteriaIds as $idx => $cid): ?>
            <tr>
                <td><?= $criteriaCodes[$idx] ?? ('C'.str_pad($idx+1,2,'0',STR_PAD_LEFT)) ?></td>
                <td class="left"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="section avoid-break">
    <h3>2. Alternatif yang Dievaluasi</h3>
    <table>
        <thead>
            <tr>
                <th width="8%">No</th>
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

<!-- ══════════════════════════════════════════════
     TAHAP 2 — MATRIKS PERBANDINGAN KRITERIA
══════════════════════════════════════════════ -->
<div class="section page-break">
    <h3>3. Matriks Perbandingan Berpasangan Kriteria</h3>
    <p class="info-text">Matriks berikut menunjukkan nilai perbandingan berpasangan antar kriteria menggunakan skala Saaty (1–9).</p>
    <table>
        <thead>
            <tr>
                <th class="left" width="22%">Kriteria</th>
                <?php foreach ($criteriaIds as $idx => $cid): ?>
                <th><?= $criteriaCodes[$idx] ?? ('C'.str_pad($idx+1,2,'0',STR_PAD_LEFT)) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criteriaIds as $i => $cid): ?>
            <tr>
                <td class="left"><strong><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></strong></td>
                <?php foreach ($criteriaIds as $j => $cid2):
                    $val = $criteriaMatrix[$i][$j] ?? 1;
                ?>
                <td class="<?= $i === $j ? 'diag' : '' ?>"><?= fmtVal($val) ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="left">Jumlah Kolom</td>
                <?php
                // Column sums
                $colSums = array_fill(0, $n, 0);
                for ($j = 0; $j < $n; $j++) {
                    for ($i = 0; $i < $n; $i++) {
                        $colSums[$j] += $criteriaMatrix[$i][$j] ?? 1;
                    }
                }
                foreach ($colSums as $cs): ?>
                <td><?= number_format($cs, 4) ?></td>
                <?php endforeach; ?>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 3 — NORMALISASI MATRIKS KRITERIA
══════════════════════════════════════════════ -->
<div class="section avoid-break">
    <h3>4. Normalisasi Matriks Kriteria</h3>
    <p class="info-text">Setiap sel dibagi dengan jumlah kolomnya masing-masing. Bobot prioritas diperoleh dari rata-rata setiap baris.</p>
    <table>
        <thead>
            <tr>
                <th class="left" width="22%">Kriteria</th>
                <?php foreach ($criteriaIds as $idx => $cid): ?>
                <th><?= $criteriaCodes[$idx] ?? ('C'.str_pad($idx+1,2,'0',STR_PAD_LEFT)) ?></th>
                <?php endforeach; ?>
                <th style="background:#b45309;">Jumlah Baris</th>
                <th style="background:#b45309;">Bobot (Wi)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criteriaIds as $i => $cid):
                $rowNorm = $normalizedCriteria[$i] ?? [];
                $rowSum  = !empty($rowNorm) ? array_sum($rowNorm) : 0;
                $wi      = $criteriaPriorities[$i] ?? 0;
            ?>
            <tr>
                <td class="left"><strong><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></strong></td>
                <?php foreach ($criteriaIds as $j => $cid2):
                    $nv = $rowNorm[$j] ?? 0;
                ?>
                <td><?= number_format($nv, 4) ?></td>
                <?php endforeach; ?>
                <td class="bold"><?= number_format($rowSum, 4) ?></td>
                <td class="bold" style="color:#1a5c5a;"><?= number_format($wi, 4) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="left">Jumlah</td>
                <?php for ($j = 0; $j < $n; $j++): ?>
                <td><?= number_format(array_sum(array_column($normalizedCriteria, $j)), 4) ?></td>
                <?php endfor; ?>
                <td><?= number_format(array_sum($criteriaPriorities) * $n, 4) ?></td>
                <td><?= number_format(array_sum($criteriaPriorities), 4) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 4 — BOBOT PRIORITAS + λmax, CI, CR
══════════════════════════════════════════════ -->
<div class="section avoid-break">
    <h3>5. Bobot Prioritas Kriteria & Uji Konsistensi</h3>

    <h4>Weighted Sum Vector & Consistency Vector</h4>
    <p class="info-text">Weighted Sum = Matriks Asli × Vektor Bobot. Consistency Vector = Weighted Sum ÷ Bobot (Wi).</p>
    <table>
        <thead>
            <tr>
                <th class="left" width="22%">Kriteria</th>
                <th>Bobot (Wi)</th>
                <th>Weighted Sum</th>
                <th>Consistency Vector</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criteriaIds as $i => $cid):
                $wi  = $criteriaPriorities[$i] ?? 0;
                $ws  = $weightedSumCriteria[$i] ?? 0;
                $cv  = $consVecCriteria[$i] ?? 0;
            ?>
            <tr>
                <td class="left"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></td>
                <td><?= number_format($wi, 4) ?></td>
                <td><?= number_format($ws, 4) ?></td>
                <td><?= number_format($cv, 4) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4>Nilai λmax, CI, RI, dan CR</h4>
    <table style="width:60%;">
        <thead>
            <tr>
                <th class="th-light left">Parameter</th>
                <th class="th-light">Nilai</th>
                <th class="th-light">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left">λmax (Lambda Maks)</td>
                <td><?= number_format($criteriaResult['lambdaMax'] ?? 0, 6) ?></td>
                <td class="left">Rata-rata consistency vector</td>
            </tr>
            <tr>
                <td class="left">n (Jumlah Kriteria)</td>
                <td><?= $criteriaResult['n'] ?? $n ?></td>
                <td class="left">Ordo matriks</td>
            </tr>
            <tr>
                <td class="left">CI (Consistency Index)</td>
                <td><?= number_format($criteriaResult['ci'] ?? 0, 6) ?></td>
                <td class="left">CI = (λmax − n) / (n − 1)</td>
            </tr>
            <tr>
                <td class="left">RI (Random Index)</td>
                <td><?= number_format($criteriaResult['ri'] ?? 0, 4) ?></td>
                <td class="left">Berdasarkan tabel Saaty (n = <?= $criteriaResult['n'] ?? $n ?>)</td>
            </tr>
            <tr>
                <td class="left"><strong>CR (Consistency Ratio)</strong></td>
                <td><strong><?= number_format($criteriaResult['cr'] ?? 0, 6) ?></strong></td>
                <td class="left">CR = CI / RI</td>
            </tr>
        </tbody>
    </table>

    <?php $isConsistent = $criteriaResult['consistent'] ?? false; ?>
    <div class="cr-box <?= $isConsistent ? 'pass' : 'fail' ?>">
        <?php if ($isConsistent): ?>
        ✅ CR = <?= number_format($criteriaResult['cr'] ?? 0, 4) ?> &lt; 0.1 → Matriks perbandingan kriteria <strong>KONSISTEN</strong>. Hasil analisis dapat diterima.
        <?php else: ?>
        ❌ CR = <?= number_format($criteriaResult['cr'] ?? 0, 4) ?> ≥ 0.1 → Matriks perbandingan kriteria <strong>TIDAK KONSISTEN</strong>. Disarankan mengulang penilaian.
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 5 — MATRIKS PERBANDINGAN ALTERNATIF PER KRITERIA
══════════════════════════════════════════════ -->
<div class="section page-break">
    <h3>6. Matriks Perbandingan Berpasangan Alternatif per Kriteria</h3>
    <p class="info-text">Setiap kriteria memiliki matriks perbandingan berpasangan antar alternatif.</p>

    <?php foreach ($criteriaIds as $cIdx => $cid):
        $cCode = $criteriaCodes[$cIdx] ?? ('C'.str_pad($cIdx+1,2,'0',STR_PAD_LEFT));
        $cName = $criteriaLabels[$cid] ?? $cid;
        $altMatrix = $data['pairwise_alternatives'][$cid] ?? [];
        $nAlt = count($altIds);

        // Re-compute normalization for this alt matrix
        $altColSums = array_fill(0, $nAlt, 0);
        for ($j = 0; $j < $nAlt; $j++) {
            for ($i = 0; $i < $nAlt; $i++) {
                $altColSums[$j] += $altMatrix[$i][$j] ?? 1;
            }
        }
    ?>
    <div class="avoid-break" style="margin-bottom:18px;">
        <h4><?= $cCode ?>. <?= htmlspecialchars($cName) ?></h4>
        <table>
            <thead>
                <tr>
                    <th class="left" width="25%">Alternatif</th>
                    <?php foreach ($altIds as $aIdx => $aid): ?>
                    <th>A<?= $aIdx + 1 ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($altIds as $i => $aid): ?>
                <tr>
                    <td class="left"><?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                    <?php foreach ($altIds as $j => $aid2):
                        $v = $altMatrix[$i][$j] ?? 1;
                    ?>
                    <td class="<?= $i === $j ? 'diag' : '' ?>"><?= fmtVal($v) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="left">Jumlah Kolom</td>
                    <?php foreach ($altColSums as $cs): ?>
                    <td><?= number_format($cs, 4) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 6 — NORMALISASI + BOBOT SUB-KRITERIA (ALTERNATIF)
══════════════════════════════════════════════ -->
<div class="section page-break">
    <h3>7. Normalisasi Matriks & Bobot Prioritas Alternatif per Kriteria</h3>
    <p class="info-text">Matriks perbandingan dinormalisasi, kemudian bobot prioritas dihitung sebagai rata-rata baris.</p>

    <?php foreach ($criteriaIds as $cIdx => $cid):
        $cCode = $criteriaCodes[$cIdx] ?? ('C'.str_pad($cIdx+1,2,'0',STR_PAD_LEFT));
        $cName = $criteriaLabels[$cid] ?? $cid;
        $altMatrix = $data['pairwise_alternatives'][$cid] ?? [];
        $nAlt = count($altIds);

        // Column sums
        $altColSums = array_fill(0, $nAlt, 0);
        for ($j = 0; $j < $nAlt; $j++) {
            for ($i = 0; $i < $nAlt; $i++) {
                $altColSums[$j] += $altMatrix[$i][$j] ?? 1;
            }
        }
        // Normalize
        $altNorm = [];
        for ($i = 0; $i < $nAlt; $i++) {
            for ($j = 0; $j < $nAlt; $j++) {
                $altNorm[$i][$j] = $altColSums[$j] > 0 ? ($altMatrix[$i][$j] ?? 1) / $altColSums[$j] : 0;
            }
        }
        // Priority vector (row average)
        $altWeights = [];
        for ($i = 0; $i < $nAlt; $i++) {
            $altWeights[$i] = $nAlt > 0 ? array_sum($altNorm[$i]) / $nAlt : 0;
        }

        // Consistency check for this sub-matrix
        $altResult = $results['alternatives_detail'][$cid] ?? null;
    ?>
    <div class="avoid-break" style="margin-bottom:18px;">
        <h4><?= $cCode ?>. <?= htmlspecialchars($cName) ?></h4>
        <table>
            <thead>
                <tr>
                    <th class="left" width="24%">Alternatif</th>
                    <?php foreach ($altIds as $aIdx => $aid): ?>
                    <th>A<?= $aIdx + 1 ?></th>
                    <?php endforeach; ?>
                    <th style="background:#b45309;">Jumlah</th>
                    <th style="background:#b45309;">Bobot (Wi)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($altIds as $i => $aid):
                    $rowSum = array_sum($altNorm[$i]);
                ?>
                <tr>
                    <td class="left"><?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                    <?php foreach ($altIds as $j => $aid2): ?>
                    <td><?= number_format($altNorm[$i][$j], 4) ?></td>
                    <?php endforeach; ?>
                    <td class="bold"><?= number_format($rowSum, 4) ?></td>
                    <td class="bold" style="color:#1a5c5a;"><?= number_format($altWeights[$i], 4) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="left">Jumlah</td>
                    <?php for ($j = 0; $j < $nAlt; $j++):
                        $colTotal = 0;
                        for ($i = 0; $i < $nAlt; $i++) $colTotal += $altNorm[$i][$j];
                    ?>
                    <td><?= number_format($colTotal, 4) ?></td>
                    <?php endfor; ?>
                    <td><?= number_format(array_sum($altWeights) * $nAlt, 4) ?></td>
                    <td><?= number_format(array_sum($altWeights), 4) ?></td>
                </tr>
            </tfoot>
        </table>

        <?php
        // Show CI/CR for each sub-matrix if computed
        if (!empty($altResult)):
            $altCR = $altResult['cr'] ?? null;
        ?>
        <p class="info-text" style="margin-top:4px;">
            λmax = <?= number_format($altResult['lambdaMax'] ?? 0, 4) ?>,
            CI = <?= number_format($altResult['ci'] ?? 0, 4) ?>,
            RI = <?= number_format($altResult['ri'] ?? 0, 4) ?>,
            CR = <?= number_format($altResult['cr'] ?? 0, 4) ?>
            <?php if ($altCR !== null): ?>
            — <?= $altResult['consistent'] ? '✅ Konsisten' : '❌ Tidak Konsisten' ?>
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 7 — PENENTUAN PRIORITAS GLOBAL
══════════════════════════════════════════════ -->
<div class="section page-break">
    <h3>8. Penentuan Prioritas Global Alternatif</h3>
    <p class="info-text">
        Skor global = Σ (Bobot Kriteria × Bobot Alternatif per Kriteria).
        Kolom "Bobot Wi" menunjukkan bobot kriteria yang dikalikan ke tiap kolom.
    </p>

    <table>
        <thead>
            <tr>
                <th class="left" width="22%">Alternatif</th>
                <?php foreach ($criteriaIds as $cIdx => $cid):
                    $cCode = $criteriaCodes[$cIdx] ?? ('C'.str_pad($cIdx+1,2,'0',STR_PAD_LEFT));
                    $wi = $criteriaPriorities[$cIdx] ?? 0;
                ?>
                <th><?= $cCode ?><br><small style="font-weight:normal;font-size:8pt;">(w=<?= number_format($wi,3) ?>)</small></th>
                <?php endforeach; ?>
                <th style="background:#b45309;">Skor Global</th>
                <th style="background:#b45309;">Ranking</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Build global score table in original alt order
            $globalScoreMap = [];
            foreach ($altIds as $aIdx => $aid) {
                $total = 0;
                foreach ($criteriaIds as $cIdx => $cid) {
                    $w = $criteriaPriorities[$cIdx] ?? 0;
                    $s = $altPriorities[$cid][$aIdx] ?? 0;
                    $total += $w * $s;
                }
                $globalScoreMap[$aid] = $total;
            }
            // Rank
            arsort($globalScoreMap);
            $rankMap = [];
            $r = 1;
            foreach ($globalScoreMap as $aid => $sc) {
                $rankMap[$aid] = $r++;
            }

            foreach ($altIds as $aIdx => $aid):
                $rowClass = $rankMap[$aid] === 1 ? 'rank-1' : ($rankMap[$aid] === 2 ? 'rank-2' : ($rankMap[$aid] === 3 ? 'rank-3' : ''));
                $globalScore = 0;
            ?>
            <tr class="<?= $rowClass ?>">
                <td class="left"><?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                <?php foreach ($criteriaIds as $cIdx => $cid):
                    $w = $criteriaPriorities[$cIdx] ?? 0;
                    $s = $altPriorities[$cid][$aIdx] ?? 0;
                    $globalScore += $w * $s;
                ?>
                <td><?= number_format($s, 4) ?></td>
                <?php endforeach; ?>
                <td class="bold"><?= number_format($globalScore * 100, 2) ?>%</td>
                <td class="bold">#<?= $rankMap[$aid] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="left">Bobot Kriteria (Wi)</td>
                <?php foreach ($criteriaPriorities as $wi): ?>
                <td><?= number_format($wi, 4) ?></td>
                <?php endforeach; ?>
                <td><?= number_format(array_sum($criteriaPriorities) * 100, 2) ?>%</td>
                <td>—</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ══════════════════════════════════════════════
     TAHAP 8 — HASIL AKHIR / RANKING
══════════════════════════════════════════════ -->
<div class="section avoid-break">
    <h3>9. Hasil Perankingan Akhir</h3>
    <table>
        <thead>
            <tr>
                <th width="8%">Rank</th>
                <th>Alternatif</th>
                <th width="20%">Skor Global</th>
                <th width="35%">Visualisasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $maxScore = !empty($ranked) ? $ranked[0]['score'] : 1;
            foreach ($ranked as $rIdx => $item):
                $rowClass = $rIdx === 0 ? 'rank-1' : ($rIdx === 1 ? 'rank-2' : ($rIdx === 2 ? 'rank-3' : ''));
                $barWidth = $maxScore > 0 ? ($item['score'] / $maxScore) * 100 : 0;
                $barColor = $rIdx === 0 ? 'gold' : ($rIdx === 1 ? 'silver' : ($rIdx === 2 ? 'bronze' : ''));
            ?>
            <tr class="<?= $rowClass ?>">
                <td><strong>#<?= $rIdx + 1 ?></strong></td>
                <td class="left"><?= htmlspecialchars($item['name']) ?></td>
                <td><strong><?= number_format($item['score'] * 100, 4) ?>%</strong></td>
                <td>
                    <div class="bar-container">
                        <div class="bar-fill <?= $barColor ?>" style="width:<?= $barWidth ?>%;">
                            <?= number_format($item['score'] * 100, 2) ?>%
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Kesimpulan -->
    <h4 style="margin-top:16px;">Kesimpulan</h4>
    <?php if (!empty($ranked)): ?>
    <p class="info-text">
        Berdasarkan hasil analisis AHP terhadap <strong><?= $n ?> kriteria</strong>
        dan <strong><?= count($altIds) ?> alternatif</strong>,
        alternatif terbaik adalah
        <strong><?= htmlspecialchars($ranked[0]['name']) ?></strong>
        dengan skor prioritas global sebesar <strong><?= number_format($ranked[0]['score'] * 100, 4) ?>%</strong>.
    </p>
    <?php if ($isConsistent): ?>
    <p class="info-text">
        Nilai CR = <?= number_format($criteriaResult['cr'] ?? 0, 4) ?> &lt; 0.1 menunjukkan
        bahwa seluruh perbandingan berpasangan kriteria konsisten, sehingga hasil analisis ini dapat diandalkan.
    </p>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════
     TANDA TANGAN
══════════════════════════════════════════════ -->
<div class="signature-wrapper">
    <div class="signature-box">
        <p class="city-date"><?= htmlspecialchars($institution) ?>, <?= $reportDate ?></p>
        <p class="title-label"><?= htmlspecialchars($signerTitle) ?></p>
        <div class="name-container">
            <span>(</span>
            <span class="name-line"><?= htmlspecialchars($signerName) ?></span>
            <span>)</span>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Print Button -->
<div class="no-print">
    <button onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>
    <p class="note">
        Unceklis <strong>"Headers and footers"</strong> di dialog cetak agar URL &amp; nomor halaman tidak muncul.<br>
        Pilih <strong>"Save as PDF"</strong> sebagai tujuan cetak.
    </p>
</div>

<script>
    window.onload = function() {
        document.querySelectorAll('.bar-fill').forEach(function(el) {
            if (el.offsetWidth < 38) {
                el.style.fontSize = '6.5pt';
                el.style.paddingRight = '2px';
                el.textContent = '';
            }
        });
    };
</script>
</body>
</html>
