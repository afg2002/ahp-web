<?php
require_once 'config.php';
require_once 'database.php';
require_once 'functions.php';
require_once 'db_helpers.php';

initSession();

echo "<!DOCTYPE html><html><head><title>Admin Check</title>";
echo "<style>body{font-family:system-ui,sans-serif;max-width:700px;margin:40px auto;padding:20px;}
pre{background:#f5f5f4;padding:12px;border:1px solid #e7e5e4;font-size:13px;}
.success{color:#1a5c5a}.error{color:#be123c}.info{color:#b45309}
table{border-collapse:collapse;width:100%}td,th{border:1px solid #e7e5e4;padding:8px 12px;text-align:left;font-size:13px}
th{background:#f5f5f4}
</style></head><body>";
echo "<h1>🔍 Admin Check</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check users table columns
    echo "<h2>1. Struktur Tabel Users</h2>";
    $cols = $conn->query("SHOW COLUMNS FROM users");
    echo "<table><tr><th>Field</th><th>Type</th></tr>";
    $hasRole = false;
    while ($col = $cols->fetch_assoc()) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
        if ($col['Field'] === 'role') $hasRole = true;
    }
    echo "</table>";
    echo $hasRole ? "<p class='success'>✅ Kolom 'role' ada</p>" : "<p class='error'>❌ Kolom 'role' TIDAK ada!</p>";

    // Check all users
    echo "<h2>2. Data Users</h2>";
    $users = $conn->query("SELECT id, username, email, role, is_active FROM users");
    echo "<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th></tr>";
    while ($u = $users->fetch_assoc()) {
        $role = $u['role'] ?? '(NULL)';
        echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['email']}</td><td>$role</td><td>{$u['is_active']}</td></tr>";
    }
    echo "</table>";

    // Check session
    echo "<h2>3. Session Saat Ini</h2>";
    echo "<pre>";
    echo "user_id: " . ($_SESSION['user_id'] ?? 'TIDAK DISET') . "\n";
    echo "username: " . ($_SESSION['username'] ?? 'TIDAK DISET') . "\n";
    echo "user_role: " . ($_SESSION['user_role'] ?? 'TIDAK DISET') . "\n";
    echo "isLoggedIn(): " . (isLoggedIn() ? 'true' : 'false') . "\n";
    echo "isSuperAdmin(): " . (isSuperAdmin() ? 'true' : 'false') . "\n";
    echo "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
