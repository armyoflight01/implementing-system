<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/seed.php';

try {
    seedDatabase();
    echo "SUCCESS: Database schema initialized and seeded successfully!\n";
    $pdo = getDBConnection();
    $workers = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
    $packages = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
    $quotas = $pdo->query("SELECT COUNT(*) FROM ministry_quotas")->fetchColumn();
    echo "Workers: $workers, Packages: $packages, Nationality Quotas: $quotas\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
