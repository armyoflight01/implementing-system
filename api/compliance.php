<?php
// api/compliance.php - Real-time Ministry of Human Resources & Social Development (HRSD) compliance audit
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// 1. Calculate Visa Allocations (10% Hourly Domestic Rule)
$quotas = $pdo->query("SELECT * FROM ministry_quotas ORDER BY total_visas DESC")->fetchAll();

$totalCompanyVisas = 0;
$totalHourlyVisas = 0;
$totalMonthlyVisas = 0;

foreach ($quotas as $q) {
    $totalCompanyVisas += intval($q['total_visas']);
    $totalHourlyVisas += intval($q['hourly_visas']);
    $totalMonthlyVisas += intval($q['monthly_visas']);
}

$hourlyVisaPercentage = ($totalCompanyVisas > 0) ? round(($totalHourlyVisas / $totalCompanyVisas) * 100, 2) : 0;
$is10PercentRuleMet = ($hourlyVisaPercentage >= 10.0);

// 2. Nationality Diversity Audit (Cannot rely on single nationality)
$nationalityBreakdown = [];
$diversityViolations = [];
foreach ($quotas as $q) {
    $percent = ($totalCompanyVisas > 0) ? round(($q['total_visas'] / $totalCompanyVisas) * 100, 2) : 0;
    $isExceeded = ($percent > $q['max_diversity_percent']);
    
    $nationalityBreakdown[] = [
        'id' => $q['id'],
        'nationality' => $q['nationality'],
        'total_visas' => $q['total_visas'],
        'hourly_visas' => $q['hourly_visas'],
        'monthly_visas' => $q['monthly_visas'],
        'percentage_of_total' => $percent,
        'max_allowed_percent' => $q['max_diversity_percent'],
        'is_compliant' => !$isExceeded,
        'max_kafala_transfers' => $q['max_kafala_transfers_allowed'],
        'kafala_transfers_used' => $q['kafala_transfers_used'],
        'kafala_transfers_remaining' => max(0, $q['max_kafala_transfers_allowed'] - $q['kafala_transfers_used']),
        'kafala_cap_reached' => ($q['kafala_transfers_used'] >= $q['max_kafala_transfers_allowed'])
    ];

    if ($isExceeded) {
        $diversityViolations[] = $q['nationality'] . " holds {$percent}% of total visas (Max allowed: {$q['max_diversity_percent']}%).";
    }
}

// 3. Price Cap Audit
$workers = $pdo->query("SELECT * FROM workers")->fetchAll();
$priceCapViolations = [];
foreach ($workers as $w) {
    if ($w['profession'] === 'Domestic Worker') {
        if ($w['hourly_rate'] > 123.0) {
            $priceCapViolations[] = "Worker #{$w['id']} ({$w['full_name']}) hourly rate {$w['hourly_rate']} SAR exceeds 123 SAR cap.";
        }
        if ($w['monthly_rate'] > 3230.0) {
            $priceCapViolations[] = "Worker #{$w['id']} ({$w['full_name']}) monthly rate {$w['monthly_rate']} SAR exceeds 3,230 SAR cap.";
        }
    }
}

echo json_encode([
    'success' => true,
    'audit_timestamp' => date('Y-m-d H:i:s'),
    'summary' => [
        'total_visas' => $totalCompanyVisas,
        'hourly_visas' => $totalHourlyVisas,
        'monthly_visas' => $totalMonthlyVisas,
        'hourly_percentage' => $hourlyVisaPercentage,
        'hourly_10_percent_rule_met' => $is10PercentRuleMet,
        'hourly_rule_target' => '10.0% Minimum Required',
        'diversity_rule_met' => (count($diversityViolations) === 0),
        'price_cap_rule_met' => (count($priceCapViolations) === 0),
        'overall_compliance_status' => ($is10PercentRuleMet && count($diversityViolations) === 0 && count($priceCapViolations) === 0) ? 'FULLY_COMPLIANT' : 'ATTENTION_REQUIRED'
    ],
    'nationalities' => $nationalityBreakdown,
    'violations' => [
        'diversity' => $diversityViolations,
        'price_caps' => $priceCapViolations
    ],
    'regulations' => [
        'domestic_hourly_cap' => '123 SAR / hr',
        'domestic_monthly_cap' => '3,230 SAR / mo',
        'exempt_professions' => 'Nurses, Caregivers, Cooks, Drivers (Uncapped)',
        'hourly_visa_allocation_rule' => 'Minimum 10% of total utilized company visas',
        'diversity_rule' => 'No reliance on a single nationality for visa issuance',
        'kafala_transfer_rule' => 'Per-nationality quotas established by Ministry must not be exceeded'
    ]
]);
