<?php
/**
 * AHP Calculator — Export Handler
 * Menangani export CSV untuk semua modul CRUD
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'db_helpers.php';

initSession();

$type = $_GET['type'] ?? '';

if (!isLoggedIn()) {
    header('Content-Type: text/plain');
    die('Silakan login terlebih dahulu.');
}

try {
    switch ($type) {
        case 'users':
            if (!isSuperAdmin()) die('Akses ditolak.');
            exportUsersCSV();
            break;
        case 'criteria':
            if (!isSuperAdmin()) die('Akses ditolak.');
            exportCriteriaCSV();
            break;
        case 'alternatives':
            if (!isSuperAdmin()) die('Akses ditolak.');
            exportAlternativesCSV();
            break;
        case 'analyses':
            exportAnalysesCSV();
            break;
        default:
            header('Content-Type: text/plain');
            die('Tipe export tidak valid. Gunakan: users, criteria, alternatives, analyses');
    }
} catch (Exception $e) {
    header('Content-Type: text/plain');
    die('Error: ' . $e->getMessage());
}
