<?php
// api/kafala.php - Sponsorship Transfer (Kafala) eligibility & Ministry nationality quota enforcement
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'transfers') {
        $stmt = $pdo->query("SELECT k.*, w.full_name as worker_name, w.passport_no, w.iqama_no, b.booking_code 
                             FROM kafala_transfers k 
                             JOIN workers w ON k.worker_id = w.id 
                             JOIN bookings b ON k.booking_id = b.id 
                             ORDER BY k.id DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'check_booking_eligibility') {
        $bookingId = intval($_GET['booking_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT b.*, w.full_name as worker_name, w.nationality, p.name as package_name, p.kafala_eligible 
                              FROM bookings b 
                              JOIN workers w ON b.worker_id = w.id 
                              JOIN packages p ON b.package_id = p.id 
                              WHERE b.id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        // Check nationality quota
        $stmtQuota = $pdo->prepare("SELECT * FROM ministry_quotas WHERE nationality = ?");
        $stmtQuota->execute([$booking['nationality']]);
        $quota = $stmtQuota->fetch();

        $remainingCap = $quota ? max(0, $quota['max_kafala_transfers_allowed'] - $quota['kafala_transfers_used']) : 0;
        $isQuotaAvailable = ($remainingCap > 0);

        echo json_encode([
            'success' => true,
            'booking' => $booking,
            'nationality' => $booking['nationality'],
            'max_quota' => $quota['max_kafala_transfers_allowed'] ?? 0,
            'transfers_used' => $quota['kafala_transfers_used'] ?? 0,
            'remaining_transfers_allowed' => $remainingCap,
            'can_transfer' => ($booking['kafala_eligible'] == 1 && $isQuotaAvailable),
            'quota_blocked' => !$isQuotaAvailable
        ]);
        exit;
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'request_transfer';

    if ($action === 'request_transfer') {
        $bookingId = intval($input['booking_id']);
        $clientIdNumber = trim($input['client_id_number'] ?? '1098273645'); // Saudi National ID / Iqama
        $notes = trim($input['notes'] ?? 'Standard post 3-month package sponsorship transfer application.');

        // Verify booking
        $stmt = $pdo->prepare("SELECT b.*, w.id as worker_id, w.nationality 
                              FROM bookings b 
                              JOIN workers w ON b.worker_id = w.id 
                              WHERE b.id = ?");
        $stmt->execute([$bookingId]);
        $b = $stmt->fetch();

        if (!$b) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking record']);
            exit;
        }

        // Verify Ministry Quota for this nationality
        $stmtQuota = $pdo->prepare("SELECT * FROM ministry_quotas WHERE nationality = ?");
        $stmtQuota->execute([$b['nationality']]);
        $quota = $stmtQuota->fetch();

        if (!$quota) {
            echo json_encode(['success' => false, 'message' => 'Nationality quota record not found in Ministry database']);
            exit;
        }

        if ($quota['kafala_transfers_used'] >= $quota['max_kafala_transfers_allowed']) {
            echo json_encode([
                'success' => false,
                'quota_exceeded' => true,
                'message' => "CRITICAL ERROR: Ministry of Labor quota for {$b['nationality']} sponsorship transfers has reached its legal cap ({$quota['max_kafala_transfers_allowed']} transfers). No additional transfers can be authorized for this nationality at this time."
            ]);
            exit;
        }

        // Proceed with transfer request
        $transferFee = 2500.0; // Standard government transfer processing fee in SAR
        $stmtInsert = $pdo->prepare("INSERT INTO kafala_transfers (booking_id, worker_id, client_name, client_id_number, nationality, transfer_fee, ministry_status, notes) 
                                     VALUES (?, ?, ?, ?, ?, ?, 'quota_approved', ?)");
        $stmtInsert->execute([$bookingId, $b['worker_id'], $b['client_name'], $clientIdNumber, $b['nationality'], $transferFee, $notes]);

        // Increment quota used in ministry table
        $pdo->prepare("UPDATE ministry_quotas SET kafala_transfers_used = kafala_transfers_used + 1 WHERE nationality = ?")
            ->execute([$b['nationality']]);

        // Update booking status
        $pdo->prepare("UPDATE bookings SET kafala_status = 'transfer_approved' WHERE id = ?")->execute([$bookingId]);

        echo json_encode([
            'success' => true,
            'message' => "Sponsorship transfer for {$b['nationality']} domestic worker approved under Ministry quota. Processing reference generated.",
            'remaining_nationality_quota' => ($quota['max_kafala_transfers_allowed'] - $quota['kafala_transfers_used'] - 1)
        ]);
        exit;
    }
}
