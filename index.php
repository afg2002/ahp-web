<?php
/**
 * AHP Calculator — Main Router
 * Sistem Pendukung Keputusan dengan Analytical Hierarchy Process
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'database.php';
require_once 'db_helpers.php';

initSession();

// Database status flag
$dbReady = false;
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $dbReady = $conn->select_db(DB_NAME);
    if (!$dbReady) {
        $dbReady = dbIsSetupComplete();
    } else {
        $dbReady = dbIsSetupComplete();
    }
} catch (Exception $e) {
    $dbReady = false;
}

$step = getCurrentStep();
$data = $_SESSION['ahp'];

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ============================================================
    // AUTH HANDLERS
    // ============================================================

    // Register
    if ($action === 'auth_register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        // Validation
        if (strlen($username) < 3) {
            $_SESSION['flash_message'] = 'Username minimal 3 karakter.';
            $_SESSION['flash_type'] = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Email tidak valid.';
            $_SESSION['flash_type'] = 'error';
        } elseif (strlen($password) < 6) {
            $_SESSION['flash_message'] = 'Password minimal 6 karakter.';
            $_SESSION['flash_type'] = 'error';
        } elseif ($password !== $passwordConfirm) {
            $_SESSION['flash_message'] = 'Konfirmasi password tidak cocok.';
            $_SESSION['flash_type'] = 'error';
        } else {
            $result = dbRegisterUser($username, $email, $password);
            if ($result['success']) {
                // Auto-login after register
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $username;
                $_SESSION['user_role'] = 'user';
                $_SESSION['flash_message'] = 'Pendaftaran berhasil! Selamat datang.';
                $_SESSION['flash_type'] = 'success';
                header('Location: ?page=dashboard');
                exit;
            } else {
                $_SESSION['flash_message'] = $result['error'];
                $_SESSION['flash_type'] = 'error';
            }
        }
        header('Location: ?page=register');
        exit;
    }

    // Login
    if ($action === 'auth_login') {
        $loginId = trim($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $result = dbLoginUser($loginId, $password);
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['user_role'] = $result['user']['role'];
            $_SESSION['flash_message'] = 'Selamat datang kembali, ' . htmlspecialchars($result['user']['username']) . '!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ?page=dashboard');
            exit;
        } else {
            $_SESSION['flash_message'] = $result['error'];
            $_SESSION['flash_type'] = 'error';
            header('Location: ?page=login');
            exit;
        }
    }

    // Logout
    if ($action === 'auth_logout') {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role']);
        resetSession();
        $_SESSION['flash_message'] = 'Anda berhasil keluar.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=home');
        exit;
    }

    // Update Profile
    if ($action === 'update_profile') {
        requireLogin();
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($username) < 3) {
            $_SESSION['flash_message'] = 'Username minimal 3 karakter.';
            $_SESSION['flash_type'] = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Email tidak valid.';
            $_SESSION['flash_type'] = 'error';
        } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
            $_SESSION['flash_message'] = 'Password baru minimal 6 karakter.';
            $_SESSION['flash_type'] = 'error';
        } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $_SESSION['flash_message'] = 'Konfirmasi password baru tidak cocok.';
            $_SESSION['flash_type'] = 'error';
        } else {
            $result = dbUpdateUserProfile($_SESSION['user_id'], $username, $email, $currentPassword, $newPassword);
            if ($result['success']) {
                $_SESSION['flash_message'] = 'Profil Anda berhasil diperbarui.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['error'];
                $_SESSION['flash_type'] = 'error';
            }
        }
        header('Location: ?page=profile');
        exit;
    }


    // ============================================================
    // ADMIN CRUD HANDLERS
    // ============================================================

    // Admin: Create User
    if ($action === 'admin_create_user') {
        requireAdmin();
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        if (strlen($username) < 3) { $_SESSION['flash_message'] = 'Username minimal 3 karakter.'; $_SESSION['flash_type'] = 'error'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $_SESSION['flash_message'] = 'Email tidak valid.'; $_SESSION['flash_type'] = 'error'; }
        elseif (strlen($password) < 6) { $_SESSION['flash_message'] = 'Password minimal 6 karakter.'; $_SESSION['flash_type'] = 'error'; }
        else {
            $result = dbCreateUser($username, $email, $password, $role);
            $_SESSION['flash_message'] = $result['success'] ? 'User berhasil dibuat.' : $result['error'];
            $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';
        }
        header('Location: ?page=admin-users'); exit;
    }

    // Admin: Update User
    if ($action === 'admin_update_user') {
        requireAdmin();
        $id = intval($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['username'])) $data['username'] = trim($_POST['username']);
        if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
        if (isset($_POST['role'])) $data['role'] = $_POST['role'];
        if (isset($_POST['is_active'])) $data['is_active'] = intval($_POST['is_active']);
        if (!empty($_POST['password'])) $data['password'] = $_POST['password'];
        dbUpdateUser($id, $data);
        $_SESSION['flash_message'] = 'User berhasil diperbarui.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=admin-users'); exit;
    }

    // Admin: Delete User
    if ($action === 'admin_delete_user') {
        requireAdmin();
        $id = intval($_POST['id'] ?? 0);
        if (dbDeleteUser($id)) {
            $_SESSION['flash_message'] = 'User berhasil dihapus.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Tidak dapat menghapus diri sendiri.';
            $_SESSION['flash_type'] = 'error';
        }
        header('Location: ?page=admin-users'); exit;
    }

    // Admin: Create Criteria
    if ($action === 'admin_create_criteria') {
        requireAdmin();
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (empty($code) || empty($name)) {
            $_SESSION['flash_message'] = 'Kode dan nama kriteria wajib diisi.';
            $_SESSION['flash_type'] = 'error';
        } else {
            $result = dbCreateCriteria($code, $name, $desc);
            $_SESSION['flash_message'] = $result['success'] ? 'Kriteria berhasil dibuat.' : $result['error'];
            $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';
        }
        header('Location: ?page=admin-criteria'); exit;
    }

    // Admin: Update Criteria
    if ($action === 'admin_update_criteria') {
        requireAdmin();
        $id = intval($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['code'])) $data['code'] = strtoupper(trim($_POST['code']));
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['description'])) $data['description'] = trim($_POST['description']);
        if (isset($_POST['is_active'])) $data['is_active'] = intval($_POST['is_active']);
        dbUpdateCriteria($id, $data);
        $_SESSION['flash_message'] = 'Kriteria berhasil diperbarui.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=admin-criteria'); exit;
    }

    // Admin: Delete Criteria
    if ($action === 'admin_delete_criteria') {
        requireAdmin();
        dbDeleteCriteria(intval($_POST['id'] ?? 0));
        $_SESSION['flash_message'] = 'Kriteria berhasil dihapus.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=admin-criteria'); exit;
    }

    // Admin: Create Global Alternative
    if ($action === 'admin_create_alternative') {
        requireAdmin();
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (empty($name)) {
            $_SESSION['flash_message'] = 'Nama alternatif wajib diisi.';
            $_SESSION['flash_type'] = 'error';
        } else {
            dbCreateGlobalAlternative($name, $desc);
            $_SESSION['flash_message'] = 'Alternatif berhasil dibuat.';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: ?page=admin-alternatives'); exit;
    }

    // Admin: Update Global Alternative
    if ($action === 'admin_update_alternative') {
        requireAdmin();
        $id = intval($_POST['id'] ?? 0);
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['description'])) $data['description'] = trim($_POST['description']);
        if (isset($_POST['is_active'])) $data['is_active'] = intval($_POST['is_active']);
        dbUpdateGlobalAlternative($id, $data);
        $_SESSION['flash_message'] = 'Alternatif berhasil diperbarui.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=admin-alternatives'); exit;
    }

    // Admin: Delete Global Alternative
    if ($action === 'admin_delete_alternative') {
        requireAdmin();
        dbDeleteGlobalAlternative(intval($_POST['id'] ?? 0));
        $_SESSION['flash_message'] = 'Alternatif berhasil dihapus.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=admin-alternatives'); exit;
    }

    // Admin: Save App & Report Settings
    if ($action === 'admin_save_settings') {
        requireAdmin();
        if (isset($_POST['app_name'])) dbSetSetting('app_name', trim($_POST['app_name']));
        if (isset($_POST['app_institution'])) dbSetSetting('app_institution', trim($_POST['app_institution']));
        if (isset($_POST['app_logo_text'])) dbSetSetting('app_logo_text', strtoupper(trim($_POST['app_logo_text'])));
        if (isset($_POST['app_logo_url'])) dbSetSetting('app_logo_url', trim($_POST['app_logo_url']));
        if (isset($_POST['report_signer_title'])) dbSetSetting('report_signer_title', trim($_POST['report_signer_title']));
        if (isset($_POST['report_signer_name'])) dbSetSetting('report_signer_name', trim($_POST['report_signer_name']));
        if (isset($_POST['report_header_align'])) dbSetSetting('report_header_align', $_POST['report_header_align']);

        $_SESSION['flash_message'] = 'Pengaturan aplikasi & laporan berhasil disimpan.';
        $_SESSION['flash_type'] = 'success';
        header('Location: ?page=admin-settings'); exit;
    }


    // ============================================================
    // AHP STEP HANDLERS
    // ============================================================

    // Reset / New calculation
    if ($action === 'reset') {
        resetSession();
        header('Location: ?page=home');
        exit;
    }

    // Step 1: Save Goal
    if ($action === 'save_goal') {
        $_SESSION['ahp']['goal'] = trim($_POST['goal'] ?? '');
        header('Location: ?page=step2');
        exit;
    }

    // Step 2: Save Criteria
    if ($action === 'save_criteria') {
        $criteriaNames = $_POST['criteria_names'] ?? [];
        $newCriteria = [];
        $labels = [];
        foreach ($criteriaNames as $idx => $name) {
            $name = trim($name);
            if (!empty($name)) {
                $newCriteria[$idx] = $name;
                $labels[$idx] = $name;
            }
        }
        $_SESSION['ahp']['criteria'] = $newCriteria;
        $_SESSION['ahp']['criteria_labels'] = $labels;
        // Reset pairwise data if criteria changed
        $_SESSION['ahp']['pairwise_criteria'] = [];
        $_SESSION['ahp']['pairwise_alternatives'] = [];
        $_SESSION['ahp']['results'] = [];
        header('Location: ?page=step3');
        exit;
    }

    // Step 3: Add Alternative (stay on step 3)
    if ($action === 'add_alternative') {
        $name = trim($_POST['new_alt_name'] ?? '');
        if (!empty($name)) {
            $alternatives = $_SESSION['ahp']['alternatives'] ?? [];
            $labels = $_SESSION['ahp']['alternative_labels'] ?? [];
            // Find the next available ID
            $maxNum = 0;
            foreach (array_keys($alternatives) as $id) {
                $num = intval(substr($id, 1));
                if ($num > $maxNum) $maxNum = $num;
            }
            $newId = 'a' . ($maxNum + 1);
            $alternatives[$newId] = $name;
            $labels[$newId] = $name;
            $_SESSION['ahp']['alternatives'] = $alternatives;
            $_SESSION['ahp']['alternative_labels'] = $labels;
            // Reset pairwise data since alternatives changed
            $_SESSION['ahp']['pairwise_alternatives'] = [];
            $_SESSION['ahp']['results'] = [];
        }
        header('Location: ?page=step3');
        exit;
    }

    // Step 3: Delete Alternative (stay on step 3)
    if ($action === 'delete_alternative') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $alternatives = $_SESSION['ahp']['alternatives'] ?? [];
            unset($alternatives[$id]);
            $_SESSION['ahp']['alternatives'] = $alternatives;
            // Also clean up labels
            $labels = $_SESSION['ahp']['alternative_labels'] ?? [];
            unset($labels[$id]);
            $_SESSION['ahp']['alternative_labels'] = $labels;
            // Reset pairwise data if alternatives changed
            $_SESSION['ahp']['pairwise_alternatives'] = [];
            $_SESSION['ahp']['results'] = [];
        }
        header('Location: ?page=step3');
        exit;
    }

    // Step 3: Save Alternatives & proceed to step 4
    if ($action === 'save_alternatives') {
        $altNames = $_POST['alt_names'] ?? [];
        $newAlts = [];
        $labels = [];
        foreach ($altNames as $idx => $name) {
            $name = trim($name);
            if (!empty($name)) {
                $newAlts[$idx] = $name;
                $labels[$idx] = $name;
            }
        }
        $_SESSION['ahp']['alternatives'] = $newAlts;
        $_SESSION['ahp']['alternative_labels'] = $labels;
        $_SESSION['ahp']['pairwise_alternatives'] = [];
        $_SESSION['ahp']['results'] = [];

        // Sync the working alternatives to the global alternatives table
        if ($dbReady) {
            try {
                dbReplaceAllGlobalAlternatives(array_values($newAlts));
            } catch (Exception $e) {}
        }

        header('Location: ?page=step4');
        exit;
    }

    // Step 4: Save Pairwise Criteria
    if ($action === 'save_pairwise_criteria') {
        $criteriaIds = array_keys($_SESSION['ahp']['criteria']);
        $n = count($criteriaIds);
        
        // Build matrix
        $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $key = $criteriaIds[$i] . '_' . $criteriaIds[$j];
                $val = $_POST[$key] ?? '1';
                $parsed = parseComparisonValue($val);
                $matrix[$i][$j] = $parsed;
                $matrix[$j][$i] = $parsed > 0 ? 1 / $parsed : 0;
            }
        }
        
        $_SESSION['ahp']['pairwise_criteria'] = $matrix;
        
        // Save criteria labels for matrix display
        $labels = [];
        foreach ($criteriaIds as $idx => $id) {
            $labels[$idx] = $_SESSION['ahp']['criteria'][$id];
        }
        $_SESSION['ahp']['pairwise_criteria_labels'] = $labels;
        
        header('Location: ?page=step5');
        exit;
    }

    // Save analysis to database
    if ($action === 'save_to_db') {
        if ($dbReady && !empty($data['results'])) {
            $analysisId = dbSaveAnalysis($data);
            $_SESSION['ahp']['saved_analysis_id'] = $analysisId;
            $_SESSION['flash_message'] = 'Analisis berhasil disimpan ke database!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ?page=results');
            exit;
        }
        header('Location: ?page=results');
        exit;
    }
    
    // Load analysis from database
    if ($action === 'load_analysis') {
        $id = intval($_POST['analysis_id'] ?? 0);
        if ($id > 0 && $dbReady && isLoggedIn()) {
            // Verify ownership
            $analysis = dbGetAnalysis($id);
            if ($analysis && $analysis['user_id'] == $_SESSION['user_id']) {
                dbLoadAnalysisIntoSession($id);
                $_SESSION['flash_message'] = 'Analisis berhasil dimuat!';
                $_SESSION['flash_type'] = 'success';
                header('Location: ?page=results');
                exit;
            }
        }
        header('Location: ?page=dashboard');
        exit;
    }
    
    // Delete analysis from database
    if ($action === 'delete_analysis') {
        $id = intval($_POST['analysis_id'] ?? 0);
        if ($id > 0 && $dbReady && isLoggedIn()) {
            // Verify ownership
            $analysis = dbGetAnalysis($id);
            if ($analysis && $analysis['user_id'] == $_SESSION['user_id']) {
                dbDeleteAnalysis($id);
                $_SESSION['flash_message'] = 'Analisis berhasil dihapus!';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Anda tidak memiliki akses ke analisis ini.';
                $_SESSION['flash_type'] = 'error';
            }
        }
        header('Location: ?page=dashboard');
        exit;
    }
    
    // Step 5: Save Pairwise Alternatives for a criterion
    if ($action === 'save_pairwise_alternatives') {
        $criteriaId = $_POST['criteria_id'] ?? '';
        $altIds = array_keys($_SESSION['ahp']['alternatives']);
        $n = count($altIds);
        
        $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $key = $criteriaId . '_' . $altIds[$i] . '_' . $altIds[$j];
                $val = $_POST[$key] ?? '1';
                $parsed = parseComparisonValue($val);
                $matrix[$i][$j] = $parsed;
                $matrix[$j][$i] = $parsed > 0 ? 1 / $parsed : 0;
            }
        }
        
        $_SESSION['ahp']['pairwise_alternatives'][$criteriaId] = $matrix;
        
        // Check if all criteria have been compared
        $criteriaIds = array_keys($_SESSION['ahp']['criteria']);
        $allDone = true;
        foreach ($criteriaIds as $cid) {
            if (!isset($_SESSION['ahp']['pairwise_alternatives'][$cid])) {
                $allDone = false;
                break;
            }
        }
        
        if ($allDone) {
            // Calculate results!
            calculateResults();
            header('Location: ?page=results');
            exit;
        }
        
        // Go to next criterion
        $nextCriterion = null;
        foreach ($criteriaIds as $cid) {
            if (!isset($_SESSION['ahp']['pairwise_alternatives'][$cid])) {
                $nextCriterion = $cid;
                break;
            }
        }
        if ($nextCriterion) {
            header('Location: ?page=step5&criterion=' . $nextCriterion);
            exit;
        }
        
        header('Location: ?page=results');
        exit;
    }
}

/**
 * Calculate all AHP results
 */
function calculateResults() {
    $data = $_SESSION['ahp'];
    
    // 1. Calculate criteria priorities
    if (!empty($data['pairwise_criteria'])) {
        $criteriaResult = ahpCalculate($data['pairwise_criteria']);
        $_SESSION['ahp']['results']['criteria'] = $criteriaResult;
    } else {
        // Equal weights if no pairwise
        $n = count($data['criteria']);
        $equalWeight = 1 / $n;
        $priorities = array_fill(0, $n, $equalWeight);
        $_SESSION['ahp']['results']['criteria'] = [
            'priorities' => $priorities,
            'lambdaMax' => $n,
            'ci' => 0,
            'cr' => 0,
            'consistent' => true,
        ];
    }
    
    $criteriaPriorities = $_SESSION['ahp']['results']['criteria']['priorities'];
    
    // 2. Calculate alternative priorities for each criterion
    $allAltPriorities = [];
    foreach ($data['pairwise_alternatives'] as $cid => $matrix) {
        $altResult = ahpCalculate($matrix);
        $allAltPriorities[$cid] = $altResult['priorities'];
    }
    $_SESSION['ahp']['results']['alternatives'] = $allAltPriorities;
    
    // 3. Calculate global priorities
    $globalPriorities = calculateGlobalPriorities($criteriaPriorities, $allAltPriorities);
    $_SESSION['ahp']['results']['globalPriorities'] = $globalPriorities;
    
    // 4. Rank
    $_SESSION['ahp']['results']['ranked'] = getRankedAlternatives($_SESSION['ahp']);
}

// ============================================================
// PAGE PROTECTION — require login for dashboard & steps
// ============================================================

$publicPages = ['home', 'login', 'register', 'about'];
$protectedPages = ['profile', 'step1', 'step2', 'step3', 'step4', 'step5', 'results', 'dashboard', 'view'];
$adminPages = ['admin-dashboard', 'admin-users', 'admin-criteria', 'admin-alternatives', 'admin-settings'];

if (in_array($step, $protectedPages) && !isLoggedIn()) {
    $_SESSION['flash_message'] = 'Silakan masuk untuk melanjutkan.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ?page=login');
    exit;
}

if (in_array($step, $adminPages) && (!isLoggedIn() || !isSuperAdmin())) {
    $_SESSION['flash_message'] = 'Akses khusus administrator.';
    $_SESSION['flash_type'] = 'error';
    header('Location: ?page=login');
    exit;
}

// Handle step5 navigation to specific criterion
if ($step === 'step5') {
    $criteriaIds = array_keys($data['criteria']);
    $currentCriterion = $_GET['criterion'] ?? ($criteriaIds[0] ?? '');
    if (!$currentCriterion || !isset($data['criteria'][$currentCriterion])) {
        $currentCriterion = $criteriaIds[0] ?? '';
    }
}

// Include view
$viewPath = __DIR__ . '/views/';

// Map page names to view files
$viewMap = [
    'home' => 'home.php',
    'login' => 'auth-login.php',
    'register' => 'auth-register.php',
    'profile' => 'profile.php',
    'step1' => 'step1.php',
    'step2' => 'step2.php',
    'step3' => 'step3.php',
    'step4' => 'step4.php',
    'step5' => 'step5.php',
    'results' => 'results.php',
    'about' => 'about.php',
    'dashboard' => 'dashboard.php',
    'view' => 'view-analysis.php',
    'admin-dashboard' => 'admin-dashboard.php',
    'admin-users' => 'admin-users.php',
    'admin-criteria' => 'admin-criteria.php',
    'admin-alternatives' => 'admin-alternatives.php',
    'admin-settings' => 'admin-settings.php',
];

$viewFile = $viewMap[$step] ?? 'home.php';

include $viewPath . 'header.php';

if (file_exists($viewPath . $viewFile)) {
    include $viewPath . $viewFile;
} else {
    include $viewPath . 'home.php';
}

include $viewPath . 'footer.php';
