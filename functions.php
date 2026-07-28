<?php
/**
 * AHP Calculation Engine
 * Analytical Hierarchy Process
 */

/**
 * Konversi nilai dropdown ke float
 * Contoh: '1/9' => 0.111, '3' => 3.0
 */
function parseComparisonValue($value) {
    if (strpos($value, '/') !== false) {
        $parts = explode('/', $value);
        return floatval($parts[0]) / floatval($parts[1]);
    }
    return floatval($value);
}

/**
 * Dapatkan nilai RI (Random Index) berdasarkan ukuran matriks
 */
function getRI($n) {
    global $riTable;
    if (isset($riTable[$n])) {
        return $riTable[$n];
    }
    // Untuk n > 15, gunakan aproximasi
    return 1.59 + ($n - 15) * 0.02;
}

/**
 * Hitung priority vector (eigenvector) dan consistency
 * dari matriks perbandingan berpasangan
 *
 * @param array $matrix Matriks n×n
 * @return array ['priorities', 'lambdaMax', 'ci', 'cr', 'consistent', ...]
 */
function ahpCalculate($matrix) {
    $n = count($matrix);
    if ($n <= 1) {
        return [
            'priorities' => [1.0],
            'lambdaMax' => 1,
            'ci' => 0,
            'cr' => 0,
            'consistent' => true,
        ];
    }

    // 1. Hitung jumlah setiap kolom
    $columnSums = array_fill(0, $n, 0);
    for ($j = 0; $j < $n; $j++) {
        for ($i = 0; $i < $n; $i++) {
            $columnSums[$j] += $matrix[$i][$j];
        }
    }

    // 2. Normalisasi matriks (bagi setiap cell dengan jumlah kolom)
    $normalized = [];
    for ($i = 0; $i < $n; $i++) {
        $normalized[$i] = [];
        for ($j = 0; $j < $n; $j++) {
            $normalized[$i][$j] = $columnSums[$j] > 0 ? $matrix[$i][$j] / $columnSums[$j] : 0;
        }
    }

    // 3. Priority vector (rata-rata baris dari matriks ternormalisasi)
    $priorities = [];
    for ($i = 0; $i < $n; $i++) {
        $priorities[$i] = array_sum($normalized[$i]) / $n;
    }

    // 4. Weighted sum vector (matriks asli × priority vector)
    $weightedSum = [];
    for ($i = 0; $i < $n; $i++) {
        $sum = 0;
        for ($j = 0; $j < $n; $j++) {
            $sum += $matrix[$i][$j] * $priorities[$j];
        }
        $weightedSum[$i] = $sum;
    }

    // 5. Consistency vector
    $consistencyVector = [];
    for ($i = 0; $i < $n; $i++) {
        $consistencyVector[$i] = $priorities[$i] > 0 ? $weightedSum[$i] / $priorities[$i] : 0;
    }

    // 6. λmax (rata-rata consistency vector)
    $lambdaMax = array_sum($consistencyVector) / $n;

    // 7. CI (Consistency Index)
    $ci = ($lambdaMax - $n) / ($n - 1);

    // 8. CR (Consistency Ratio)
    $ri = getRI($n);
    $cr = $ri > 0 ? $ci / $ri : 0;

    return [
        'priorities' => $priorities,
        'lambdaMax' => $lambdaMax,
        'ci' => $ci,
        'cr' => $cr,
        'ri' => $ri,
        'consistent' => $cr < 0.1,
        'n' => $n,
        'normalized' => $normalized,
        'weightedSum' => $weightedSum,
        'consistencyVector' => $consistencyVector,
    ];
}

/**
 * Hitung global priorities untuk semua alternatif
 * 
 * @param array $criteriaPriorities Bobot kriteria [c1, c2, ...]
 * @param array $alternativePriorities Prioritas lokal alternatif per kriteria [criteria_id => [alt1, alt2, ...]]
 * @return array Global priorities untuk setiap alternatif
 */
function calculateGlobalPriorities($criteriaPriorities, $alternativePriorities) {
    $criteriaIds = array_keys($alternativePriorities);
    if (empty($criteriaIds)) return [];

    $criterionIndex = 0;
    $globalPriorities = [];
    
    // Inisialisasi
    foreach ($criteriaIds as $cid) {
        foreach ($alternativePriorities[$cid] as $altIdx => $priority) {
            if (!isset($globalPriorities[$altIdx])) {
                $globalPriorities[$altIdx] = 0;
            }
        }
    }

    // Hitung global priority = sum atas semua kriteria (bobot kriteria × prioritas lokal alternatif)
    foreach ($criteriaIds as $cid) {
        if (isset($alternativePriorities[$cid])) {
            $criterionWeight = $criteriaPriorities[$criterionIndex] ?? 0;
            foreach ($alternativePriorities[$cid] as $altIdx => $priority) {
                $globalPriorities[$altIdx] += $criterionWeight * $priority;
            }
            $criterionIndex++;
        }
    }

    return $globalPriorities;
}

/**
 * Session helpers
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['ahp'])) {
        resetSession();
    }
}

function resetSession() {
    $_SESSION['ahp'] = [
        'goal' => '',
        'criteria' => [],
        'alternatives' => [],
        'pairwise_criteria' => [],
        'pairwise_alternatives' => [],
        'criteria_labels' => [],
        'alternative_labels' => [],
    ];
}

function getCurrentStep() {
    $steps = ['home', 'login', 'register', 'step1', 'step2', 'step3', 'step4', 'step5', 'results', 'dashboard', 'view', 'about', 'admin-dashboard', 'admin-users', 'admin-criteria', 'admin-alternatives'];
    $step = $_GET['page'] ?? 'home';
    if (!in_array($step, $steps)) {
        return 'home';
    }
    return $step;
}

function getStepNumber($step) {
    $steps = [
        'home' => 0,
        'step1' => 1,
        'step2' => 2,
        'step3' => 3,
        'step4' => 4,
        'step5' => 5,
        'results' => 6,
    ];
    return $steps[$step] ?? 0;
}

/**
 * Format angka untuk display
 */
function formatPriority($value) {
    return number_format($value * 100, 2) . '%';
}

function formatNumber($value, $decimals = 4) {
    return number_format($value, $decimals);
}

/**
 * Get sorted alternatives by global priority
 */
function getRankedAlternatives($data) {
    if (!isset($data['results']['globalPriorities']) || !isset($data['alternative_labels'])) {
        return [];
    }
    
    $ranked = [];
    foreach ($data['alternative_labels'] as $id => $name) {
        $idx = array_search($id, array_keys($data['alternative_labels']));
        $ranked[] = [
            'id' => $id,
            'name' => $name,
            'score' => $data['results']['globalPriorities'][$idx] ?? 0,
        ];
    }
    
    usort($ranked, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    return $ranked;
}
