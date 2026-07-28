<?php
/**
 * Konfigurasi AHP Calculator
 */

// Skala Saaty untuk perbandingan berpasangan
$saatyScale = [
    '1/9' => '1/9 - Extremely Less Important',
    '1/8' => '1/8',
    '1/7' => '1/7 - Very Strongly Less Important',
    '1/6' => '1/6',
    '1/5' => '1/5 - Strongly Less Important',
    '1/4' => '1/4',
    '1/3' => '1/3 - Moderately Less Important',
    '1/2' => '1/2',
    '1'   => '1 - Equal Importance',
    '2'   => '2',
    '3'   => '3 - Moderate Importance',
    '4'   => '4',
    '5'   => '5 - Strong Importance',
    '6'   => '6',
    '7'   => '7 - Very Strong Importance',
    '8'   => '8',
    '9'   => '9 - Extreme Importance',
];

// Simple Saaty scale values for dropdowns
$saatyValues = ['1/9','1/8','1/7','1/6','1/5','1/4','1/3','1/2','1','2','3','4','5','6','7','8','9'];

// Random Index (RI) untuk Consistency Ratio
$riTable = [
    1  => 0.00,
    2  => 0.00,
    3  => 0.58,
    4  => 0.90,
    5  => 1.12,
    6  => 1.24,
    7  => 1.32,
    8  => 1.41,
    9  => 1.45,
    10 => 1.49,
    11 => 1.51,
    12 => 1.48,
    13 => 1.56,
    14 => 1.57,
    15 => 1.59,
];

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ahp_calculator');
define('DB_PORT', 3306);

// Nama aplikasi
define('APP_NAME', 'AHP Calculator');
define('APP_TAGLINE', 'Sistem Pendukung Keputusan dengan Analytical Hierarchy Process');
define('APP_INSTITUTION', 'Widya Corietania Basri');

// Default goal untuk skripsi
define('DEFAULT_GOAL', 'Prioritas Pengurusan Akta di Widya Corietania Basri');
