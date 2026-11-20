<?php
// admin/kafala.php - Sponsorship Transfer (Kafala) & Ministry Nationality Quotas Management
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$quotas = $pdo->query("SELECT * FROM ministry_quotas ORDER BY total_visas DESC")->fetchAll();

$transfers = $pdo->query("SELECT k.*, w.full_name as worker_name, w.passport_no, w.iqama_no, b.booking_code, b.total_amount 
                         FROM kafala_transfers k 
                         JOIN workers w ON k.worker_id = w.id 
                         JOIN bookings b ON k.booking_id = b.id 
                         ORDER BY k.id DESC")->fetchAll();

$eligibleBookings = $pdo->query("SELECT b.*, w.full_name as worker_name, w.nationality, w.profession, p.name as package_name 
                                FROM bookings b 
                                JOIN workers w ON b.worker_id = w.id 
                                JOIN packages p ON b.package_id = p.id 
                                WHERE p.kafala_eligible = 1 
                                ORDER BY b.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kafala Sponsorship Transfers & Ministry Quotas | SAFWA Labor Control</title>
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
            <a href="housing.php" class="nav-item"><i class="fa-solid fa-building-shield"></i><span>Housing & Security</span></a>
            <a href="fleet.php" class="nav-item"><i class="fa-solid fa-bus"></i><span>Fleet Transportation</span></a>
            <div class="nav-section-title">Legal & Talent</div>
            <a href="kafala.php" class="nav-item active"><i class="fa-solid fa-id-card-clip"></i><span>Kafala Sponsorship</span></a>
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
                <div class="topbar-title"><h1>Sponsorship Transfer (Kafala) & Ministry Quotas</h1></div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-outline btn-sm" onclick="toggleLanguage()"><i class="fa-solid fa-globe"></i> AR / EN</button>
            </div>
        </header>

        <main class="content-body">
            <!-- Regulatory Directives Banner -->
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center;">
                <i class="fa-solid fa-id-card-clip text-primary" style="font-size:2rem;"></i>
                <div style="font-size:0.85rem; color:#1e40af;">
                    <strong>Package Expiration & Sponsorship Transfer (Kafala) Regulations:</strong>
                    Upon expiration of promotional discounted packages (e.g. 3-month package @ 11,000 SAR), clients must be notified of their eligibility to transfer the worker's sponsorship.
                    <strong>Crucial Rule:</strong> The Ministry of Labor has established statutory caps on the number of sponsorship transfers allowed for each nationality, which <em>cannot be exceeded</em>.
                </div>
            </div>

            <!-- Ministry Nationality Quota Caps Cards -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-scale-balanced text-primary"></i> Ministry Kafala Quotas Per Nationality</h3>
                </div>
                <div class="panel-body">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem;">
                        <?php foreach ($quotas as $q): 
                            $remaining = max(0, $q['max_kafala_transfers_allowed'] - $q['kafala_transfers_used']);
                            $isFull = ($remaining === 0);
                            $pct = round(($q['kafala_transfers_used'] / $q['max_kafala_transfers_allowed']) * 100);
                        ?>
                            <div style="border:1px solid <?= $isFull ? '#ef4444' : '#e2e8f0' ?>; background:<?= $isFull ? '#fef2f2' : '#ffffff' ?>; border-radius:12px; padding:1.1rem;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                    <h4 style="font-size:1rem; font-weight:700; margin:0;"><?= htmlspecialchars($q['nationality']) ?></h4>
                                    <span class="badge <?= $isFull ? 'badge-danger' : 'badge-info' ?>"><?= $pct ?>% Used</span>
                                </div>
                                <div style="font-size:1.5rem; font-weight:800; color:#0f172a; margin-bottom:0.25rem;">
                                    <?= $q['kafala_transfers_used'] ?> <span style="font-size:0.9rem; color:#64748b;">/ <?= $q['max_kafala_transfers_allowed'] ?> Cap</span>
                                </div>
                                <div class="progress-bar mb-2">
                                    <div class="progress-fill" style="width:<?= $pct ?>%; background:<?= $isFull ? '#ef4444' : '#0f766e' ?>;"></div>
                                </div>
                                <div style="font-size:0.75rem; color:<?= $isFull ? '#991b1b' : '#059669' ?>; font-weight:600;">
                                    <?= $isFull ? '<i class="fa-solid fa-ban"></i> Quota Full - Transfers Blocked' : "<i class=\"fa-solid fa-check\"></i> {$remaining} Transfers Available" ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Matured 3-Month Packages (11,000 SAR) & Client Eligibility -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-user-check text-primary"></i> Multi-Month Packages Matured for Sponsorship Transfer</h3>
                    <div class="panel-actions">
                        <span class="badge badge-success"><?= count($eligibleBookings) ?> Contracts Eligible</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Client Name & Phone</th>
                                    <th>Worker (Nationality)</th>
                                    <th>Package Type</th>
                                    <th>Total Paid</th>
                                    <th>Duration Period</th>
                                    <th>Kafala Notification Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eligibleBookings as $b): ?>
                                <tr>
                                    <td><strong><?= $b['booking_code'] ?></strong></td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($b['client_name']) ?></strong></div>
                                        <div style="font-size:0.75rem; color:#64748b;"><?= $b['client_phone'] ?></div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($b['worker_name']) ?></strong>
                                        <div style="font-size:0.75rem; color:#059669; font-weight:600;"><?= $b['nationality'] ?> (<?= $b['profession'] ?>)</div>
                                    </td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($b['package_name']) ?></span></td>
                                    <td><strong style="color:#0f766e;"><?= number_format($b['total_amount']) ?> SAR</strong></td>
                                    <td><?= $b['start_date'] ?> to <?= $b['end_date'] ?></td>
                                    <td>
                                        <?php if ($b['kafala_status'] === 'transfer_approved'): ?>
                                            <span class="badge badge-success"><i class="fa-solid fa-check-double"></i> Transfer Approved</span>
                                        <?php elseif ($b['kafala_status'] === 'eligible'): ?>
                                            <span class="badge badge-warning"><i class="fa-solid fa-bell"></i> Client Notified (Eligible)</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">In Duration Period</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" onclick="triggerClientKafalaNotice(<?= $b['id'] ?>)">
                                            <i class="fa-solid fa-paper-plane"></i> Send Client Notice
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Processed Sponsorship Transfers Log -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-list-check text-primary"></i> Processed Sponsorship Transfer Records</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Worker Name</th>
                                    <th>Nationality</th>
                                    <th>Client (New Sponsor)</th>
                                    <th>Saudi ID / Iqama</th>
                                    <th>Gov Fee</th>
                                    <th>Ministry Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transfers)): ?>
                                    <tr><td colspan="8" style="text-align:center; padding:1.5rem; color:#64748b;">No formal transfers submitted yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($transfers as $t): ?>
                                    <tr>
                                        <td><strong>#TRF-<?= $t['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($t['worker_name']) ?></td>
                                        <td><span class="badge badge-info"><?= $t['nationality'] ?></span></td>
                                        <td><?= htmlspecialchars($t['client_name']) ?></td>
                                        <td style="font-family:monospace;"><?= $t['client_id_number'] ?></td>
                                        <td><?= number_format($t['transfer_fee']) ?> SAR</td>
                                        <td><span class="badge badge-success"><i class="fa-solid fa-check"></i> Quota Approved</span></td>
                                        <td><?= substr($t['request_date'], 0, 10) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
function triggerClientKafalaNotice(bookingId) {
    alert("Automated SMS & In-App Notification dispatched to client: You are now eligible to transfer domestic worker sponsorship (Kafala) under approved Ministry quota.");
}
</script>
</body>
</html>
