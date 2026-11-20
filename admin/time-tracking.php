<?php
// admin/time-tracking.php - Real-time hours monitoring, check-in/out and shift logs
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$activeLogs = $pdo->query("SELECT t.*, w.full_name as worker_name, w.profession, w.nationality, 
                                  b.booking_code, b.client_name, b.client_address, b.service_type, b.hours_allocated 
                           FROM time_logs t 
                           JOIN workers w ON t.worker_id = w.id 
                           JOIN bookings b ON t.booking_id = b.id 
                           WHERE t.status = 'active' 
                           ORDER BY t.check_in_time DESC")->fetchAll();

$historyLogs = $pdo->query("SELECT t.*, w.full_name as worker_name, w.profession, w.nationality, 
                                   b.booking_code, b.client_name, b.client_address 
                            FROM time_logs t 
                            JOIN workers w ON t.worker_id = w.id 
                            JOIN bookings b ON t.booking_id = b.id 
                            WHERE t.status = 'completed' 
                            ORDER BY t.id DESC LIMIT 30")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Hours & Time Tracking | SAFWA Labor Control</title>
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
            <a href="time-tracking.php" class="nav-item active"><i class="fa-solid fa-clock-rotate-left"></i><span>Time & Hours Tracking</span></a>
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
                <div class="topbar-title"><h1>Domestic Labor Service Hours & Time Tracking</h1></div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-outline btn-sm" onclick="toggleLanguage()"><i class="fa-solid fa-globe"></i> AR / EN</button>
                <button class="btn btn-primary btn-sm" onclick="location.reload()"><i class="fa-solid fa-arrows-rotate"></i> Refresh Timers</button>
            </div>
        </header>

        <main class="content-body">
            <!-- Active Shift Cards -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-stopwatch text-primary"></i> Active Shifts in Real Time</h3>
                    <div class="panel-actions">
                        <span class="badge badge-success"><?= count($activeLogs) ?> Active Workers on Site</span>
                    </div>
                </div>
                <div class="panel-body">
                    <?php if (empty($activeLogs)): ?>
                        <div style="text-align:center; padding:2.5rem; color:#64748b;">
                            <i class="fa-solid fa-mug-hot fa-3x" style="margin-bottom:0.75rem; color:#94a3b8;"></i>
                            <p>No active service shifts right now. Workers are at housing accommodation or in scheduled transit.</p>
                        </div>
                    <?php else: ?>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap:1.25rem;">
                            <?php foreach ($activeLogs as $log): ?>
                                <div style="border:1px solid #e2e8f0; border-radius:14px; padding:1.25rem; background:#ffffff; box-shadow:0 4px 6px rgba(0,0,0,0.02);">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                                        <div>
                                            <span class="badge badge-info mb-1"><?= $log['booking_code'] ?></span>
                                            <h4 style="font-size:1.1rem; font-weight:700; margin:0;"><?= htmlspecialchars($log['worker_name']) ?></h4>
                                            <span style="font-size:0.8rem; color:#059669; font-weight:600;"><?= $log['profession'] ?> (<?= $log['nationality'] ?>)</span>
                                        </div>
                                        <div style="text-align:right;">
                                            <span class="badge badge-success"><i class="fa-solid fa-circle-dot"></i> Live On Duty</span>
                                        </div>
                                    </div>

                                    <div style="background:#f8fafc; border-radius:10px; padding:0.75rem; font-size:0.8rem; color:#475569; margin-bottom:1rem;">
                                        <div><i class="fa-solid fa-user text-primary"></i> <strong>Client:</strong> <?= htmlspecialchars($log['client_name']) ?></div>
                                        <div><i class="fa-solid fa-location-dot text-danger"></i> <strong>Location:</strong> <?= htmlspecialchars($log['client_address']) ?></div>
                                        <div><i class="fa-solid fa-clock text-info"></i> <strong>Check-in Time:</strong> <?= $log['check_in_time'] ?></div>
                                        <?php if ($log['hours_allocated'] > 0): ?>
                                            <div><i class="fa-solid fa-hourglass-half text-warning"></i> <strong>Package Limit:</strong> <?= $log['hours_allocated'] ?> Hours</div>
                                        <?php endif; ?>
                                    </div>

                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div style="font-size:0.75rem; color:#64748b;">
                                            GPS: <?= $log['location_lat'] ?>, <?= $log['location_lng'] ?>
                                        </div>
                                        <button class="btn btn-danger btn-sm" onclick="openCheckOutModal(<?= $log['id'] ?>, '<?= htmlspecialchars($log['worker_name']) ?>')">
                                            <i class="fa-solid fa-right-from-bracket"></i> Check-Out Shift
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Completed Shifts History Table -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Completed Service Shifts & Verified Hours</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking Ref</th>
                                    <th>Worker</th>
                                    <th>Client & Address</th>
                                    <th>Check-In</th>
                                    <th>Check-Out</th>
                                    <th>Hours Worked</th>
                                    <th>Verification Signature</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyLogs as $h): ?>
                                <tr>
                                    <td><strong><?= $h['booking_code'] ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($h['worker_name']) ?></strong>
                                        <div style="font-size:0.7rem; color:#64748b;"><?= $h['profession'] ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($h['client_name']) ?></div>
                                        <div style="font-size:0.7rem; color:#64748b;"><?= htmlspecialchars($h['client_address']) ?></div>
                                    </td>
                                    <td><?= substr($h['check_in_time'], 0, 16) ?></td>
                                    <td><?= substr($h['check_out_time'], 0, 16) ?></td>
                                    <td>
                                        <strong style="color:#0f766e; font-size:1rem;"><?= $h['hours_worked'] ?> hrs</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><i class="fa-solid fa-signature"></i> Verified</span>
                                        <div style="font-size:0.65rem; color:#94a3b8; font-family:monospace;"><?= $h['client_signature_token'] ?: 'APP-SIG-KSA' ?></div>
                                    </td>
                                    <td><span class="badge badge-info">Completed & Billed</span></td>
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

<!-- Check Out Modal -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-dialog" style="max-width:400px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-clock text-primary"></i> Confirm Shift Check-Out</h3>
            <button class="btn-icon" onclick="closeCheckOutModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:0.85rem; color:#475569;">
                Checking out worker <strong id="checkoutWorkerName"></strong>. Service duration will be calculated and billed, and transit bus requested.
            </p>
            <div class="form-group">
                <label class="form-label">Client Approval Code / Signature:</label>
                <input type="text" id="clientSignatureInput" class="form-control" value="CLIENT-VERIFIED-APP">
            </div>
            <div class="form-group">
                <label class="form-label">Supervisor / Shift Notes:</label>
                <input type="text" id="shiftNotesInput" class="form-control" value="Shift successfully finished with high client rating.">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeCheckOutModal()">Cancel</button>
            <button class="btn btn-success" id="btnConfirmCheckOut" onclick="confirmCheckOut()"><i class="fa-solid fa-check"></i> Complete Shift</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
let currentLogId = null;
function openCheckOutModal(logId, name) {
    currentLogId = logId;
    document.getElementById('checkoutWorkerName').innerText = name;
    document.getElementById('checkoutModal').classList.add('active');
}
function closeCheckOutModal() {
    document.getElementById('checkoutModal').classList.remove('active');
}
async function confirmCheckOut() {
    const sig = document.getElementById('clientSignatureInput').value;
    const notes = document.getElementById('shiftNotesInput').value;

    try {
        const res = await fetch('../api/time-tracker.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'check_out',
                time_log_id: currentLogId,
                client_signature: sig,
                notes: notes
            })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (e) {
        alert("Check out failed.");
    }
}
</script>
</body>
</html>
