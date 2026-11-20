<?php
// api/housing.php - Accommodation, supervisor oversight, and Indonesian runaway prevention system
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'overview';

    if ($action === 'units') {
        $stmt = $pdo->query("SELECT h.*, 
                             (SELECT COUNT(*) FROM workers WHERE housing_unit_id = h.id) as actual_occupants,
                             (SELECT COUNT(*) FROM workers WHERE housing_unit_id = h.id AND absconding_risk = 'high') as high_risk_occupants
                             FROM housing_units h");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'security_logs') {
        $stmt = $pdo->query("SELECT s.*, w.full_name as worker_name, w.nationality, w.profession, w.absconding_risk 
                             FROM security_logs s 
                             JOIN workers w ON s.worker_id = w.id 
                             ORDER BY s.id DESC LIMIT 50");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'runaway_risk_matrix') {
        // High alert list, especially Indonesian workers
        $stmt = $pdo->query("SELECT w.*, h.building_name, h.room_number, h.supervisor_name, h.supervisor_phone 
                             FROM workers w 
                             LEFT JOIN housing_units h ON w.housing_unit_id = h.id 
                             ORDER BY CASE WHEN w.nationality = 'Indonesian' THEN 1 ELSE 2 END, w.absconding_risk DESC");
        $workers = $stmt->fetchAll();

        $stats = [
            'total_workers' => count($workers),
            'indonesian_total' => 0,
            'high_risk_count' => 0,
            'medium_risk_count' => 0,
            'low_risk_count' => 0,
            'absconded_count' => 0
        ];

        foreach ($workers as $w) {
            if ($w['nationality'] === 'Indonesian') {
                $stats['indonesian_total']++;
            }
            if ($w['absconding_risk'] === 'high') {
                $stats['high_risk_count']++;
            } elseif ($w['absconding_risk'] === 'medium') {
                $stats['medium_risk_count']++;
            } else {
                $stats['low_risk_count']++;
            }
            if ($w['status'] === 'absconded') {
                $stats['absconded_count']++;
            }
        }

        echo json_encode(['success' => true, 'stats' => $stats, 'data' => $workers]);
        exit;
    }

    // Default overview
    $units = $pdo->query("SELECT COUNT(*) FROM housing_units")->fetchColumn();
    $logs = $pdo->query("SELECT COUNT(*) FROM security_logs")->fetchColumn();
    $highRisk = $pdo->query("SELECT COUNT(*) FROM workers WHERE absconding_risk = 'high'")->fetchColumn();

    echo json_encode([
        'success' => true,
        'summary' => [
            'housing_units_count' => $units,
            'security_events_count' => $logs,
            'high_risk_absconding_workers' => $highRisk,
            'curfew_status' => 'Active 24/7 Gate Surveillance'
        ]
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'log_security_event';

    if ($action === 'log_security_event') {
        $workerId = intval($input['worker_id']);
        $eventType = trim($input['event_type'] ?? 'curfew_check'); // curfew_check, unauthorized_exit_prevented, gate_pass_issued, absconded_reported
        $supervisor = trim($input['supervisor_name'] ?? 'Supervisor On Duty');
        $notes = trim($input['notes'] ?? '');
        $reportedMinistry = intval($input['reported_to_ministry'] ?? 0);

        if ($eventType === 'absconded_reported') {
            $reportedMinistry = 1;
            // Update worker status to absconded
            $pdo->prepare("UPDATE workers SET status = 'absconded' WHERE id = ?")->execute([$workerId]);
        }

        $stmt = $pdo->prepare("INSERT INTO security_logs (worker_id, event_type, logged_at, supervisor_name, notes, reported_to_ministry) 
                               VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?)");
        $stmt->execute([$workerId, $eventType, $supervisor, $notes, $reportedMinistry]);

        echo json_encode([
            'success' => true,
            'message' => 'Housing security event logged successfully.',
            'event_id' => $pdo->lastInsertId()
        ]);
        exit;
    }
}
