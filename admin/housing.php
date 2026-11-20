<?php
// admin/housing.php - Accommodation, supervisor oversight and Indonesian runaway prevention
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$housingUnits = $pdo->query("SELECT h.*, 
                            (SELECT COUNT(*) FROM workers WHERE housing_unit_id = h.id) as actual_occupants,
                            (SELECT COUNT(*) FROM workers WHERE housing_unit_id = h.id AND absconding_risk = 'high') as high_risk_count
                            FROM housing_units h")->fetchAll();

$indonesianWorkers = $pdo->query("SELECT w.*, h.building_name, h.room_number, h.supervisor_name 
                                  FROM workers w 
                                  LEFT JOIN housing_units h ON w.housing_unit_id = h.id 
                                  WHERE w.nationality = 'Indonesian' 
                                  ORDER BY w.id ASC")->fetchAll();

$securityLogs = $pdo->query("SELECT s.*, w.full_name as worker_name, w.nationality, w.profession 
                             FROM security_logs s 
                             JOIN workers w ON s.worker_id = w.id 
                             ORDER BY s.id DESC LIMIT 40")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation & Security Oversight | SAFWA Labor Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-container">
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fa-solid fa-landmark"></i></div>
            <div class="logo-text"><h2>SAFWA LABOR</h2><span>KSA Labor Control</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Core Operations</div>
            <a href="index.php" class="nav-item"><i class="fa-solid fa-shield-halved"></i><span>Ministry Compliance</span></a>
            <a href="workers.php" class="nav-item"><i class="fa-solid fa-users"></i><span>Workers Directory</span></a>
            <a href="time-tracking.php" class="nav-item"><i class="fa-solid fa-clock-rotate-left"></i><span>Time & Hours Tracking</span></a>
            <a href="housing.php" class="nav-item active"><i class="fa-solid fa-building-shield"></i><span>Housing & Security</span></a>
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
                <div class="topbar-title"><h1>Accommodation & Security Supervision Center</h1></div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-outline btn-sm" onclick="toggleLanguage()"><i class="fa-solid fa-globe"></i> AR / EN</button>
                <button class="btn btn-danger btn-sm" onclick="openLogIncidentModal()"><i class="fa-solid fa-triangle-exclamation"></i> Log Security Alert</button>
            </div>
        </header>

        <main class="content-body">
            <!-- Security Directive Banner -->
            <div style="background:#fef3c7; border:1px solid #f59e0b; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center;">
                <i class="fa-solid fa-shield-halved" style="font-size:2rem; color:#b45309;"></i>
                <div style="font-size:0.85rem; color:#78350f;">
                    <strong>Housing Supervision & Runaway Prevention Protocol:</strong>
                    Suitable compound housing must be provided with assigned female supervisors (Fatimah Al-Ghamdi & Nouf Al-Otaibi) to oversee workers 24/7 and strictly prevent leaving the premises without authorized dispatch.
                    Special surveillance is in effect for high-demand <strong>Indonesian workers</strong> to prevent absconding.
                </div>
            </div>

            <!-- Housing Units Grid -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-hotel text-primary"></i> Compound Housing Units & Supervisor Assignments</h3>
                    <div class="panel-actions">
                        <span class="badge badge-success"><?= count($housingUnits) ?> Housing Blocks Monitored</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem;">
                        <?php foreach ($housingUnits as $unit): ?>
                            <div style="border:1px solid #e2e8f0; border-radius:14px; padding:1.25rem; background:#ffffff;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                                    <div>
                                        <h4 style="font-size:1.05rem; font-weight:700; margin:0;"><?= htmlspecialchars($unit['building_name']) ?></h4>
                                        <span class="badge badge-info mt-1">Room <?= $unit['room_number'] ?></span>
                                    </div>
                                    <span class="badge badge-success"><i class="fa-solid fa-lock"></i> Gate Active</span>
                                </div>

                                <div style="margin:1rem 0;">
                                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.35rem;">
                                        <span>Occupancy</span>
                                        <strong><?= $unit['actual_occupants'] ?> / <?= $unit['capacity'] ?> Beds</strong>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width:<?= ($unit['actual_occupants']/$unit['capacity'])*100 ?>%; background:#0f766e;"></div>
                                    </div>
                                    <?php if ($unit['high_risk_count'] > 0): ?>
                                        <div style="font-size:0.75rem; color:#b45309; font-weight:700; margin-top:0.4rem;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> <?= $unit['high_risk_count'] ?> High Absconding Risk Occupants
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div style="background:#f8fafc; border-radius:8px; padding:0.6rem; font-size:0.75rem; color:#475569;">
                                    <div><strong>Supervisor:</strong> <?= htmlspecialchars($unit['supervisor_name']) ?></div>
                                    <div><strong>Phone:</strong> <?= htmlspecialchars($unit['supervisor_phone']) ?></div>
                                    <div><strong>Protocol:</strong> <?= htmlspecialchars($unit['security_status']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Indonesian Runaway Prevention Matrix -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                        Indonesian Workers Absconding Prevention & Passport Vault Monitor
                    </h3>
                    <div class="panel-actions">
                        <span class="badge risk-high"><?= count($indonesianWorkers) ?> Indonesian Personnel Under Security</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Worker Name</th>
                                    <th>Profession</th>
                                    <th>Passport & Iqama</th>
                                    <th>Assigned Room</th>
                                    <th>Supervisor On Duty</th>
                                    <th>Risk Classification</th>
                                    <th>Current Status</th>
                                    <th>Curfew Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($indonesianWorkers as $w): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($w['full_name']) ?></strong></td>
                                    <td><span class="badge badge-info"><?= $w['profession'] ?></span></td>
                                    <td>
                                        <div>Passport: <?= $w['passport_no'] ?> (Secured in Company Vault)</div>
                                        <div style="font-size:0.7rem; color:#64748b;">Iqama: <?= $w['iqama_no'] ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($w['building_name']) ?> (Rm <?= $w['room_number'] ?>)</td>
                                    <td><strong><?= htmlspecialchars($w['supervisor_name']) ?></strong></td>
                                    <td><span class="badge risk-high"><i class="fa-solid fa-shield"></i> High Runaway Alert</span></td>
                                    <td>
                                        <span class="badge badge-success" style="text-transform:capitalize;">
                                            <?= str_replace('_', ' ', $w['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" onclick="logRollCall(<?= $w['id'] ?>, '<?= htmlspecialchars($w['full_name']) ?>')">
                                            <i class="fa-solid fa-clipboard-check"></i> Roll Call Check
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Security Audit & Gate Pass Incident Log -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-shield-virus text-primary"></i> 24/7 Gate Surveillance & Incident Log</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Worker Name</th>
                                    <th>Nationality</th>
                                    <th>Event Type</th>
                                    <th>Supervisor On Duty</th>
                                    <th>Details & Notes</th>
                                    <th>Ministry Musaned Report</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($securityLogs as $log): ?>
                                <tr>
                                    <td><?= $log['logged_at'] ?></td>
                                    <td><strong><?= htmlspecialchars($log['worker_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($log['nationality']) ?></td>
                                    <td>
                                        <?php if ($log['event_type'] === 'unauthorized_exit_prevented'): ?>
                                            <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Unauthorized Exit Blocked</span>
                                        <?php elseif ($log['event_type'] === 'curfew_roll_call'): ?>
                                            <span class="badge badge-success"><i class="fa-solid fa-bed"></i> Curfew Verified Present</span>
                                        <?php else: ?>
                                            <span class="badge badge-info"><i class="fa-solid fa-id-badge"></i> <?= str_replace('_', ' ', $log['event_type']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['supervisor_name']) ?></td>
                                    <td style="max-width:300px; font-size:0.8rem;"><?= htmlspecialchars($log['notes']) ?></td>
                                    <td>
                                        <?php if ($log['reported_to_ministry']): ?>
                                            <span class="badge badge-warning"><i class="fa-solid fa-paper-plane"></i> Transmitted to HRSD</span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size:0.75rem;">Internal Log</span>
                                        <?php endif; ?>
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

<!-- Log Security Event Modal -->
<div class="modal-overlay" id="securityModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-shield text-primary"></i> Log Compound Security Event</h3>
            <button class="btn-icon" onclick="closeSecurityModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Worker:</label>
                <select id="secWorkerSelect" class="form-control">
                    <?php foreach ($indonesianWorkers as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['full_name']) ?> (<?= $w['nationality'] ?> - Room <?= $w['room_number'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Event Classification:</label>
                <select id="secEventType" class="form-control">
                    <option value="curfew_roll_call">Routine Evening Curfew Roll Call (Confirmed In Room)</option>
                    <option value="unauthorized_exit_prevented">Unauthorized Gate Exit Attempt Blocked by Guard</option>
                    <option value="high_risk_monitoring">Indonesian Nationality Welfare & Risk Inspection</option>
                    <option value="absconded_reported">CRITICAL: Worker Left Premises Without Pass (Report to Ministry)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Supervisor On Duty:</label>
                <select id="secSupervisor" class="form-control">
                    <option value="Fatimah Al-Ghamdi">Fatimah Al-Ghamdi (Compound Block A)</option>
                    <option value="Nouf Al-Otaibi">Nouf Al-Otaibi (Compound Block B)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Incident Report & Security Notes:</label>
                <textarea id="secNotes" class="form-control" rows="3" placeholder="Enter incident circumstances, security intervention and actions taken..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeSecurityModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitModalSecurityEvent()"><i class="fa-solid fa-check"></i> Register Security Entry</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
function openLogIncidentModal() {
    document.getElementById('securityModal').classList.add('active');
}
function closeSecurityModal() {
    document.getElementById('securityModal').classList.remove('active');
}
function logRollCall(workerId, name) {
    document.getElementById('secWorkerSelect').value = workerId;
    document.getElementById('secEventType').value = 'curfew_roll_call';
    document.getElementById('secNotes').value = `Roll call verified: ${name} present in assigned bed. Welfare satisfactory.`;
    openLogIncidentModal();
}
async function submitModalSecurityEvent() {
    const workerId = document.getElementById('secWorkerSelect').value;
    const eventType = document.getElementById('secEventType').value;
    const supervisor = document.getElementById('secSupervisor').value;
    const notes = document.getElementById('secNotes').value;

    try {
        const res = await fetch('../api/housing.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'log_security_event',
                worker_id: workerId,
                event_type: eventType,
                supervisor_name: supervisor,
                notes: notes,
                reported_to_ministry: (eventType === 'absconded_reported' ? 1 : 0)
            })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    } catch (e) {
        alert("Failed to submit event.");
    }
}
</script>
</body>
</html>
