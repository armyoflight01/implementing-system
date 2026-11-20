// assets/js/admin.js - Executive Management, Ministry Compliance & Fleet Dashboard

document.addEventListener('DOMContentLoaded', () => {
    // Check current page and init corresponding logic
    const path = window.location.pathname;
    if (path.includes('index.php') || path.endsWith('/admin/') || path.endsWith('/admin')) {
        loadComplianceDashboard();
    }
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
async function loadComplianceDashboard() {
    try {
        const res = await fetch('../api/compliance.php');
        const data = await res.json();
        if (!data.success) return;

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
        if (tbody) {
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
            if (summary.price_cap_rule_met) {
                priceCapAuditEl.innerHTML = `
                    <div style="background:#ecfdf5; border:1px solid #10b981; padding:0.75rem 1rem; border-radius:10px; color:#065f46; font-size:0.85rem;">
                        <i class="fa-solid fa-circle-check"></i> <strong>100% Price Cap Compliant:</strong> All domestic worker contracts adhere to 123 SAR/hr & 3,230 SAR/mo caps. Specialized roles (Nurses, Caregivers, Cooks, Drivers) remain exempt.
                    </div>
                `;
            } else {
                priceCapAuditEl.innerHTML = `
                    <div style="background:#fef2f2; border:1px solid #ef4444; padding:0.75rem 1rem; border-radius:10px; color:#991b1b; font-size:0.85rem;">
                        <i class="fa-solid fa-circle-xmark"></i> <strong>Price Cap Non-Compliance:</strong> Violations detected. Immediate price adjustments required.
                    </div>
                `;
            }
        }
    } catch (e) {
        console.error("Failed to load compliance data", e);
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
async function saveWorker() {
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

    try {
        const res = await fetch('../api/workers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                full_name: fullName,
                nationality: nationality,
                profession: profession,
                contract_type: contractType,
                hourly_rate: hourlyRate,
                monthly_rate: monthlyRate,
                housing_unit_id: housingUnitId
            })
        });

        const data = await res.json();
        if (data.success) {
            alert("Worker successfully registered.");
            location.reload();
        } else {
            alert("Registration failed: " + data.message);
        }
    } catch (e) {
        alert("Server error registering worker.");
    }
}

// 5. Housing Security Event Logger (Preventing Runaway & Curfew Checks)
async function submitSecurityEvent(workerId) {
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
                notes: notes
            })
        });

        const data = await res.json();
        if (data.success) {
            alert("Security event registered and logged into accommodation records.");
            location.reload();
        }
    } catch (e) {
        alert("Error logging security event.");
    }
}

// 6. Fleet Dispatch Creation
async function scheduleTrip() {
    const vehicleId = document.getElementById('dispatchVehicle').value;
    const tripType = document.getElementById('dispatchTripType').value;
    const destination = document.getElementById('dispatchDestination').value;
    const workerCount = document.getElementById('dispatchWorkersCount').value;

    try {
        const res = await fetch('../api/fleet.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'create_trip',
                vehicle_id: vehicleId,
                trip_type: tripType,
                destination_area: destination,
                worker_count: workerCount
            })
        });

        const data = await res.json();
        if (data.success) {
            alert("Transportation bus trip dispatched successfully!");
            location.reload();
        }
    } catch (e) {
        alert("Error scheduling fleet dispatch.");
    }
}

// 7. Complete Trip & Return Bus to Terminal
async function markTripCompleted(tripId) {
    if (!confirm("Confirm that bus/vehicle has returned to accommodation terminal safely?")) return;
    try {
        const res = await fetch('../api/fleet.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'complete_trip',
                trip_id: tripId
            })
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        }
    } catch (e) {
        alert("Error updating trip status.");
    }
}

// Helper Rounding
function round(num, decimals) {
    return Number(Math.round(num + 'e' + decimals) + 'e-' + decimals);
}
