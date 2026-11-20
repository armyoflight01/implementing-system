<?php
// api/workers.php - Worker management, pricing cap check & risk profiling
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Price Cap Constants as defined by Saudi Ministry Regulations
define('CAP_DOMESTIC_HOURLY', 123.0);
define('CAP_DOMESTIC_MONTHLY', 3230.0);

if ($method === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($id) {
        $stmt = $pdo->prepare("SELECT w.*, h.building_name, h.room_number, h.supervisor_name, h.supervisor_phone 
                              FROM workers w 
                              LEFT JOIN housing_units h ON w.housing_unit_id = h.id 
                              WHERE w.id = ?");
        $stmt->execute([$id]);
        $worker = $stmt->fetch();
        
        if (!$worker) {
            echo json_encode(['success' => false, 'message' => 'Worker not found']);
            exit;
        }

        // Price cap evaluation
        $isDomestic = ($worker['profession'] === 'Domestic Worker');
        $worker['is_price_capped'] = $isDomestic;
        $worker['hourly_cap_exceeded'] = ($isDomestic && $worker['hourly_rate'] > CAP_DOMESTIC_HOURLY);
        $worker['monthly_cap_exceeded'] = ($isDomestic && $worker['monthly_rate'] > CAP_DOMESTIC_MONTHLY);
        $worker['price_cap_info'] = $isDomestic 
            ? 'Regulated by Ministry: Max ' . CAP_DOMESTIC_HOURLY . ' SAR/hr, Max ' . CAP_DOMESTIC_MONTHLY . ' SAR/mo'
            : 'Unregulated Specialized Profession: No price cap applied under Ministry rules';

        echo json_encode(['success' => true, 'data' => $worker]);
        exit;
    }

    // Filter list
    $profession = isset($_GET['profession']) ? trim($_GET['profession']) : '';
    $contract_type = isset($_GET['contract_type']) ? trim($_GET['contract_type']) : '';
    $nationality = isset($_GET['nationality']) ? trim($_GET['nationality']) : '';
    $risk = isset($_GET['risk']) ? trim($_GET['risk']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    $query = "SELECT w.*, h.building_name, h.room_number, h.supervisor_name 
              FROM workers w 
              LEFT JOIN housing_units h ON w.housing_unit_id = h.id 
              WHERE 1=1";
    $params = [];

    if ($profession !== '') {
        $query .= " AND w.profession = ?";
        $params[] = $profession;
    }
    if ($contract_type !== '') {
        $query .= " AND (w.contract_type = ? OR w.contract_type = 'both')";
        $params[] = $contract_type;
    }
    if ($nationality !== '') {
        $query .= " AND w.nationality = ?";
        $params[] = $nationality;
    }
    if ($risk !== '') {
        $query .= " AND w.absconding_risk = ?";
        $params[] = $risk;
    }
    if ($status !== '') {
        $query .= " AND w.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY w.id ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $workers = $stmt->fetchAll();

    foreach ($workers as &$w) {
        $isDomestic = ($w['profession'] === 'Domestic Worker');
        $w['is_price_capped'] = $isDomestic;
        $w['max_hourly_cap'] = $isDomestic ? CAP_DOMESTIC_HOURLY : null;
        $w['max_monthly_cap'] = $isDomestic ? CAP_DOMESTIC_MONTHLY : null;
    }

    echo json_encode(['success' => true, 'count' => count($workers), 'data' => $workers]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $fullName = trim($input['full_name'] ?? '');
    $nationality = trim($input['nationality'] ?? 'Indonesian');
    $profession = trim($input['profession'] ?? 'Domestic Worker');
    $contractType = trim($input['contract_type'] ?? 'hourly');
    $hourlyRate = floatval($input['hourly_rate'] ?? 95.0);
    $monthlyRate = floatval($input['monthly_rate'] ?? 3000.0);
    $passportNo = trim($input['passport_no'] ?? '');
    $iqamaNo = trim($input['iqama_no'] ?? '');
    $housingUnitId = intval($input['housing_unit_id'] ?? 1);
    $visaType = trim($input['visa_type'] ?? ($contractType === 'hourly' ? 'hourly' : 'monthly'));

    // Automatically flag Indonesian workers as High Absconding Risk as required by business guidelines
    $abscondingRisk = ($nationality === 'Indonesian') ? 'high' : trim($input['absconding_risk'] ?? 'low');

    // Validation of Ministry Price Caps
    if ($profession === 'Domestic Worker') {
        if ($hourlyRate > CAP_DOMESTIC_HOURLY) {
            echo json_encode([
                'success' => false, 
                'message' => 'Ministry Price Cap Violation: Domestic worker hourly rate cannot exceed ' . CAP_DOMESTIC_HOURLY . ' SAR/hr.'
            ]);
            exit;
        }
        if ($monthlyRate > CAP_DOMESTIC_MONTHLY) {
            echo json_encode([
                'success' => false, 
                'message' => 'Ministry Price Cap Violation: Domestic worker monthly rate cannot exceed ' . CAP_DOMESTIC_MONTHLY . ' SAR/month.'
            ]);
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO workers (full_name, nationality, profession, contract_type, hourly_rate, monthly_rate, passport_no, iqama_no, housing_unit_id, absconding_risk, status, photo, visa_type) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_housing', 'worker-default.png', ?)");
    $stmt->execute([$fullName, $nationality, $profession, $contractType, $hourlyRate, $monthlyRate, $passportNo, $iqamaNo, $housingUnitId, $abscondingRisk, $visaType]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true, 
        'message' => 'Worker successfully registered into company system with assigned housing supervisor.',
        'worker_id' => $newId
    ]);
    exit;
}
