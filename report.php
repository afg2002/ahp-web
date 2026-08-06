<?php
/**
 * AHP Calculator — Report PDF (Print-to-PDF)
 * Laporan untuk setiap modul CRUD dengan kop surat, tanggal, dan tanda tangan
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'db_helpers.php';

initSession();

if (!isLoggedIn()) {
    die('Silakan login terlebih dahulu.');
}

$type = $_GET['type'] ?? '';
$today = date('d F Y');
$institution = dbGetSetting('app_institution', APP_INSTITUTION);
$appName = dbGetSetting('app_name', APP_NAME);
$logoText = dbGetSetting('app_logo_text', 'A');
$logoUrl = dbGetSetting('app_logo_url', '');
$signerTitle = dbGetSetting('report_signer_title', 'Hormat Kami,');
$signerName = dbGetSetting('report_signer_name', 'Widya Corietania Basri, S.H., M.Kn.');
$headerAlign = dbGetSetting('report_header_align', 'center');

// Collect data
$data = [];
$title = '';
$headers = [];
$rows = [];

switch ($type) {
    case 'users':
        if (!isSuperAdmin()) die('Akses ditolak.');
        $title = 'Laporan Data Users';
        $data = dbGetAllUsers();
        $headers = ['No', 'Username', 'Email', 'Role', 'Status', 'Terdaftar'];
        $no = 1;
        foreach ($data as $u) {
            $rows[] = [
                $no++,
                $u['username'],
                $u['email'],
                $u['role'] === 'super_admin' ? 'Admin' : 'User',
                $u['is_active'] ? 'Aktif' : 'Nonaktif',
                date('d/m/Y', strtotime($u['created_at'])),
            ];
        }
        break;

    case 'criteria':
        if (!isSuperAdmin()) die('Akses ditolak.');
        $title = 'Laporan Data Kriteria';
        $data = dbGetAllCriteria();
        $headers = ['No', 'Kode', 'Nama Kriteria', 'Deskripsi', 'Status'];
        $no = 1;
        foreach ($data as $c) {
            $rows[] = [
                $no++,
                $c['code'],
                $c['name'],
                $c['description'] ?? '-',
                $c['is_active'] ? 'Aktif' : 'Nonaktif',
            ];
        }
        break;

    case 'alternatives':
        if (!isSuperAdmin()) die('Akses ditolak.');
        $title = 'Laporan Data Alternatif Global';
        $data = dbGetAllGlobalAlternatives();
        $headers = ['No', 'Nama Alternatif', 'Deskripsi', 'Status', 'Dibuat'];
        $no = 1;
        foreach ($data as $a) {
            $rows[] = [
                $no++,
                $a['name'],
                $a['description'] ?? '-',
                $a['is_active'] ? 'Aktif' : 'Nonaktif',
                date('d/m/Y', strtotime($a['created_at'])),
            ];
        }
        break;

    case 'analyses':
        $title = 'Laporan Data Analisis';
        $data = isSuperAdmin() ? dbGetAllAnalyses() : dbGetUserAnalyses($_SESSION['user_id']);
        $headers = ['No', 'User', 'Goal', 'Alternatif', 'Hasil Terbaik', 'Skor', 'Status', 'Tanggal'];
        $no = 1;
        foreach ($data as $a) {
            $alts = $a['alt_count'] ?? 0;
            $top = $a['top_alternative'] ?? '-';
            $score = isset($a['top_score']) ? number_format($a['top_score'] * 100, 1) . '%' : '-';
            $rows[] = [
                $no++,
                $a['user_name'] ?? htmlspecialchars($_SESSION['username'] ?? '-'),
                $a['goal'],
                $alts . ' alternatif',
                $top,
                $score,
                $a['status'] === 'completed' ? 'Selesai' : 'Draft',
                date('d/m/Y', strtotime($a['created_at'])),
            ];
        }
        break;

    default:
        die('Tipe laporan tidak valid.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars($appName) ?></title>
    <style>
        /* ── Page setup — suppress browser URL/page numbers ── */
        @page {
            margin: 1.2cm 1.5cm;
            size: auto;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Georgia', 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1a1a1a;
            width: 100%;
        }

        /* ── Kop Surat ── */
        .letterhead {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px double #1a5c5a;
        }
        .letterhead.header-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .letterhead.header-left {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 20px;
            text-align: left;
        }
        .letterhead .logo-area {
            flex-shrink: 0;
            width: 70px;
            height: 70px;
            border: 2px solid #1a5c5a;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
        }
        .letterhead.header-left .logo-area {
            margin-bottom: 0;
        }
        .letterhead .logo-area img {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }
        .letterhead .logo-area span {
            font-family: Georgia, serif;
            font-size: 26pt;
            font-weight: bold;
            font-style: italic;
            color: #1a5c5a;
        }
        .letterhead .text-area h1 {
            font-size: 15pt;
            font-weight: bold;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #1a5c5a;
        }
        .letterhead .text-area .sub {
            font-size: 10pt;
            color: #444;
            margin: 0 0 2px 0;
        }
        .letterhead .text-area .address {
            font-size: 9pt;
            color: #777;
        }

        /* ── Title ── */
        .report-title {
            text-align: center;
            margin: 20px 0 16px 0;
        }
        .report-title h3 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .report-title .periode {
            font-size: 10pt;
            color: #666;
            margin-top: 3px;
        }

        /* ── Info bar ── */
        .info-bar {
            font-size: 10pt;
            color: #555;
            margin: 8px 0 14px 0;
            padding: 6px 10px;
            background: #f5f5f5;
            display: flex;
            justify-content: space-between;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10.5pt;
        }
        table thead th {
            background-color: #1a5c5a;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 7px 8px;
            border: 1px solid #134e4a;
            font-size: 10pt;
        }
        table tbody td {
            padding: 6px 8px;
            border: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
        }
        table tbody td.left { text-align: left; }
        table tbody tr:nth-child(even) { background: #f9f9f9; }

        /* ── Signature ── */
        .signature-wrapper {
            margin-top: 48px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 280px;
        }
        .signature-box .city-date {
            font-size: 11pt;
            margin-bottom: 4px;
            white-space: nowrap;
        }
        .signature-box .title-label {
            font-size: 10pt;
            color: #555;
            margin-bottom: 75px;
        }
        .signature-box .name-container {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
            white-space: nowrap;
        }
        .signature-box .name-line {
            display: inline-block;
            min-width: 210px;
            border-bottom: 1.5px dotted #1a1a1a;
            text-align: center;
            padding-bottom: 2px;
            margin: 0 4px;
        }

        /* ── Print button (screen only) ── */
        .no-print {
            text-align: center;
            margin: 30px 0;
        }
        .no-print button {
            background: #1a5c5a;
            color: white;
            border: none;
            padding: 14px 36px;
            font-size: 14px;
            cursor: pointer;
            font-family: Georgia, serif;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .no-print button:hover { background: #134e4a; }
        .no-print .print-note {
            margin-top: 8px;
            font-size: 11px;
            color: #888;
            font-family: Georgia, serif;
        }

        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
            table thead th {
                background-color: #1a5c5a !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            table tbody tr:nth-child(even) {
                background: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .letterhead .logo-area {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

    <!-- ═══════════════════════════════════════════
         KOP SURAT — CENTER / LEFT ALIGNED (CUSTOM)
         ═══════════════════════════════════════════ -->
    <div class="letterhead <?= $headerAlign === 'center' ? 'header-center' : 'header-left' ?>">
        <div class="logo-area">
            <?php if (!empty($logoUrl)): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
            <?php else: ?>
            <span><?= htmlspecialchars($logoText) ?></span>
            <?php endif; ?>
        </div>
        <div class="text-area">
            <h1><?= htmlspecialchars($institution) ?></h1>
            <p class="sub">Sistem Pendukung Keputusan — Metode Analytical Hierarchy Process (AHP)</p>
            <p class="address"><?= htmlspecialchars($appName) ?> — Prioritas Pengurusan Akta</p>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         TITLE
         ═══════════════════════════════════════════ -->
    <div class="report-title">
        <h3><?= htmlspecialchars($title) ?></h3>
        <p class="periode">Per <?= $today ?></p>
    </div>

    <!-- ═══════════════════════════════════════════
         INFO BAR
         ═══════════════════════════════════════════ -->
    <div class="info-bar">
        <span>Total Data: <strong><?= count($data) ?></strong>
            <?php
                $label = match($type) {
                    'users' => 'user',
                    'criteria' => 'kriteria',
                    'alternatives' => 'alternatif',
                    'analyses' => 'analisis',
                    default => 'data'
                };
                echo $label;
            ?>
        </span>
        <span>Tanggal Cetak: <?= $today ?></span>
    </div>

    <!-- ═══════════════════════════════════════════
         DATA TABLE
         ═══════════════════════════════════════════ -->
    <table>
        <thead>
            <tr>
                <?php foreach ($headers as $h): ?>
                <th><?= htmlspecialchars($h) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="<?= count($headers) ?>" style="padding:24px;color:#aaa;text-align:center;">Tidak ada data.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <?php foreach ($r as $i => $cell): ?>
                    <td class="<?= $i === 0 ? '' : 'left' ?>"><?= htmlspecialchars($cell) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ═══════════════════════════════════════════
         TANDA TANGAN
         ═══════════════════════════════════════════ -->
    <div class="signature-wrapper">
        <div class="signature-box">
            <p class="city-date"><?= htmlspecialchars($institution) ?>, <?= $today ?></p>
            <p class="title-label"><?= htmlspecialchars($signerTitle) ?></p>
            <div class="name-container">
                <span>(</span>
                <span class="name-line"><?= htmlspecialchars($signerName) ?></span>
                <span>)</span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         PRINT BUTTON
         ═══════════════════════════════════════════ -->
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <p class="print-note">
            ✓ Unceklis <strong>"Header dan Footer"</strong> (atau "Headers and footers") di dialog cetak browser<br>
            agar URL dan nomor halaman di tepi kertas tidak muncul.<br>
            Pilih <strong>"Save as PDF"</strong> sebagai tujuan cetak.
        </p>
    </div>

</body>
</html>
