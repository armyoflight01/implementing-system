<?php
// admin/hr-maharah.php - Recruitment management for Marketing (2) & Customer Service (2) roles via Maharah platform
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$candidates = $pdo->query("SELECT * FROM hr_recruitment ORDER BY id ASC")->fetchAll();

$marketingCount = 0;
$marketingFilled = 0;
$csCount = 0;
$csFilled = 0;

foreach ($candidates as $c) {
    if ($c['role'] === 'Marketing Specialist') {
        $marketingCount++;
        if (in_array($c['interview_status'], ['Offered', 'Hired'])) $marketingFilled++;
    } elseif ($c['role'] === 'Customer Service Representative') {
        $csCount++;
        if (in_array($c['interview_status'], ['Offered', 'Hired'])) $csFilled++;
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Recruitment (Maharah Platform) | SAFWA Labor Control</title>
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
            <a href="kafala.php" class="nav-item"><i class="fa-solid fa-id-card-clip"></i><span>Kafala Sponsorship</span></a>
            <a href="hr-maharah.php" class="nav-item active"><i class="fa-solid fa-briefcase"></i><span>Maharah HR Hiring</span></a>
            <a href="finance.php" class="nav-item"><i class="fa-solid fa-wallet"></i><span>Finance & Price Caps</span></a>
            <div class="nav-section-title">App Client Channel</div>
            <a href="../app/index.php" class="nav-item" target="_blank"><i class="fa-solid fa-mobile-screen-button text-primary"></i><span>Open Mobile App View</span></a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" onclick="document.getElementById('appSidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title"><h1>Talent Acquisition & Maharah Recruitment Pipeline</h1></div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-outline btn-sm" onclick="toggleLanguage()"><i class="fa-solid fa-globe"></i> AR / EN</button>
                <button class="btn btn-primary btn-sm" onclick="openAddCandidateModal()"><i class="fa-solid fa-user-plus"></i> Import Candidate</button>
            </div>
        </header>

        <main class="content-body">
            <!-- Recruitment Target Directive Banner -->
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center;">
                <i class="fa-solid fa-id-card-clip text-primary" style="font-size:2rem;"></i>
                <div style="font-size:0.85rem; color:#1e40af;">
                    <strong>Mandatory Staffing Plan (Assisted by "Maharah" Platform):</strong>
                    Required to hire <strong>2 Marketing Specialists</strong> and <strong>2 Customer Service Representatives</strong> (female candidates preferred).
                    Prevailing market benchmark salary for these professional roles is <strong>approximately 7,000 SAR / month</strong>.
                </div>
            </div>

            <!-- Target Progress Cards -->
            <div class="stats-grid">
                <!-- Marketing Positions Card -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Marketing Specialists (Target: 2)</span>
                        <div class="stat-card-icon" style="background:#ccfbf1; color:#0f766e;"><i class="fa-solid fa-bullhorn"></i></div>
                    </div>
                    <div class="stat-card-value"><?= $marketingFilled ?> <span style="font-size:0.9rem; color:#64748b;">/ 2 Hired/Offered</span></div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= ($marketingFilled/2)*100 ?>%; background:#0f766e;"></div>
                    </div>
                    <div class="stat-card-meta">
                        <span class="badge badge-success"><i class="fa-solid fa-coins"></i> Budget: ~7,000 SAR</span>
                        <span class="badge badge-info">Female Preferred</span>
                    </div>
                </div>

                <!-- Customer Service Card -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Customer Service Reps (Target: 2)</span>
                        <div class="stat-card-icon" style="background:#d1fae5; color:#047857;"><i class="fa-solid fa-headset"></i></div>
                    </div>
                    <div class="stat-card-value"><?= $csFilled ?> <span style="font-size:0.9rem; color:#64748b;">/ 2 Hired/Offered</span></div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= ($csFilled/2)*100 ?>%; background:#10b981;"></div>
                    </div>
                    <div class="stat-card-meta">
                        <span class="badge badge-success"><i class="fa-solid fa-coins"></i> Budget: ~7,000 SAR</span>
                        <span class="badge badge-info">Female Preferred</span>
                    </div>
                </div>

                <!-- Sourcing Platform Card -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <span class="stat-card-title">Sourcing Channel Partner</span>
                        <div class="stat-card-icon" style="background:#fef3c7; color:#b45309;"><i class="fa-solid fa-handshake"></i></div>
                    </div>
                    <div class="stat-card-value" style="font-size:1.4rem;">Maharah Platform</div>
                    <div class="stat-card-meta">
                        <span class="badge badge-warning"><i class="fa-solid fa-link"></i> Active Talent Sourcing API</span>
                    </div>
                    <div style="font-size:0.75rem; color:#64748b; margin-top:0.35rem;">National Human Resource & Manpower Pool</div>
                </div>
            </div>

            <!-- Candidates Pipeline Table -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-user-group text-primary"></i> Shortlisted Candidates from Maharah Platform</h3>
                    <div class="panel-actions">
                        <span class="badge badge-success"><?= count($candidates) ?> Profiles in Pipeline</span>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Candidate Name</th>
                                    <th>Role Applied</th>
                                    <th>Gender</th>
                                    <th>Expected Salary</th>
                                    <th>Source</th>
                                    <th>Qualifications & Experience</th>
                                    <th>Hiring Stage</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $c): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($c['candidate_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($c['role'] === 'Marketing Specialist') ? 'badge-info' : 'badge-success' ?>">
                                            <?= htmlspecialchars($c['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><i class="fa-solid fa-venus"></i> <?= $c['gender'] ?></span>
                                    </td>
                                    <td>
                                        <strong style="color:#0f766e;"><?= number_format($c['expected_salary']) ?> SAR</strong>
                                        <?php if ($c['expected_salary'] <= 7000): ?>
                                            <span style="font-size:0.65rem; color:#059669; font-weight:700;"><i class="fa-solid fa-check"></i> Benchmark</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning"><i class="fa-solid fa-star"></i> <?= $c['platform_source'] ?></span>
                                    </td>
                                    <td style="max-width:280px; font-size:0.8rem;"><?= htmlspecialchars($c['qualifications']) ?></td>
                                    <td>
                                        <?php
                                            $stClass = 'badge-info';
                                            if ($c['interview_status'] === 'Hired') $stClass = 'badge-success';
                                            if ($c['interview_status'] === 'Offered') $stClass = 'badge-warning';
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= $c['interview_status'] ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline btn-sm" onclick="updateCandidateStatus(<?= $c['id'] ?>, '<?= $c['candidate_name'] ?>', '<?= $c['interview_status'] ?>')">
                                            <i class="fa-solid fa-pen-to-square"></i> Status
                                        </button>
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

<!-- Add Candidate Modal -->
<div class="modal-overlay" id="candidateModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-user-plus text-primary"></i> Add Candidate from Maharah Platform</h3>
            <button class="btn-icon" onclick="closeCandidateModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Candidate Full Name:</label>
                <input type="text" id="candName" class="form-control" placeholder="e.g. Noura Saad Al-Qahtani">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Position / Role:</label>
                    <select id="candRole" class="form-control">
                        <option value="Marketing Specialist">Marketing Specialist (تسويق)</option>
                        <option value="Customer Service Representative">Customer Service Representative (خدمة عملاء)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender (Preference: Female):</label>
                    <select id="candGender" class="form-control">
                        <option value="Female">Female (أنثى - Preferred)</option>
                        <option value="Male">Male (ذكر)</option>
                    </select>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Expected Salary (Benchmark ~7,000 SAR):</label>
                    <input type="number" id="candSalary" class="form-control" value="7000">
                </div>
                <div class="form-group">
                    <label class="form-label">Sourcing Channel:</label>
                    <input type="text" id="candSource" class="form-control" value="Maharah Platform" readonly>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Qualifications & Experience:</label>
                <textarea id="candQualifications" class="form-control" rows="2" placeholder="Degrees, previous CRM/marketing experience, languages..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Initial Interview Stage:</label>
                <select id="candStatus" class="form-control">
                    <option value="Screened">Screened / Shortlisted</option>
                    <option value="Interview Scheduled">Interview Scheduled</option>
                    <option value="Offered">Offer Extended (7,000 SAR)</option>
                    <option value="Hired">Contract Signed / Hired</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeCandidateModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitNewCandidate()"><i class="fa-solid fa-check"></i> Add Candidate</button>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal-dialog" style="max-width:380px;">
        <div class="modal-header">
            <h3 class="modal-title">Update Candidate Status</h3>
            <button class="btn-icon" onclick="closeStatusModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size:0.85rem; color:#475569;">Candidate: <strong id="statusCandName"></strong></p>
            <div class="form-group">
                <label class="form-label">New Status:</label>
                <select id="newStatusSelect" class="form-control">
                    <option value="Screened">Screened</option>
                    <option value="Interview Scheduled">Interview Scheduled</option>
                    <option value="Offered">Offer Extended (7,000 SAR)</option>
                    <option value="Hired">Contract Signed / Hired</option>
                    <option value="Rejected">Declined / Not Selected</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Evaluation Notes:</label>
                <input type="text" id="statusNotes" class="form-control" placeholder="Interview score, joining date, etc.">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeStatusModal()">Cancel</button>
            <button class="btn btn-primary" onclick="confirmStatusUpdate()"><i class="fa-solid fa-save"></i> Save</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
let activeCandidateId = null;
function openAddCandidateModal() {
    document.getElementById('candidateModal').classList.add('active');
}
function closeCandidateModal() {
    document.getElementById('candidateModal').classList.remove('active');
}
async function submitNewCandidate() {
    const name = document.getElementById('candName').value;
    const role = document.getElementById('candRole').value;
    const gender = document.getElementById('candGender').value;
    const salary = parseFloat(document.getElementById('candSalary').value);
    const source = document.getElementById('candSource').value;
    const quals = document.getElementById('candQualifications').value;
    const status = document.getElementById('candStatus').value;

    if (!name) {
        alert("Please enter candidate name.");
        return;
    }

    try {
        const res = await fetch('../api/hr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add_candidate',
                candidate_name: name,
                role: role,
                gender: gender,
                expected_salary: salary,
                platform_source: source,
                qualifications: quals,
                interview_status: status
            })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    } catch (e) {
        alert("Error saving candidate.");
    }
}

function updateCandidateStatus(id, name, current) {
    activeCandidateId = id;
    document.getElementById('statusCandName').innerText = name;
    document.getElementById('newStatusSelect').value = current;
    document.getElementById('statusModal').classList.add('active');
}
function closeStatusModal() {
    document.getElementById('statusModal').classList.remove('active');
}
async function confirmStatusUpdate() {
    const newStatus = document.getElementById('newStatusSelect').value;
    const notes = document.getElementById('statusNotes').value;

    try {
        const res = await fetch('../api/hr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_status',
                id: activeCandidateId,
                interview_status: newStatus,
                notes: notes
            })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        }
    } catch (e) {
        alert("Failed to update status.");
    }
}
</script>
</body>
</html>
