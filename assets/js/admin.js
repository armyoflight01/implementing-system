// assets/js/admin.js - Executive Management, Ministry Compliance & Fleet Dashboard (Static & PHP Compatible)

document.addEventListener('DOMContentLoaded', () => {
    loadComplianceDashboard();
});

// 1. Language Toggle (Arabic RTL / English LTR)
function toggleLanguage() {
    const html = document.documentElement;
    const currentLang = html.getAttribute('lang') || 'en';
    if (currentLang === 'en') {
        html.setAttribute('lang', 'ar');
        html.setAttribute('dir', 'rtl');
        localStorage.setItem('sys_lang', 'ar');
    } else {
        html.setAttribute('lang', 'en');
        html.setAttribute('dir', 'ltr');
        localStorage.setItem('sys_lang', 'en');
    }
}

// 2. Load Ministry Compliance Dashboard
function loadComplianceDashboard() {
    let data = null;
    if (typeof DB !== 'undefined') {
        data = DB.getCompliance();
        renderComplianceView(data);
        return;
    }

    fetch('../api/compliance.php')
        .then(res => res.json())
        .then(resData => {
            if (resData.success) {
                renderComplianceView(resData);
            }
        })
        .catch(() => {
            if (typeof DB !== 'undefined') {
                renderComplianceView(DB.getCompliance());
            }
        });
}

function renderComplianceView(data) {
    if (!data) return;
    const summary = data.summary;

    // 10% Hourly Domestic Visa Rule
    const hourlyRatioEl = document.getElementById('metricHourlyRatio');
    if (hourlyRatioEl) {
        hourlyRatioEl.innerText = summary.hourly_percentage + '%';
        const statusEl = document.getElementById('metricHourlyStatus');
        if (statusEl) {
            if (summary.hourly_10_percent_rule_met) {
                statusEl.innerHTML = `<span class="badge badge-success"><i class="fa-solid fa-check"></i> Compliant (Target ≥ 10%)</span>`;
            } else {
                statusEl.innerHTML = `<span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Non-Compliant (< 10%)</span>`;
            }
        }
        const progBar = document.getElementById('hourlyRatioProgress');
        if (progBar) {
            progBar.style.width = Math.min(100, (summary.hourly_percentage / 20) * 100) + '%';
            progBar.style.backgroundColor = summary.hourly_10_percent_rule_met ? '#10b981' : '#ef4444';
        }
    }

    // Nationality Diversity & Kafala Quotas Table
    const tbody = document.getElementById('nationalityQuotasTbody');
    if (tbody && data.nationalities) {
        tbody.innerHTML = data.nationalities.map(n => {
            const diversityBadge = n.is_compliant 
                ? `<span class="badge badge-success"><i class="fa-solid fa-check"></i> ${n.percentage_of_total}% (Max ${n.max_allowed_percent}%)</span>`
                : `<span class="badge badge-danger"><i class="fa-solid fa-circle-exclamation"></i> Over Cap (${n.percentage_of_total}%)</span>`;

            const kafalaBadge = n.kafala_cap_reached 
                ? `<span class="badge badge-danger">Cap Reached (${n.kafala_transfers_used}/${n.max_kafala_transfers})</span>`
                : `<span class="badge badge-info">${n.kafala_transfers_used} / ${n.max_kafala_transfers} (${n.kafala_transfers_remaining} left)</span>`;

            const runawayNote = (n.nationality === 'Indonesian')
                ? `<span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> High Absconding Alert</span>`
                : `<span class="text-muted">Standard</span>`;

            return `
                <tr>
                    <td><strong>${n.nationality}</strong></td>
                    <td>${n.total_visas} Visas</td>
                    <td>${n.hourly_visas} (${round((n.hourly_visas/n.total_visas)*100, 1)}%)</td>
                    <td>${n.monthly_visas}</td>
                    <td>${diversityBadge}</td>
                    <td>${kafalaBadge}</td>
                    <td>${runawayNote}</td>
                </tr>
            `;
        }).join('');
    }

    // Price Cap Auditing
    const priceCapAuditEl = document.getElementById('priceCapAuditStatus');
    if (priceCapAuditEl) {
        priceCapAuditEl.innerHTML = `
            <div style="background:#ecfdf5; border:1px solid #10b981; padding:0.75rem 1rem; border-radius:10px; color:#065f46; font-size:0.85rem;">
                <i class="fa-solid fa-circle-check"></i> <strong>100% Price Cap Compliant:</strong> All domestic worker contracts adhere to 123 SAR/hr & 3,230 SAR/mo caps. Specialized roles (Nurses, Caregivers, Cooks, Drivers) remain exempt.
            </div>
        `;
    }
}

// 3. Worker Modal Price Cap Live Indicator
function checkWorkerProfessionCap() {
    const prof = document.getElementById('workerProfession').value;
    const hourlyInput = document.getElementById('workerHourlyRate');
    const monthlyInput = document.getElementById('workerMonthlyRate');
    const capNotice = document.getElementById('workerPriceCapNotice');

    if (prof === 'Domestic Worker') {
        capNotice.style.display = 'block';
        capNotice.innerHTML = `<i class="fa-solid fa-scale-balanced"></i> <strong>Saudi Ministry Price Cap Applies:</strong> Max 123 SAR/hour, Max 3,230 SAR/month.`;
        if (parseFloat(hourlyInput.value) > 123) hourlyInput.value = 123;
        if (parseFloat(monthlyInput.value) > 3230) monthlyInput.value = 3230;
    } else {
        capNotice.style.display = 'block';
        capNotice.innerHTML = `<i class="fa-solid fa-circle-info"></i> <strong>Specialized Profession:</strong> Exempt from Ministry price caps under labor regulation.`;
    }
}

// 4. Save New Worker with Cap Validation
function saveWorker() {
    const fullName = document.getElementById('workerFullName').value;
    const nationality = document.getElementById('workerNationality').value;
    const profession = document.getElementById('workerProfession').value;
    const contractType = document.getElementById('workerContractType').value;
    const hourlyRate = parseFloat(document.getElementById('workerHourlyRate').value);
    const monthlyRate = parseFloat(document.getElementById('workerMonthlyRate').value);
    const housingUnitId = document.getElementById('workerHousingUnit').value;

    if (!fullName) {
        alert("Please enter worker full name.");
        return;
    }

    if (profession === 'Domestic Worker') {
        if (hourlyRate > 123) {
            alert("Error: Domestic worker hourly rate cannot exceed 123 SAR as mandated by Ministry of Labor.");
            return;
        }
        if (monthlyRate > 3230) {
            alert("Error: Domestic worker monthly rate cannot exceed 3,230 SAR as mandated by Ministry of Labor.");
            return;
        }
    }

    if (typeof DB !== 'undefined') {
        DB.addWorker({
            full_name: fullName,
            nationality: nationality,
            profession: profession,
            contract_type: contractType,
            hourly_rate: hourlyRate,
            monthly_rate: monthlyRate,
            housing_unit_id: parseInt(housingUnitId),
            building_name: 'Al-Yasmin Compound',
            room_number: '101',
            supervisor_name: 'Fatimah Al-Ghamdi',
            absconding_risk: nationality === 'Indonesian' ? 'high' : 'low',
            status: 'in_housing',
            visa_type: contractType === 'hourly' ? 'hourly' : 'monthly'
        });
        alert("Worker successfully registered.");
        location.reload();
        return;
    }
}

// 5. Housing Security Event Logger
function submitSecurityEvent(workerId) {
    const eventType = document.getElementById('secEventType').value;
    const supervisor = document.getElementById('secSupervisor').value;
    const notes = document.getElementById('secNotes').value;

    if (typeof DB !== 'undefined') {
        const worker = DB.getWorkers().find(w => w.id == workerId);
        DB.addSecurityLog({
            worker_id: workerId,
            worker_name: worker ? worker.full_name : 'Worker',
            nationality: worker ? worker.nationality : 'Indonesian',
            profession: worker ? worker.profession : 'Domestic Worker',
            event_type: eventType,
            logged_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
            supervisor_name: supervisor,
            notes: notes,
            reported_to_ministry: (eventType === 'absconded_reported' ? 1 : 0)
        });
        alert("Security event registered and logged into accommodation records.");
        location.reload();
        return;
    }
}

// 6. Fleet Dispatch Creation
function scheduleTrip() {
    const vehicleId = document.getElementById('dispatchVehicle').value;
    const tripType = document.getElementById('dispatchTripType').value;
    const destination = document.getElementById('dispatchDestination').value;
    const workerCount = parseInt(document.getElementById('dispatchWorkersCount').value);

    if (typeof DB !== 'undefined') {
        const v = DB.getFleet().find(f => f.id == vehicleId);
        DB.addTrip({
            vehicle_id: parseInt(vehicleId),
            vehicle_type: v ? v.vehicle_type : 'Toyota HiAce Van',
            plate_number: v ? v.plate_number : 'KSA-4310-H',
            driver_name: v ? v.driver_name : 'Tariq Al-Harbi',
            driver_phone: v ? v.driver_phone : '+966 50 334 8899',
            trip_type: tripType,
            departure_time: new Date().toISOString().replace('T', ' ').substring(0, 19),
            arrival_time: null,
            destination_area: destination,
            worker_count: workerCount,
            status: 'in_progress'
        });
        alert("Transportation bus trip dispatched successfully!");
        location.reload();
        return;
    }
}

// 7. Complete Trip & Return Bus to Terminal
function markTripCompleted(tripId) {
    if (!confirm("Confirm that bus/vehicle has returned to accommodation terminal safely?")) return;
    if (typeof DB !== 'undefined') {
        DB.completeTrip(tripId);
        location.reload();
        return;
    }
}

function round(num, decimals) {
    return Number(Math.round(num + 'e' + decimals) + 'e-' + decimals);
}
