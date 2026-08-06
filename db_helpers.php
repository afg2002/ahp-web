<?php
/**
 * Database Helper Functions for AHP
 * Includes admin CRUD, user management, global alternatives, reporting
 */

// ============================================================
// AUTH HELPERS
// ============================================================

function dbRegisterUser($username, $email, $password)
{
    $db = Database::getInstance();
    $existing = $db->getRow(
        "SELECT id FROM users WHERE username = ? OR email = ?",
        [$username, $email],
    );
    if ($existing) {
        return [
            "success" => false,
            "error" => "Username atau email sudah terdaftar.",
        ];
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    // Try new schema with role column, fallback to old schema
    try {
        $userId = $db->insert(
            "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')",
            [$username, $email, $passwordHash],
        );
    } catch (Exception $e) {
        $userId = $db->insert(
            "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)",
            [$username, $email, $passwordHash],
        );
    }
    return ["success" => true, "user_id" => $userId];
}

function dbLoginUser($loginId, $password)
{
    $db = Database::getInstance();
    // Try with is_active check (new schema), fallback to old schema
    try {
        $user = $db->getRow(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = TRUE",
            [$loginId, $loginId],
        );
    } catch (Exception $e) {
        $user = $db->getRow(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$loginId, $loginId],
        );
    }
    if (!$user) {
        return ["success" => false, "error" => "Akun tidak ditemukan."];
    }
    if (!password_verify($password, $user["password_hash"])) {
        return ["success" => false, "error" => "Password salah."];
    }

    // Update last login (new schema) — silent fail if column doesn't exist
    try {
        $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [
            $user["id"],
        ]);
    } catch (Exception $e) {
    }

    // Ensure 'role' key exists for backward compat
    if (!isset($user["role"])) {
        $user["role"] = "user";
    }
    if (!isset($user["is_active"])) {
        $user["is_active"] = 1;
    }

    return ["success" => true, "user" => $user];
}

function isLoggedIn()
{
    return isset($_SESSION["user_id"]) && $_SESSION["user_id"] > 0;
}

function isSuperAdmin()
{
    return isset($_SESSION["user_role"]) &&
        $_SESSION["user_role"] === "super_admin";
}

function requireLogin()
{
    if (!isLoggedIn()) {
        $_SESSION["flash_message"] = "Silakan masuk terlebih dahulu.";
        $_SESSION["flash_type"] = "error";
        header("Location: ?page=login");
        exit();
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isSuperAdmin()) {
        $_SESSION["flash_message"] = "Akses khusus administrator.";
        $_SESSION["flash_type"] = "error";
        header("Location: ?page=dashboard");
        exit();
    }
}

function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }
    $db = Database::getInstance();
    // Try new schema with role/is_active/last_login, fallback to old schema
    try {
        $user = $db->getRow(
            "SELECT id, username, email, role, is_active, last_login, created_at FROM users WHERE id = ?",
            [$_SESSION["user_id"]],
        );
    } catch (Exception $e) {
        $user = $db->getRow(
            "SELECT id, username, email, created_at FROM users WHERE id = ?",
            [$_SESSION["user_id"]],
        );
        if ($user) {
            $user["role"] = "user";
            $user["is_active"] = 1;
            $user["last_login"] = null;
        }
    }
    return $user;
}

// ============================================================
// USER CRUD (ADMIN)
// ============================================================

function dbGetAllUsers()
{
    $db = Database::getInstance();
    return $db->getRows(
        "SELECT id, username, email, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC",
    );
}

function dbGetUser($id)
{
    $db = Database::getInstance();
    return $db->getRow(
        "SELECT id, username, email, role, is_active, last_login, created_at FROM users WHERE id = ?",
        [$id],
    );
}

function dbCreateUser($username, $email, $password, $role = "user")
{
    $db = Database::getInstance();
    $existing = $db->getRow(
        "SELECT id FROM users WHERE username = ? OR email = ?",
        [$username, $email],
    );
    if ($existing) {
        return [
            "success" => false,
            "error" => "Username atau email sudah digunakan.",
        ];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $db->insert(
        "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)",
        [$username, $email, $hash, $role],
    );
    return ["success" => true];
}

function dbUpdateUser($id, $data)
{
    $db = Database::getInstance();
    $fields = [];
    $params = [];
    if (isset($data["username"])) {
        $fields[] = "username = ?";
        $params[] = $data["username"];
    }
    if (isset($data["email"])) {
        $fields[] = "email = ?";
        $params[] = $data["email"];
    }
    if (isset($data["role"])) {
        $fields[] = "role = ?";
        $params[] = $data["role"];
    }
    if (isset($data["is_active"])) {
        $fields[] = "is_active = ?";
        $params[] = $data["is_active"];
    }
    if (isset($data["password"]) && !empty($data["password"])) {
        $fields[] = "password_hash = ?";
        $params[] = password_hash($data["password"], PASSWORD_DEFAULT);
    }
    if (empty($fields)) {
        return false;
    }
    $params[] = $id;
    $db->execute(
        "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?",
        $params,
    );
    return true;
}

function dbUpdateUserProfile($userId, $username, $email, $currentPassword = null, $newPassword = null)
{
    $db = Database::getInstance();
    $user = $db->getRow("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        return ["success" => false, "error" => "User tidak ditemukan."];
    }

    // Check duplicate username/email for other users
    $existing = $db->getRow(
        "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?",
        [$username, $email, $userId]
    );
    if ($existing) {
        return ["success" => false, "error" => "Username atau email sudah digunakan pengguna lain."];
    }

    $updateData = [
        "username" => $username,
        "email" => $email,
    ];

    // If changing password, verify current password
    if (!empty($newPassword)) {
        if (empty($currentPassword) || !password_verify($currentPassword, $user["password_hash"])) {
            return ["success" => false, "error" => "Password saat ini tidak sesuai."];
        }
        $updateData["password"] = $newPassword;
    }

    $updated = dbUpdateUser($userId, $updateData);
    if ($updated) {
        $_SESSION["username"] = $username;
        return ["success" => true];
    }
    return ["success" => false, "error" => "Gagal memperbarui profil."];
}


function dbDeleteUser($id)
{
    $db = Database::getInstance();
    if ($id == $_SESSION["user_id"]) {
        return false;
    } // Can't delete self
    return $db->execute("DELETE FROM users WHERE id = ?", [$id]);
}

function dbGetUserStats()
{
    $db = Database::getInstance();
    return [
        "total" => $db->getValue("SELECT COUNT(*) FROM users"),
        "active" => $db->getValue(
            "SELECT COUNT(*) FROM users WHERE is_active = TRUE",
        ),
        "admins" => $db->getValue(
            "SELECT COUNT(*) FROM users WHERE role = 'super_admin'",
        ),
        "regular" => $db->getValue(
            "SELECT COUNT(*) FROM users WHERE role = 'user'",
        ),
        "recent" => $db->getRows(
            "SELECT id, username, email, role, last_login, created_at FROM users ORDER BY created_at DESC LIMIT 5",
        ),
    ];
}

// ============================================================
// CRITERIA CRUD (ADMIN)
// ============================================================

function dbGetAllCriteria()
{
    $db = Database::getInstance();
    return $db->getRows("SELECT * FROM criteria ORDER BY code ASC");
}

function dbGetActiveCriteria()
{
    $db = Database::getInstance();
    return $db->getRows(
        "SELECT * FROM criteria WHERE is_active = 1 ORDER BY code ASC",
    );
}

function dbGetCriteria($id)
{
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM criteria WHERE id = ?", [$id]);
}

function dbCreateCriteria($code, $name, $description = "")
{
    $db = Database::getInstance();
    $existing = $db->getRow("SELECT id FROM criteria WHERE code = ?", [$code]);
    if ($existing) {
        return [
            "success" => false,
            "error" => "Kode kriteria sudah digunakan.",
        ];
    }
    $db->insert(
        "INSERT INTO criteria (code, name, description) VALUES (?, ?, ?)",
        [$code, $name, $description],
    );
    return ["success" => true];
}

function dbUpdateCriteria($id, $data)
{
    $db = Database::getInstance();
    $fields = [];
    $params = [];
    if (isset($data["code"])) {
        $fields[] = "code = ?";
        $params[] = $data["code"];
    }
    if (isset($data["name"])) {
        $fields[] = "name = ?";
        $params[] = $data["name"];
    }
    if (isset($data["description"])) {
        $fields[] = "description = ?";
        $params[] = $data["description"];
    }
    if (isset($data["is_active"])) {
        $fields[] = "is_active = ?";
        $params[] = $data["is_active"];
    }
    if (empty($fields)) {
        return false;
    }
    $params[] = $id;
    return $db->execute(
        "UPDATE criteria SET " . implode(", ", $fields) . " WHERE id = ?",
        $params,
    );
}

function dbDeleteCriteria($id)
{
    $db = Database::getInstance();
    return $db->execute("DELETE FROM criteria WHERE id = ?", [$id]);
}

function dbGetCriteriaStats()
{
    $db = Database::getInstance();
    return [
        "total" => $db->getValue("SELECT COUNT(*) FROM criteria"),
        "active" => $db->getValue(
            "SELECT COUNT(*) FROM criteria WHERE is_active = TRUE",
        ),
        "inactive" => $db->getValue(
            "SELECT COUNT(*) FROM criteria WHERE is_active = FALSE",
        ),
        "used_in_analyses" => $db->getValue(
            "SELECT COUNT(DISTINCT analysis_id) FROM comparisons WHERE type = 'criteria'",
        ),
    ];
}

// ============================================================
// GLOBAL ALTERNATIVES CRUD (ADMIN)
// ============================================================

function dbGetAllGlobalAlternatives()
{
    $db = Database::getInstance();
    return $db->getRows("SELECT * FROM global_alternatives ORDER BY name ASC");
}

function dbGetActiveGlobalAlternatives()
{
    $db = Database::getInstance();
    return $db->getRows(
        "SELECT * FROM global_alternatives WHERE is_active = 1 ORDER BY name ASC",
    );
}

function dbGetGlobalAlternative($id)
{
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM global_alternatives WHERE id = ?", [$id]);
}

function dbCreateGlobalAlternative($name, $description = "")
{
    $db = Database::getInstance();
    $db->insert(
        "INSERT INTO global_alternatives (name, description) VALUES (?, ?)",
        [$name, $description],
    );
    return ["success" => true];
}

function dbUpdateGlobalAlternative($id, $data)
{
    $db = Database::getInstance();
    $fields = [];
    $params = [];
    if (isset($data["name"])) {
        $fields[] = "name = ?";
        $params[] = $data["name"];
    }
    if (isset($data["description"])) {
        $fields[] = "description = ?";
        $params[] = $data["description"];
    }
    if (isset($data["is_active"])) {
        $fields[] = "is_active = ?";
        $params[] = $data["is_active"];
    }
    if (empty($fields)) {
        return false;
    }
    $params[] = $id;
    return $db->execute(
        "UPDATE global_alternatives SET " .
            implode(", ", $fields) .
            " WHERE id = ?",
        $params,
    );
}

function dbDeleteGlobalAlternative($id)
{
    $db = Database::getInstance();
    return $db->execute("DELETE FROM global_alternatives WHERE id = ?", [$id]);
}

function dbReplaceAllGlobalAlternatives($names)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $conn->query("DELETE FROM global_alternatives");
    if (empty($names)) {
        return 0;
    }
    $stmt = $conn->prepare("INSERT INTO global_alternatives (name) VALUES (?)");
    $count = 0;
    foreach ($names as $name) {
        $name = trim($name);
        if ($name === '') {
            continue;
        }
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $count++;
    }
    $stmt->close();
    return $count;
}

function dbGetGlobalAltStats()
{
    $db = Database::getInstance();
    $total = $db->getValue("SELECT COUNT(*) FROM global_alternatives");
    $active = $db->getValue(
        "SELECT COUNT(*) FROM global_alternatives WHERE is_active = TRUE",
    );
    // Count how many times each global alternative is used in analyses
    $mostUsed = $db->getRows("
        SELECT ga.name, COUNT(a.id) as usage_count
        FROM global_alternatives ga
        LEFT JOIN alternatives a ON a.name = ga.name
        GROUP BY ga.id, ga.name
        ORDER BY usage_count DESC
        LIMIT 5
    ");
    return [
        "total" => $total,
        "active" => $active,
        "inactive" => $total - $active,
        "most_used" => $mostUsed,
    ];
}

// ============================================================
// ANALYSIS HELPERS (existing + enhanced)
// ============================================================

function dbGetUserAnalyses($userId)
{
    $db = Database::getInstance();
    $analyses = $db->getRows(
        "SELECT a.*,
         (SELECT COUNT(*) FROM alternatives WHERE analysis_id = a.id) as alt_count,
         (SELECT JSON_UNQUOTE(JSON_EXTRACT(result_data, '$.ranking')) FROM comparisons WHERE analysis_id = a.id AND type = 'results' LIMIT 1) as ranking_json
         FROM analyses a WHERE a.user_id = ? ORDER BY a.created_at DESC",
        [$userId],
    );
    foreach ($analyses as &$a) {
        if ($a["ranking_json"]) {
            $ranking = json_decode($a["ranking_json"], true);
            $a["ranking"] = $ranking;
            if (!empty($ranking) && is_array($ranking)) {
                $a["top_alternative"] = $ranking[0]["name"] ?? "-";
                $a["top_score"] = $ranking[0]["score"] ?? 0;
            }
        }
        unset($a["ranking_json"]);
    }
    return $analyses;
}

function dbGetAllAnalyses()
{
    $db = Database::getInstance();
    $analyses = $db->getRows(
        "SELECT a.*, u.username as user_name,
         (SELECT COUNT(*) FROM alternatives WHERE analysis_id = a.id) as alt_count,
         (SELECT JSON_UNQUOTE(JSON_EXTRACT(result_data, '$.ranking')) FROM comparisons WHERE analysis_id = a.id AND type = 'results' LIMIT 1) as ranking_json
         FROM analyses a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC",
    );
    foreach ($analyses as &$a) {
        if ($a["ranking_json"]) {
            $ranking = json_decode($a["ranking_json"], true);
            if (!empty($ranking) && is_array($ranking)) {
                $a["top_alternative"] = $ranking[0]["name"] ?? "-";
                $a["top_score"] = $ranking[0]["score"] ?? 0;
            }
        }
        unset($a["ranking_json"]);
    }
    return $analyses;
}

function dbGetAnalysisStats()
{
    $db = Database::getInstance();
    return [
        "total" => $db->getValue("SELECT COUNT(*) FROM analyses"),
        "completed" => $db->getValue(
            "SELECT COUNT(*) FROM analyses WHERE status = 'completed'",
        ),
        "draft" => $db->getValue(
            "SELECT COUNT(*) FROM analyses WHERE status = 'draft'",
        ),
        "total_alternatives" => $db->getValue(
            "SELECT COUNT(*) FROM alternatives",
        ),
        "recent" => $db->getRows("
            SELECT a.*, u.username
            FROM analyses a LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC LIMIT 10
        "),
        "by_month" => $db->getRows("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM analyses GROUP BY month ORDER BY month DESC LIMIT 12
        "),
    ];
}

function dbGetAnalysis($id)
{
    $db = Database::getInstance();
    $analysis = $db->getRow("SELECT * FROM analyses WHERE id = ?", [$id]);
    if (!$analysis) {
        return null;
    }
    $analysis["alternatives_list"] = $db->getRows(
        "SELECT * FROM alternatives WHERE analysis_id = ? ORDER BY id ASC",
        [$id],
    );
    $analysis["comparisons"] = $db->getRows(
        "SELECT * FROM comparisons WHERE analysis_id = ? ORDER BY id ASC",
        [$id],
    );
    return $analysis;
}

function dbSaveAnalysis($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $goal = $data["goal"] ?? DEFAULT_GOAL;
    $notes = $_POST["notes"] ?? "";
    $clientName = $_POST["client_name"] ?? "";
    $userId = $_SESSION["user_id"] ?? null;

    if ($userId) {
        $analysisId = $db->insert(
            "INSERT INTO analyses (user_id, goal, client_name, notes, status) VALUES (?, ?, ?, ?, 'completed')",
            [$userId, $goal, $clientName, $notes],
        );
    } else {
        $analysisId = $db->insert(
            "INSERT INTO analyses (goal, client_name, notes, status) VALUES (?, ?, ?, 'completed')",
            [$goal, $clientName, $notes],
        );
    }

    // Save alternatives
    if (!empty($data["alternatives"])) {
        $altStmt = $conn->prepare(
            "INSERT INTO alternatives (analysis_id, name) VALUES (?, ?)",
        );
        foreach ($data["alternatives"] as $id => $name) {
            $altName = $conn->real_escape_string($name);
            $altStmt->bind_param("is", $analysisId, $altName);
            $altStmt->execute();
        }
        $altStmt->close();
    }

    // Save pairwise criteria
    if (!empty($data["pairwise_criteria"])) {
        $criteriaIds = array_keys($data["criteria"]);
        $pairwiseData = [];
        foreach ($data["pairwise_criteria"] as $i => $row) {
            foreach ($row as $j => $val) {
                $pairwiseData[$criteriaIds[$i] . "_" . $criteriaIds[$j]] = $val;
            }
        }
        $db->insert(
            "INSERT INTO comparisons (analysis_id, type, pairwise_data) VALUES (?, 'criteria', ?)",
            [$analysisId, json_encode($pairwiseData)],
        );
    }

    // Save pairwise alternatives
    if (!empty($data["pairwise_alternatives"])) {
        $altIds = array_keys($data["alternatives"]);
        foreach ($data["pairwise_alternatives"] as $criteriaId => $matrix) {
            $pairwiseData = [];
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $val) {
                    $pairwiseData[$altIds[$i] . "_" . $altIds[$j]] = $val;
                }
            }
            $db->insert(
                "INSERT INTO comparisons (analysis_id, type, criterion_code, pairwise_data) VALUES (?, 'alternatives', ?, ?)",
                [$analysisId, $criteriaId, json_encode($pairwiseData)],
            );
        }
    }

    // Save results
    if (!empty($data["results"])) {
        $results = $data["results"];
        $criteriaPriorities = $results["criteria"] ?? [];
        $altPriorities = $results["alternatives"] ?? [];
        $globalPriorities = $results["globalPriorities"] ?? [];
        $ranking = getRankedAlternatives($data);
        $cr = $results["criteria"]["cr"] ?? 0;
        $crJson = json_encode(["criteria_cr" => $cr]);

        $db->insert(
            "INSERT INTO comparisons (analysis_id, type, pairwise_data, result_data) VALUES (?, 'results', ?, ?)",
            [
                $analysisId,
                $crJson,
                json_encode([
                    "criteria_priorities" => $criteriaPriorities,
                    "alternative_priorities" => $altPriorities,
                    "global_priorities" => $globalPriorities,
                    "ranking" => $ranking,
                    "criteria_labels" => $data["criteria_labels"] ?? [],
                    "alternative_labels" => $data["alternative_labels"] ?? [],
                ]),
            ],
        );
    }

    return $analysisId;
}

function dbDeleteAnalysis($id)
{
    $db = Database::getInstance();
    return $db->execute("DELETE FROM analyses WHERE id = ?", [$id]);
}

function dbLoadAnalysisIntoSession($id)
{
    $analysis = dbGetAnalysis($id);
    if (!$analysis) {
        return false;
    }
    resetSession();
    $_SESSION["ahp"]["goal"] = $analysis["goal"];
    $_SESSION["ahp"]["client_name"] = $analysis["client_name"];
    $_SESSION["ahp"]["notes"] = $analysis["notes"];
    $_SESSION["ahp"]["saved_analysis_id"] = $analysis["id"];

    // Reconstruct the original session alternative keys so they match the keys
    // used when the pairwise data was saved (e.g. "a1_a2"). The alternatives
    // table rows were inserted in the same order as the session keys, but the
    // DB auto-increment IDs may differ (e.g. after other analyses were saved
    // first), so DB IDs cannot be used as session keys.
    //
    // Prefer keys derived from the stored pairwise data (order of first
    // appearance) — this also preserves gaps left by deleted alternatives
    // (e.g. a1, a3). Fall back to positional keys (a1, a2, ...) when no
    // alternative pairwise data exists.
    $altKeysFromData = [];
    foreach ($analysis["comparisons"] as $comp) {
        if ($comp["type"] !== "alternatives" || empty($comp["pairwise_data"])) {
            continue;
        }
        $pairwiseData = json_decode($comp["pairwise_data"], true);
        if (!is_array($pairwiseData)) {
            continue;
        }
        foreach ($pairwiseData as $key => $val) {
            $parts = explode("_", $key);
            if (count($parts) >= 2) {
                foreach ($parts as $p) {
                    if (preg_match('/^a\d+$/', $p) && !in_array($p, $altKeysFromData)) {
                        $altKeysFromData[] = $p;
                    }
                }
            }
        }
    }

    $alts = [];
    $altLabels = [];
    $altList = $analysis["alternatives_list"];
    $nAlts = count($altList);
    $useDataKeys = count($altKeysFromData) === $nAlts;
    for ($i = 0; $i < $nAlts; $i++) {
        $id = $useDataKeys ? $altKeysFromData[$i] : ("a" . ($i + 1));
        $alts[$id] = $altList[$i]["name"];
        $altLabels[$id] = $altList[$i]["name"];
    }
    $_SESSION["ahp"]["alternatives"] = $alts;
    $_SESSION["ahp"]["alternative_labels"] = $altLabels;

    $criteriaRows = dbGetActiveCriteria();
    $criteria = [];
    $criteriaLabels = [];
    foreach ($criteriaRows as $c) {
        $id = "c" . $c["id"];
        $criteria[$id] = $c["name"];
        $criteriaLabels[$id] = $c["name"];
    }
    $_SESSION["ahp"]["criteria"] = $criteria;
    $_SESSION["ahp"]["criteria_labels"] = $criteriaLabels;

    foreach ($analysis["comparisons"] as $comp) {
        $pairwiseData = json_decode($comp["pairwise_data"], true);
        if ($comp["type"] === "criteria") {
            $criteriaIds = array_keys($criteria);
            $n = count($criteriaIds);
            $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));
            foreach ($pairwiseData as $key => $val) {
                $parts = explode("_", $key);
                if (count($parts) >= 2) {
                    $ci = array_search($parts[0], $criteriaIds);
                    $cj = array_search($parts[1], $criteriaIds);
                    if ($ci !== false && $cj !== false) {
                        $matrix[$ci][$cj] = $val;
                        $matrix[$cj][$ci] = $val > 0 ? 1 / $val : 0;
                    }
                }
            }
            $_SESSION["ahp"]["pairwise_criteria"] = $matrix;
            $_SESSION["ahp"]["results"]["criteria"] = ahpCalculate($matrix);
        }
        if ($comp["type"] === "alternatives") {
            $criterionCode = $comp["criterion_code"];
            $altIds = array_keys($alts);
            $n = count($altIds);
            $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));
            foreach ($pairwiseData as $key => $val) {
                $parts = explode("_", $key);
                if (count($parts) >= 2) {
                    $ai = array_search($parts[0], $altIds);
                    $aj = array_search($parts[1], $altIds);
                    if ($ai !== false && $aj !== false) {
                        $matrix[$ai][$aj] = $val;
                        $matrix[$aj][$ai] = $val > 0 ? 1 / $val : 0;
                    }
                }
            }
            $_SESSION["ahp"]["pairwise_alternatives"][$criterionCode] = $matrix;
            $_SESSION["ahp"]["results"]["alternatives"][
                $criterionCode
            ] = ahpCalculate($matrix)["priorities"];
        }
        if ($comp["type"] === "results") {
            $resultData = json_decode($comp["result_data"], true);
            if ($resultData) {
                $cp = $resultData["criteria_priorities"] ?? [];
                $_SESSION["ahp"]["results"]["criteria_priorities"] = is_string(
                    $cp,
                )
                    ? json_decode($cp, true) ?? []
                    : $cp;
                $_SESSION["ahp"]["results"]["global_data"] = $resultData;
            }
        }
    }

    if (!empty($_SESSION["ahp"]["results"]["criteria"])) {
        $cp = $_SESSION["ahp"]["results"]["criteria"]["priorities"];
        $ap = $_SESSION["ahp"]["results"]["alternatives"] ?? [];
        $_SESSION["ahp"]["results"][
            "globalPriorities"
        ] = calculateGlobalPriorities($cp, $ap);
        $_SESSION["ahp"]["results"]["ranked"] = getRankedAlternatives(
            $_SESSION["ahp"],
        );
    }
    return true;
}

// ============================================================
// EXPORT / REPORTING HELPERS
// ============================================================

function exportToCSV($headers, $rows)
{
    header("Content-Type: text/csv; charset=utf-8");
    header(
        'Content-Disposition: attachment; filename="export-' .
            date("Y-m-d") .
            '.csv"',
    );
    $output = fopen("php://output", "w");
    fprintf($output, chr(0xef) . chr(0xbb) . chr(0xbf)); // BOM for UTF-8
    fputcsv($output, $headers);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

function exportUsersCSV()
{
    $users = dbGetAllUsers();
    $headers = [
        "ID",
        "Username",
        "Email",
        "Role",
        "Active",
        "Last Login",
        "Created",
    ];
    $rows = [];
    foreach ($users as $u) {
        $rows[] = [
            $u["id"],
            $u["username"],
            $u["email"],
            $u["role"],
            $u["is_active"] ? "Yes" : "No",
            $u["last_login"] ?? "-",
            $u["created_at"],
        ];
    }
    exportToCSV($headers, $rows);
}

function exportCriteriaCSV()
{
    $criteria = dbGetAllCriteria();
    $headers = ["ID", "Code", "Name", "Description", "Active", "Created"];
    $rows = [];
    foreach ($criteria as $c) {
        $rows[] = [
            $c["id"],
            $c["code"],
            $c["name"],
            $c["description"] ?? "-",
            $c["is_active"] ? "Yes" : "No",
            $c["created_at"],
        ];
    }
    exportToCSV($headers, $rows);
}

function exportAlternativesCSV()
{
    $alts = dbGetAllGlobalAlternatives();
    $headers = ["ID", "Name", "Description", "Active", "Created"];
    $rows = [];
    foreach ($alts as $a) {
        $rows[] = [
            $a["id"],
            $a["name"],
            $a["description"] ?? "-",
            $a["is_active"] ? "Yes" : "No",
            $a["created_at"],
        ];
    }
    exportToCSV($headers, $rows);
}

function exportAnalysesCSV()
{
    $analyses = isSuperAdmin()
        ? dbGetAllAnalyses()
        : dbGetUserAnalyses($_SESSION["user_id"]);
    $headers = [
        "ID",
        "User",
        "Goal",
        "Client",
        "Status",
        "Alternatives",
        "Top Result",
        "Score",
        "Created",
    ];
    $rows = [];
    foreach ($analyses as $a) {
        $rows[] = [
            $a["id"],
            $a["user_name"] ?? "-",
            $a["goal"],
            $a["client_name"] ?? "-",
            $a["status"],
            $a["alt_count"] ?? 0,
            $a["top_alternative"] ?? "-",
            isset($a["top_score"])
                ? number_format($a["top_score"] * 100, 2) . "%"
                : "-",
            $a["created_at"],
        ];
    }
    exportToCSV($headers, $rows);
}

function dbIsSetupComplete()
{
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $result = $conn->query(
            "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = '" .
                DB_NAME .
                "' AND table_name = 'criteria'",
        );
        if (!$result || $result->fetch_assoc()["cnt"] == 0) {
            return false;
        }
        $count = $db->getValue("SELECT COUNT(*) FROM criteria");
        return $count >= 6;
    } catch (Exception $e) {
        return false;
    }
}

// ============================================================
// APP SETTINGS HELPERS
// ============================================================

function dbEnsureSettingsTable()
{
    static $ensured = false;
    if ($ensured) return;
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $conn->query("
            CREATE TABLE IF NOT EXISTS `settings` (
                `setting_key` VARCHAR(50) PRIMARY KEY,
                `setting_value` TEXT DEFAULT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $ensured = true;
    } catch (Exception $e) {}
}

function dbGetSetting($key, $default = '')
{
    dbEnsureSettingsTable();
    try {
        $db = Database::getInstance();
        $val = $db->getValue("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        if ($val !== null) return $val;
    } catch (Exception $e) {}

    $defaults = [
        'app_name' => APP_NAME,
        'app_institution' => APP_INSTITUTION,
        'app_logo_text' => 'A',
        'app_logo_url' => '',
        'report_signer_title' => 'Hormat Kami,',
        'report_signer_name' => 'Widya Corietania Basri, S.H., M.Kn.',
        'report_header_align' => 'center',
    ];

    return $defaults[$key] ?? $default;
}

function dbSetSetting($key, $value)
{
    dbEnsureSettingsTable();
    $db = Database::getInstance();
    $db->execute(
        "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        [$key, $value]
    );
    return true;
}

function dbGetAllSettings()
{
    dbEnsureSettingsTable();
    $defaults = [
        'app_name' => APP_NAME,
        'app_institution' => APP_INSTITUTION,
        'app_logo_text' => 'A',
        'app_logo_url' => '',
        'report_signer_title' => 'Hormat Kami,',
        'report_signer_name' => 'Widya Corietania Basri, S.H., M.Kn.',
        'report_header_align' => 'center',
    ];
    try {
        $db = Database::getInstance();
        $rows = $db->getRows("SELECT setting_key, setting_value FROM settings");
        foreach ($rows as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {}
    return $defaults;
}

