<?php
/**
 * AHP Calculator — Database Setup
 * Jalankan ONCE untuk inisialisasi database dan seed data
 *
 * Cara jalankan: php setup.php
 * Atau akses dari browser: http://localhost:8000/setup.php
 */

require_once 'config.php';
require_once 'database.php';

echo "<!DOCTYPE html><html><head><title>AHP Setup</title>";
echo "<style>
    body { font-family: system-ui, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
    .success { background: #d1fae5; border: 1px solid #6ee7b7; padding: 12px 16px; border-radius: 4px; margin: 8px 0; }
    .error { background: #fee2e2; border: 1px solid #fca5a5; padding: 12px 16px; border-radius: 4px; margin: 8px 0; }
    .info { background: #dbeafe; border: 1px solid #93c5fd; padding: 12px 16px; border-radius: 4px; margin: 8px 0; }
    .warning { background: #fef3c7; border: 1px solid #fcd34d; padding: 12px 16px; border-radius: 4px; margin: 8px 0; }
    h1 { color: #1f2937; font-family: Georgia, serif; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
</style></head><body>";
echo "<h1>🔧 AHP Calculator — Database Setup</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // 1. Create database
    echo "<div class='info'>⚡ Creating database <code>" . DB_NAME . "</code>...</div>";
    $db->createDatabase();
    echo "<div class='success'>✅ Database <code>" . DB_NAME . "</code> created/ready.</div>";

    // 2. Create tables
    echo "<div class='info'>⚡ Creating tables...</div>";

    $conn->query("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) UNIQUE NOT NULL,
            `email` VARCHAR(100) UNIQUE NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` ENUM('super_admin', 'user') NOT NULL DEFAULT 'user',
            `is_active` BOOLEAN DEFAULT TRUE,
            `last_login` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<div class='success'>✅ Table <code>users</code> ready (with role support).</div>";

    $conn->query("
        CREATE TABLE IF NOT EXISTS `criteria` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(10) UNIQUE NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `is_active` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<div class='success'>✅ Table <code>criteria</code> ready.</div>";

    $conn->query("
        CREATE TABLE IF NOT EXISTS `global_alternatives` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `is_active` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    // Clean duplicates from global_alternatives
    $conn->query("DELETE t1 FROM global_alternatives t1 INNER JOIN global_alternatives t2 WHERE t1.id > t2.id AND t1.name = t2.name");
    // Add UNIQUE constraint on name to prevent future duplicates
    try {
        $conn->query("ALTER TABLE `global_alternatives` ADD UNIQUE INDEX `uk_name` (`name`)");
    } catch (Exception $e) {
        // Index may already exist, ignore
    }
    echo "<div class='success'>✅ Table <code>global_alternatives</code> ready (unique + deduped).</div>";

    $conn->query("
        CREATE TABLE IF NOT EXISTS `analyses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT DEFAULT NULL,
            `goal` VARCHAR(255) NOT NULL DEFAULT 'Prioritas Pengurusan Akta',
            `client_name` VARCHAR(100) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `status` ENUM('draft', 'completed') DEFAULT 'draft',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<div class='success'>✅ Table <code>analyses</code> ready.</div>";

    $conn->query("
        CREATE TABLE IF NOT EXISTS `alternatives` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `analysis_id` INT NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`analysis_id`) REFERENCES `analyses`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<div class='success'>✅ Table <code>alternatives</code> (per-analysis) ready.</div>";

    $conn->query("
        CREATE TABLE IF NOT EXISTS `comparisons` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `analysis_id` INT NOT NULL,
            `type` ENUM('criteria', 'alternatives', 'results') NOT NULL,
            `criterion_code` VARCHAR(10) DEFAULT NULL,
            `pairwise_data` JSON NOT NULL,
            `result_data` JSON DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`analysis_id`) REFERENCES `analyses`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<div class='success'>✅ Table <code>comparisons</code> ready.</div>";

    // 2b. Legacy migration: add columns to existing tables if needed
    echo "<div class='info'>⚡ Checking legacy schema migration...</div>";
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN `role` ENUM('super_admin', 'user') NOT NULL DEFAULT 'user' AFTER `password_hash`");
        $conn->query("ALTER TABLE users ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE AFTER `role`");
        $conn->query("ALTER TABLE users ADD COLUMN `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `is_active`");
        $conn->query("ALTER TABLE users ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
        echo "<div class='success'>✅ Legacy migration: added role/is_active/last_login/updated_at to users table.</div>";
    } else {
        echo "<div class='success'>✅ Users table already up-to-date.</div>";
    }

    // 3. Seed criteria
    echo "<div class='info'>⚡ Seeding criteria data...</div>";

    $criteriaData = [
        ['C01', 'Tingkat Urgensi', 'Prioritas berdasarkan tingkat urgensi/kedaruratan pengurusan akta'],
        ['C02', 'Kelengkapan Dokumen', 'Kelengkapan persyaratan dokumen yang dibutuhkan'],
        ['C03', 'Jenis Akta', 'Jenis akta yang akan diurus (Akta Kelahiran, Akta Kematian, Akta Perkawinan, dll)'],
        ['C04', 'Nilai Transaksi', 'Nilai transaksi atau nilai ekonomi yang terkait dengan akta'],
        ['C05', 'Status Klien', 'Status hubungan klien (VIP, Reguler, Baru, dll)'],
        ['C06', 'Waktu Pengajuan', 'Waktu atau tanggal pengajuan permohonan akta'],
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO `criteria` (`code`, `name`, `description`) VALUES (?, ?, ?)");
    foreach ($criteriaData as $c) {
        $stmt->bind_param("sss", $c[0], $c[1], $c[2]);
        $stmt->execute();
    }
    $stmt->close();
    echo "<div class='success'>✅ 6 Kriteria seeded.</div>";

    // 4. Seed global alternatives (data customer/alternatif pengurusan akta)
    echo "<div class='info'>⚡ Seeding global alternatives...</div>";

    $altData = [
        'PT Jaya Abadi',
        'PT Pelindung Citra',
        'PT Sumber Waras',
        'PT Global Sistema',
        'PT Agung Properti',
        'Putri Sinaga',
        'Dito Pramono',
        'Venny Pangestu',
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO `global_alternatives` (`name`, `description`) VALUES (?, ?)");
    $desc = 'Customer/alternatif prioritas pengurusan akta';
    foreach ($altData as $name) {
        $stmt->bind_param("ss", $name, $desc);
        $stmt->execute();
    }
    $stmt->close();
    echo "<div class='success'>✅ " . count($altData) . " Global alternatives seeded.</div>";

    // 5. Create super admin if not exists
    echo "<div class='info'>⚡ Creating super admin account...</div>";

    $superAdminUsername = 'admin';
    $superAdminEmail = 'admin@ahp-calculator.com';
    $superAdminPassword = 'admin123'; // Change this in production!
    $passwordHash = password_hash($superAdminPassword, PASSWORD_DEFAULT);

    $check = $db->getRow("SELECT id FROM users WHERE username = ? OR email = ?", [$superAdminUsername, $superAdminEmail]);
    if (!$check) {
        $db->insert(
            "INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, 'super_admin', TRUE)",
            [$superAdminUsername, $superAdminEmail, $passwordHash]
        );
        echo "<div class='success'>✅ Super admin created: <code>$superAdminUsername</code> / <code>$superAdminPassword</code></div>";
    } else {
        echo "<div class='warning'>⚠️ Super admin already exists.</div>";
    }



    echo "<hr style='margin: 24px 0; border: none; border-top: 2px solid #e5e7eb;'>";
    echo "<div class='success' style='font-size: 1.1em;'>🚀 Setup selesai! Aplikasi siap digunakan.</div>";
    echo "<p style='text-align: center; margin-top: 20px;'>";
    echo "<a href='/' style='display: inline-block; background: #1a5c5a; color: white; padding: 12px 24px; text-decoration: none; font-weight: 600;'>Ke Aplikasi</a>";
    echo "</p>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>
</body>
</html>
