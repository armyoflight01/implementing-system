<?php
// admin/workers.php - Workers Directory, Absconding Risk Matrix, & Price Cap Enforcement
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$workers = $pdo->query("SELECT w.*, h.building_name, h.room_number, h.supervisor_name, h.supervisor_phone 
                       FROM workers w 
                       LEFT JOIN housing_units h ON w.housing_unit_id = h.id 
                       ORDER BY w.id ASC")->fetchAll();

$housingUnits = $pdo->query("SELECT * FROM housing_units")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workers Directory & Risk Profiling | SAFWA Labor Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fa-solid fa-landmark"></i></div>
            <div class="logo-text"><h2>SAFWA LABOR</h2><span>KSA Labor Control</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Core Operations</div>
            <a href="index.php" class="nav-item"><i class="fa-solid fa-shield-halved"></i><span>Ministry Compliance</span></a>
            <a href="workers.php" class="nav-item active"><i class="fa-solid fa-users"></i><span>Workers Directory</span></a>
            <a href="time-tracking.php" class="nav-item"><i class="fa-solid fa-clock-rotate-left"></i><span>Time & Hours Tracking</span></a>
            <a href="housing.php" class="nav-item"><i class="fa-solid fa-building-shield"></i><span>Housing & Security</span></a>
            <a href="fleet.php" class="nav-item"><i class="fa-solid fa-bus"></i><span>Fleet Transportation</span></a>
            <div class="nav-section-title">Legal & Talent</div>
            <a href="kafala.php" class="nav-item"><i class="fa-solid fa-id-card-clip"></i><span>Kafala Sponsorship</span></a>
            <a href="hr-maharah.php" class="nav-item"><i class="fa-solid fa-briefcase"></i><span>Maharah HR Hiring</span></a>
            <a href="finance.php" class="nav-item"><i class="fa-solid fa-wallet"></i><span>Finance & Price Caps</span></a>
            <div class="nav-section-title">App Client Channel</div>
            <a href="../app/index.php" class="nav-item" target="_blank"><i class="fa-solid fa-mobile-screen-button text-primary"></i><span>Open Mobile App View</span></a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="document.getElementById('appSidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title"><h1>Domestic & Specialized Workers Directory</h1></div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-outline btn-sm" onclick="toggleLanguage()"><i class="fa-solid fa-globe"></i> AR / EN</button>
                <button class="btn btn-primary btn-sm" onclick="openAddWorkerModal()"><i class="fa-solid fa-user-plus"></i> Register Worker</button>
            </div>
        </header>

        <main class="content-body">
            <!-- Notice Banner on Ministry Price Caps & Indonesian Absconding Monitoring -->
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center;">
                <i class="fa-solid fa-circle-exclamation text-primary" style="font-size:1.75rem;"></i>
                <div style="font-size:0.85rem; color:#1e40af;">
                    <strong>Statutory Price Caps & Nationality Security Protocols:</strong>
                    Domestic workers are capped at <strong>123 SAR/hr</strong> and <strong>3,230 SAR/mo</strong>. Nurses, Caregivers, Cooks, and Drivers are exempt.
                    Due to high demand and elevated statistical runaway rates among <strong>Indonesian workers</strong>, high absconding risk protocols and 24/7 housing supervision are active.
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-list-check text-primary"></i> Active Workforce & Assigned Housing Supervisors</h3>
                    <div class="panel-actions">
                        <span class="badge badge-warning"><?= count($workers) ?> Registered Personnel</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Worker Name</th>
                                    <th>Nationality</th>
                                    <th>Profession</th>
                                    <th>Contract Type</th>
                                    <th>Rates & Price Cap Status</th>
                                    <th>Housing Unit & Supervisor</th>
                                    <th>Absconding Risk</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($workers as $w): 
                                    $isDomestic = ($w['profession'] === 'Domestic Worker');
                                ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.6rem;">
                                            <div style="width:36px; height:36px; border-radius:8px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; color:#475569; font-weight:700;">
                                                <?= strtoupper(substr($w['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($w['full_name']) ?></strong>
                                                <div style="font-size:0.7rem; color:#64748b;">Iqama: <?= $w['iqama_no'] ?: 'Pending' ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($w['nationality']) ?></strong>
                                        <?php if ($w['nationality'] === 'Indonesian'): ?>
                                            <div style="font-size:0.65rem; color:#b45309; font-weight:700;"><i class="fa-solid fa-shield"></i> High Demand / Runaway Alert</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $isDomestic ? 'badge-info' : 'badge-success' ?>">
                                            <?= htmlspecialchars($w['profession']) ?>
                                        </span>
                                    </td>
                                    <td style="text-transform:capitalize;"><?= $w['contract_type'] ?></td>
                                    <td>
                                        <div><strong><?= $w['hourly_rate'] ?> SAR/hr</strong> | <strong><?= $w['monthly_rate'] ?> SAR/mo</strong></div>
                                        <?php if ($isDomestic): ?>
                                            <span class="cap-notice" title="Adheres to Ministry 123/hr and 3230/mo cap"><i class="fa-solid fa-check"></i> Capped</span>
                                        <?php else: ?>
                                            <span style="font-size:0.7rem; color:#2563eb; font-weight:600;"><i class="fa-solid fa-star"></i> Uncapped Special</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($w['building_name'] ?: 'Compound Unit') ?></strong> - Room <?= $w['room_number'] ?></div>
                                        <div style="font-size:0.7rem; color:#64748b;">Supervisor: <?= htmlspecialchars($w['supervisor_name'] ?: 'On Duty') ?></div>
                                    </td>
                                    <td>
                                        <?php if ($w['absconding_risk'] === 'high'): ?>
                                            <span class="badge risk-high"><i class="fa-solid fa-triangle-exclamation"></i> High Risk</span>
                                        <?php elseif ($w['absconding_risk'] === 'medium'): ?>
                                            <span class="badge risk-medium">Medium</span>
                                        <?php else: ?>
                                            <span class="badge risk-low"><i class="fa-solid fa-check"></i> Low</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $stClass = 'badge-info';
                                            if ($w['status'] === 'in_service') $stClass = 'badge-success';
                                            if ($w['status'] === 'absconded') $stClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?= $stClass ?>" style="text-transform:capitalize;">
                                            <?= str_replace('_', ' ', $w['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="time-tracking.php?worker_id=<?= $w['id'] ?>" class="btn btn-outline btn-sm" title="View Shift Hours">
                                            <i class="fa-solid fa-clock"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Worker Modal with Real-time Price Cap Validation -->
<div class="modal-overlay" id="addWorkerModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-user-plus text-primary"></i> Register New Domestic or Care Worker</h3>
            <button class="btn-icon" onclick="closeAddWorkerModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Full Name (اسم العامل/العاملة):</label>
                <input type="text" id="workerFullName" class="form-control" placeholder="e.g. Nur Aini">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Nationality (الجنسية):</label>
                    <select id="workerNationality" class="form-control">
                        <option value="Indonesian">Indonesian (إندونيسيا - High Demand)</option>
                        <option value="Filipino">Filipino (الفلبين)</option>
                        <option value="Kenyan">Kenyan (كينيا)</option>
                        <option value="Ethiopian">Ethiopian (إثيوبيا)</option>
                        <option value="Ugandan">Ugandan (أوغندا)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Profession (المهنة):</label>
                    <select id="workerProfession" class="form-control" onchange="checkWorkerProfessionCap()">
                        <option value="Domestic Worker">Domestic Worker (عاملة منزلية - Capped)</option>
                        <option value="Nurse">Private Nurse (ممرضة منزلية - Uncapped)</option>
                        <option value="Caregiver">Caregiver (رعاية كبار سن - Uncapped)</option>
                        <option value="Cook">Private Cook (طباخة/طباخ - Uncapped)</option>
                        <option value="Driver">Private Driver (سائق خاص - Uncapped)</option>
                    </select>
                </div>
            </div>

            <!-- Price Cap Indicator Alert -->
            <div id="workerPriceCapNotice" class="cap-notice mb-3" style="width:100%; box-sizing:border-box;">
                <i class="fa-solid fa-scale-balanced"></i> <strong>Saudi Ministry Price Cap Applies:</strong> Max 123 SAR/hour, Max 3,230 SAR/month.
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Hourly Rate (SAR):</label>
                    <input type="number" id="workerHourlyRate" class="form-control" value="95" max="123">
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Rate (SAR):</label>
                    <input type="number" id="workerMonthlyRate" class="form-control" value="3150" max="3230">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Contract Type:</label>
                    <select id="workerContractType" class="form-control">
                        <option value="hourly">Hourly Only (10% Visa Allocation)</option>
                        <option value="monthly">Monthly Only</option>
                        <option value="both">Flexible (Both Hourly & Monthly)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Housing Unit:</label>
                    <select id="workerHousingUnit" class="form-control">
                        <?php foreach ($housingUnits as $h): ?>
                            <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['building_name']) ?> - Room <?= $h['room_number'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeAddWorkerModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveWorker()"><i class="fa-solid fa-check"></i> Register Worker</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
function openAddWorkerModal() {
    document.getElementById('addWorkerModal').classList.add('active');
    checkWorkerProfessionCap();
}
function closeAddWorkerModal() {
    document.getElementById('addWorkerModal').classList.remove('active');
}
</script>
</body>
</html>
