<?php
// api/time-tracker.php - Real-time tracking of service hours, check-in/out, GPS and duration
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'live_active') {
        // Retrieve all currently active shifts in real-time
        $stmt = $pdo->query("SELECT t.*, w.full_name as worker_name, w.nationality, w.profession, w.photo, 
                                    b.booking_code, b.client_name, b.client_address, b.service_type, b.hours_allocated
                             FROM time_logs t
                             JOIN workers w ON t.worker_id = w.id
                             JOIN bookings b ON t.booking_id = b.id
                             WHERE t.status = 'active'
                             ORDER BY t.check_in_time DESC");
        $activeLogs = $stmt->fetchAll();

        // Calculate live elapsed seconds / minutes / hours
        $now = time();
        foreach ($activeLogs as &$log) {
            $checkInTimestamp = strtotime($log['check_in_time']);
            $elapsedSeconds = max(0, $now - $checkInTimestamp);
            $log['elapsed_hours'] = round($elapsedSeconds / 3600, 2);
            $log['elapsed_formatted'] = gmdate("H:i:s", $elapsedSeconds);
            $allocated = floatval($log['hours_allocated']);
            $log['remaining_hours'] = ($allocated > 0) ? max(0, round($allocated - $log['elapsed_hours'], 2)) : 'N/A (Monthly)';
        }

        echo json_encode(['success' => true, 'count' => count($activeLogs), 'data' => $activeLogs]);
        exit;
    }

    // Default: return recent time logs
    $workerId = $_GET['worker_id'] ?? null;
    $bookingId = $_GET['booking_id'] ?? null;

    $query = "SELECT t.*, w.full_name as worker_name, w.profession, b.booking_code, b.client_name 
              FROM time_logs t 
              JOIN workers w ON t.worker_id = w.id 
              JOIN bookings b ON t.booking_id = b.id 
              WHERE 1=1";
    $params = [];
    if ($workerId) {
        $query .= " AND t.worker_id = ?";
        $params[] = $workerId;
    }
    if ($bookingId) {
        $query .= " AND t.booking_id = ?";
        $params[] = $bookingId;
    }
    $query .= " ORDER BY t.id DESC LIMIT 50";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'check_in';

    if ($action === 'check_in') {
        $bookingId = intval($input['booking_id']);
        $workerId = intval($input['worker_id']);
        $lat = floatval($input['lat'] ?? 24.7136);
        $lng = floatval($input['lng'] ?? 46.6753);
        $notes = trim($input['notes'] ?? 'Worker checked in at client residence.');

        $stmt = $pdo->prepare("INSERT INTO time_logs (booking_id, worker_id, check_in_time, hours_worked, location_lat, location_lng, status, notes) 
                               VALUES (?, ?, CURRENT_TIMESTAMP, 0, ?, ?, 'active', ?)");
        $stmt->execute([$bookingId, $workerId, $lat, $lng, $notes]);

        $pdo->prepare("UPDATE workers SET status = 'in_service' WHERE id = ?")->execute([$workerId]);

        echo json_encode([
            'success' => true,
            'message' => 'Check-in recorded successfully. Service duration tracking is now active.',
            'time_log_id' => $pdo->lastInsertId()
        ]);
        exit;
    }

    if ($action === 'check_out') {
        $timeLogId = intval($input['time_log_id']);
        $clientSig = trim($input['client_signature'] ?? 'CONFIRMED-BY-CLIENT');
        $notes = trim($input['notes'] ?? 'Service session completed and verified.');

        $stmt = $pdo->prepare("SELECT * FROM time_logs WHERE id = ?");
        $stmt->execute([$timeLogId]);
        $log = $stmt->fetch();

        if (!$log) {
            echo json_encode(['success' => false, 'message' => 'Log entry not found']);
            exit;
        }

        $checkInTimestamp = strtotime($log['check_in_time']);
        $checkOutTimestamp = time();
        $hoursWorked = round(($checkOutTimestamp - $checkInTimestamp) / 3600, 2);
        if ($hoursWorked < 0.1) {
            $hoursWorked = 0.5; // minimum test interval
        }

        $checkOutTimeStr = date('Y-m-d H:i:s', $checkOutTimestamp);

        $stmtUpdate = $pdo->prepare("UPDATE time_logs 
                                     SET check_out_time = ?, hours_worked = ?, client_signature_token = ?, notes = ?, status = 'completed' 
                                     WHERE id = ?");
        $stmtUpdate->execute([$checkOutTimeStr, $hoursWorked, $clientSig, $notes, $timeLogId]);

        // Update booking consumed hours
        $pdo->prepare("UPDATE bookings SET hours_consumed = hours_consumed + ? WHERE id = ?")
            ->execute([$hoursWorked, $log['booking_id']]);

        // Update worker status back to in_transit or in_housing
        $pdo->prepare("UPDATE workers SET status = 'in_transit' WHERE id = ?")->execute([$log['worker_id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Service session checked out. Total duration: ' . $hoursWorked . ' hours. Transport bus requested.',
            'hours_worked' => $hoursWorked
        ]);
        exit;
    }
}
