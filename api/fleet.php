<?php
// api/fleet.php - Bus and vehicle transportation fleet management for hourly & monthly workers
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'all';

    if ($action === 'vehicles') {
        $stmt = $pdo->query("SELECT * FROM fleet ORDER BY id ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'trips') {
        $stmt = $pdo->query("SELECT t.*, f.vehicle_type, f.plate_number, f.driver_name, f.driver_phone 
                             FROM dispatch_trips t 
                             JOIN fleet f ON t.vehicle_id = f.id 
                             ORDER BY t.id DESC LIMIT 50");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    // Default overview
    $vehicles = $pdo->query("SELECT * FROM fleet")->fetchAll();
    $activeTrips = $pdo->query("SELECT t.*, f.plate_number, f.driver_name 
                                FROM dispatch_trips t 
                                JOIN fleet f ON t.vehicle_id = f.id 
                                WHERE t.status != 'completed'")->fetchAll();

    echo json_encode([
        'success' => true,
        'vehicles' => $vehicles,
        'active_trips' => $activeTrips,
        'total_fleet' => count($vehicles)
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'create_trip';

    if ($action === 'create_trip') {
        $vehicleId = intval($input['vehicle_id']);
        $tripType = trim($input['trip_type'] ?? 'Hourly Dropoff Route');
        $destArea = trim($input['destination_area'] ?? 'Riyadh Metropolitan Area');
        $workerCount = intval($input['worker_count'] ?? 1);
        $depTime = $input['departure_time'] ?? date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO dispatch_trips (vehicle_id, trip_type, departure_time, destination_area, worker_count, status) 
                               VALUES (?, ?, ?, ?, ?, 'scheduled')");
        $stmt->execute([$vehicleId, $tripType, $depTime, $destArea, $workerCount]);

        $pdo->prepare("UPDATE fleet SET current_status = 'on_trip' WHERE id = ?")->execute([$vehicleId]);

        echo json_encode([
            'success' => true,
            'message' => 'Transportation dispatch trip successfully scheduled and vehicle assigned.',
            'trip_id' => $pdo->lastInsertId()
        ]);
        exit;
    }

    if ($action === 'complete_trip') {
        $tripId = intval($input['trip_id']);
        $trip = $pdo->prepare("SELECT * FROM dispatch_trips WHERE id = ?");
        $trip->execute([$tripId]);
        $t = $trip->fetch();

        if ($t) {
            $pdo->prepare("UPDATE dispatch_trips SET status = 'completed', arrival_time = CURRENT_TIMESTAMP WHERE id = ?")->execute([$tripId]);
            $pdo->prepare("UPDATE fleet SET current_status = 'available' WHERE id = ?")->execute([$t['vehicle_id']]);
        }

        echo json_encode(['success' => true, 'message' => 'Dispatch trip marked completed and bus returned to housing terminal.']);
        exit;
    }
}
