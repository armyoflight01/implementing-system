<?php
// admin/fleet.php - Fleet & Bus transportation management for domestic workers
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$fleet = $pdo->query("SELECT * FROM fleet ORDER BY id ASC")->fetchAll();
$trips = $pdo->query("SELECT t.*, f.vehicle_type, f.plate_number, f.driver_name, f.driver_phone 
                      FROM dispatch_trips t 
                      JOIN fleet f ON t.vehicle_id = f.id 
                      ORDER BY t.id DESC LIMIT 40")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet & Worker Transportation | SAFWA Labor Control</title>
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
            <a href="fleet.php" class="nav-item active"><i class="fa-solid fa-bus"></i><span>Fleet Transportation</span></a>
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
                <div class="topbar-title"><h1>Worker Transportation Fleet & Bus Dispatch</h1></div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-outline btn-sm" onclick="toggleLanguage()"><i class="fa-solid fa-globe"></i> AR / EN</button>
                <button class="btn btn-primary btn-sm" onclick="openScheduleTripModal()"><i class="fa-solid fa-calendar-plus"></i> Schedule Bus Dispatch</button>
            </div>
        </header>

        <main class="content-body">
            <!-- Transportation Policy Banner -->
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center;">
                <i class="fa-solid fa-bus-simple text-primary" style="font-size:2rem;"></i>
                <div style="font-size:0.85rem; color:#1e40af;">
                    <strong>Mandatory Safe Transit Standard:</strong>
                    It is essential to provide company buses and vehicles to transport workers employed on both hourly and monthly bases. Workers must be securely transported directly from housing to client residences and safely returned upon shift completion.
                </div>
            </div>

            <!-- Vehicles Fleet Cards Grid -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-van-shuttle text-primary"></i> Company Transportation Fleet</h3>
                    <div class="panel-actions">
                        <span class="badge badge-info"><?= count($fleet) ?> Fleet Units Available</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
                        <?php foreach ($fleet as $v): ?>
                            <div style="border:1px solid #e2e8f0; border-radius:14px; padding:1.25rem; background:#ffffff; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                                    <div>
                                        <h4 style="font-size:1.05rem; font-weight:700; margin:0;"><?= htmlspecialchars($v['vehicle_type']) ?></h4>
                                        <div style="font-size:0.8rem; font-family:monospace; color:#0f766e; font-weight:700; margin-top:0.25rem;"><?= $v['plate_number'] ?></div>
                                    </div>
                                    <span class="badge <?= ($v['current_status'] === 'available') ? 'badge-success' : 'badge-warning' ?>" style="text-transform:capitalize;">
                                        <?= str_replace('_', ' ', $v['current_status']) ?>
                                    </span>
                                </div>

                                <div style="font-size:0.85rem; color:#475569; margin-bottom:1rem;">
                                    <div><i class="fa-solid fa-users text-primary"></i> Passenger Capacity: <strong><?= $v['capacity'] ?> Seats</strong></div>
                                    <div><i class="fa-solid fa-id-card-clip text-info"></i> Driver: <strong><?= htmlspecialchars($v['driver_name']) ?></strong></div>
                                    <div><i class="fa-solid fa-phone text-muted"></i> <?= htmlspecialchars($v['driver_phone']) ?></div>
                                </div>

                                <button class="btn btn-outline btn-sm w-100" onclick="dispatchVehicleById(<?= $v['id'] ?>, '<?= $v['plate_number'] ?>')">
                                    <i class="fa-solid fa-route"></i> Dispatch for Shifts
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Dispatch Trips & Routes Table -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-map-location-dot text-primary"></i> Daily Transport Dispatch Schedule</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Trip ID</th>
                                    <th>Vehicle & Plate</th>
                                    <th>Driver</th>
                                    <th>Trip Nature</th>
                                    <th>Departure</th>
                                    <th>Destination District</th>
                                    <th>Workers Transported</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trips as $t): ?>
                                <tr>
                                    <td><strong>#TRIP-<?= $t['id'] ?></strong></td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($t['vehicle_type']) ?></strong></div>
                                        <div style="font-size:0.7rem; font-family:monospace; color:#0f766e;"><?= $t['plate_number'] ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($t['driver_name']) ?></div>
                                        <div style="font-size:0.7rem; color:#64748b;"><?= $t['driver_phone'] ?></div>
                                    </td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($t['trip_type']) ?></span></td>
                                    <td><?= substr($t['departure_time'], 0, 16) ?></td>
                                    <td><i class="fa-solid fa-location-dot text-danger"></i> <?= htmlspecialchars($t['destination_area']) ?></td>
                                    <td><strong><?= $t['worker_count'] ?> Workers</strong></td>
                                    <td>
                                        <span class="badge <?= ($t['status'] === 'completed') ? 'badge-success' : (($t['status'] === 'in_progress') ? 'badge-warning' : 'badge-info') ?>" style="text-transform:capitalize;">
                                            <?= str_replace('_', ' ', $t['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($t['status'] !== 'completed'): ?>
                                            <button class="btn btn-success btn-sm" onclick="markTripCompleted(<?= $t['id'] ?>)">
                                                <i class="fa-solid fa-check"></i> Safe Return
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size:0.75rem;"><i class="fa-solid fa-circle-check text-success"></i> Terminal Logged</span>
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

<!-- Schedule Trip Modal -->
<div class="modal-overlay" id="scheduleTripModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-bus text-primary"></i> Dispatch Bus / Vehicle</h3>
            <button class="btn-icon" onclick="closeScheduleTripModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Select Bus / Van:</label>
                <select id="dispatchVehicle" class="form-control">
                    <?php foreach ($fleet as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['vehicle_type']) ?> (<?= $v['plate_number'] ?> - Driver: <?= $v['driver_name'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Trip Type:</label>
                <select id="dispatchTripType" class="form-control">
                    <option value="Hourly Dropoff Route">Hourly Workers Morning Drop-Off (Client Residences)</option>
                    <option value="Hourly Pickup & Return">Hourly Workers Evening Pick-Up (Return to Housing)</option>
                    <option value="Monthly Placement">Monthly Domestic Worker Initial Placement</option>
                    <option value="Medical & Biometric Transit">Medical Examination / Jawazat Biometrics Route</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Destination Area (Riyadh Districts):</label>
                <input type="text" id="dispatchDestination" class="form-control" value="Riyadh - Al-Malqa, Al-Sahafa, & Al-Yasmin" placeholder="e.g. Al-Sulaimaniyah">
            </div>
            <div class="form-group">
                <label class="form-label">Number of Workers Transported:</label>
                <input type="number" id="dispatchWorkersCount" class="form-control" value="6" min="1" max="30">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeScheduleTripModal()">Cancel</button>
            <button class="btn btn-primary" onclick="scheduleTrip()"><i class="fa-solid fa-paper-plane"></i> Confirm & Dispatch Bus</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
function openScheduleTripModal() {
    document.getElementById('scheduleTripModal').classList.add('active');
}
function closeScheduleTripModal() {
    document.getElementById('scheduleTripModal').classList.remove('active');
}
function dispatchVehicleById(id, plate) {
    document.getElementById('dispatchVehicle').value = id;
    openScheduleTripModal();
}
</script>
</body>
</html>
