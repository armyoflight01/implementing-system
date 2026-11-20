<?php
// api/bookings.php - Booking management, Apple Pay / Google Pay processing, and Kafala eligibility trigger
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

define('CAP_DOMESTIC_HOURLY', 123.0);
define('CAP_DOMESTIC_MONTHLY', 3230.0);

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'packages') {
        $stmt = $pdo->query("SELECT * FROM packages ORDER BY is_discounted DESC, price_sar ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'check_kafala_notifications') {
        // Find completed multi-month packages eligible for Kafala sponsorship transfer
        $stmt = $pdo->query("SELECT b.*, w.full_name as worker_name, w.nationality, w.profession, p.name as package_name 
                             FROM bookings b 
                             JOIN workers w ON b.worker_id = w.id 
                             JOIN packages p ON b.package_id = p.id 
                             WHERE p.kafala_eligible = 1 AND (b.kafala_status = 'eligible' OR b.end_date <= CURRENT_TIMESTAMP)
                             ORDER BY b.id DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    // Default: list bookings
    $status = $_GET['status'] ?? '';
    $query = "SELECT b.*, w.full_name as worker_name, w.nationality, w.profession, w.absconding_risk, p.name as package_name, p.kafala_eligible 
              FROM bookings b 
              LEFT JOIN workers w ON b.worker_id = w.id 
              LEFT JOIN packages p ON b.package_id = p.id 
              WHERE 1=1";
    $params = [];
    if ($status !== '') {
        $query .= " AND b.status = ?";
        $params[] = $status;
    }
    $query .= " ORDER BY b.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    echo json_encode(['success' => true, 'count' => count($bookings), 'data' => $bookings]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $clientName = trim($input['client_name'] ?? 'Saudi Citizen Client');
    $clientPhone = trim($input['client_phone'] ?? '+966 50 000 0000');
    $clientAddress = trim($input['client_address'] ?? 'Riyadh, Saudi Arabia');
    $workerId = intval($input['worker_id'] ?? 1);
    $packageId = isset($input['package_id']) ? intval($input['package_id']) : null;
    $serviceType = trim($input['service_type'] ?? 'monthly');
    $paymentMethod = trim($input['payment_method'] ?? 'apple_pay'); // apple_pay, google_pay, mada
    $hoursAllocated = floatval($input['hours_allocated'] ?? 0);
    $durationMonths = intval($input['duration_months'] ?? 1);
    
    // Fetch worker details
    $stmt = $pdo->prepare("SELECT * FROM workers WHERE id = ?");
    $stmt->execute([$workerId]);
    $worker = $stmt->fetch();
    if (!$worker) {
        echo json_encode(['success' => false, 'message' => 'Worker not found']);
        exit;
    }

    $totalAmount = 0.0;
    $isKafalaEligible = 0;
    $startDate = date('Y-m-d');

    if ($packageId) {
        $stmtPkg = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
        $stmtPkg->execute([$packageId]);
        $pkg = $stmtPkg->fetch();
        if ($pkg) {
            $totalAmount = floatval($pkg['price_sar']);
            $isKafalaEligible = $pkg['kafala_eligible'];
            if ($pkg['duration_type'] === 'months') {
                $endDate = date('Y-m-d', strtotime('+' . $pkg['duration_value'] . ' months'));
                $serviceType = 'monthly';
            } else {
                $endDate = $startDate;
                $hoursAllocated = floatval($pkg['duration_value']);
                $serviceType = 'hourly';
            }
        }
    } else {
        // Custom booking calculation
        if ($serviceType === 'hourly') {
            $rate = $worker['hourly_rate'];
            // Price cap validation for domestic workers
            if ($worker['profession'] === 'Domestic Worker' && $rate > CAP_DOMESTIC_HOURLY) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Hourly rate violates Saudi Ministry price cap (Max 123 SAR/hr).'
                ]);
                exit;
            }
            $totalAmount = $rate * ($hoursAllocated > 0 ? $hoursAllocated : 4);
            $endDate = $startDate;
        } else {
            $monthlyRate = $worker['monthly_rate'];
            if ($worker['profession'] === 'Domestic Worker' && $monthlyRate > CAP_DOMESTIC_MONTHLY) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Monthly rate violates Saudi Ministry price cap (Max 3,230 SAR/month).'
                ]);
                exit;
            }
            $totalAmount = $monthlyRate * $durationMonths;
            $endDate = date('Y-m-d', strtotime('+' . $durationMonths . ' months'));
            if ($durationMonths >= 3) {
                $isKafalaEligible = 1;
            }
        }
    }

    // Generate unique Saudi booking code
    $bookingCode = 'BK-SA-' . rand(10000, 99999);
    
    // Simulate Apple Pay / Google Pay transaction token
    $paymentRef = ($paymentMethod === 'apple_pay') 
        ? 'APL-' . strtoupper(bin2hex(random_bytes(6))) . '-KSA'
        : (($paymentMethod === 'google_pay') 
            ? 'GPAY-' . strtoupper(bin2hex(random_bytes(6))) . '-SA'
            : 'MADA-' . strtoupper(bin2hex(random_bytes(6))));

    // Insert booking
    $stmtInsert = $pdo->prepare("INSERT INTO bookings (booking_code, client_name, client_phone, client_address, worker_id, package_id, service_type, hours_allocated, hours_consumed, start_date, end_date, total_amount, payment_status, payment_method, payment_ref, kafala_notified, kafala_status, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, 'paid', ?, ?, 0, ?, 'active')");
    $kafalaInitialStatus = ($isKafalaEligible == 1) ? 'pending_duration' : 'none';
    $stmtInsert->execute([$bookingCode, $clientName, $clientPhone, $clientAddress, $workerId, $packageId, $serviceType, $hoursAllocated, $startDate, $endDate, $totalAmount, $paymentMethod, $paymentRef, $kafalaInitialStatus]);

    $bookingId = $pdo->lastInsertId();

    // Update worker status
    $pdo->prepare("UPDATE workers SET status = 'in_service' WHERE id = ?")->execute([$workerId]);

    // Create an initial time log if hourly
    if ($serviceType === 'hourly') {
        $pdo->prepare("INSERT INTO time_logs (booking_id, worker_id, check_in_time, hours_worked, location_lat, location_lng, status) 
                       VALUES (?, ?, CURRENT_TIMESTAMP, 0, 24.7136, 46.6753, 'active')")
            ->execute([$bookingId, $workerId]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully via ' . strtoupper(str_replace('_', ' ', $paymentMethod)),
        'booking_id' => $bookingId,
        'booking_code' => $bookingCode,
        'total_amount' => $totalAmount,
        'payment_ref' => $paymentRef,
        'kafala_eligible' => $isKafalaEligible,
        'kafala_notice' => $isKafalaEligible 
            ? 'Package includes eligibility for Sponsorship Transfer (Kafala) upon expiration, subject to Ministry nationality caps.' 
            : null
    ]);
    exit;
}
