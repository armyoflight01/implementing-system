<?php
// api/hr.php - Recruitment management for Marketing (2) & Customer Service (2) roles via Maharah platform
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM hr_recruitment ORDER BY id ASC");
    $candidates = $stmt->fetchAll();

    // Summary count by role & hiring status
    $marketingCount = 0;
    $marketingHired = 0;
    $csCount = 0;
    $csHired = 0;

    foreach ($candidates as $c) {
        if ($c['role'] === 'Marketing Specialist') {
            $marketingCount++;
            if (in_array($c['interview_status'], ['Offered', 'Hired'])) {
                $marketingHired++;
            }
        } elseif ($c['role'] === 'Customer Service Representative') {
            $csCount++;
            if (in_array($c['interview_status'], ['Offered', 'Hired'])) {
                $csHired++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'summary' => [
            'marketing_target' => 2,
            'marketing_filled_or_offered' => $marketingHired,
            'customer_service_target' => 2,
            'customer_service_filled_or_offered' => $csHired,
            'benchmark_salary' => '7,000 SAR',
            'preferred_gender' => 'Female',
            'recruitment_platform' => 'Maharah Platform (منصة مهارة)'
        ],
        'candidates' => $candidates
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'add_candidate';

    if ($action === 'add_candidate') {
        $candidateName = trim($input['candidate_name'] ?? '');
        $role = trim($input['role'] ?? 'Marketing Specialist');
        $gender = trim($input['gender'] ?? 'Female');
        $expectedSalary = floatval($input['expected_salary'] ?? 7000.0);
        $source = trim($input['platform_source'] ?? 'Maharah');
        $status = trim($input['interview_status'] ?? 'Screened');
        $qualifications = trim($input['qualifications'] ?? '');
        $notes = trim($input['notes'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO hr_recruitment (candidate_name, role, gender, expected_salary, platform_source, interview_status, qualifications, notes) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$candidateName, $role, $gender, $expectedSalary, $source, $status, $qualifications, $notes]);

        echo json_encode(['success' => true, 'message' => 'Candidate profile added from ' . $source]);
        exit;
    }

    if ($action === 'update_status') {
        $id = intval($input['id']);
        $newStatus = trim($input['interview_status']);
        $notes = trim($input['notes'] ?? '');

        $stmt = $pdo->prepare("UPDATE hr_recruitment SET interview_status = ?, notes = CASE WHEN ? != '' THEN ? ELSE notes END WHERE id = ?");
        $stmt->execute([$newStatus, $notes, $notes, $id]);

        echo json_encode(['success' => true, 'message' => 'Candidate recruitment status updated to ' . $newStatus]);
        exit;
    }
}
