// assets/js/app.js - Client Mobile Application Logic & Payment Integrations

let currentWorkers = [];
let currentPackages = [];
let selectedBookingTarget = null;
let activeLiveLog = null;
let timerInterval = null;

document.addEventListener('DOMContentLoaded', () => {
    loadPackages();
    loadWorkers();
    checkActiveTracking();
    checkKafalaEligibility();
});

// 1. Load Packages from API
async function loadPackages() {
    try {
        const res = await fetch('../api/bookings.php?action=packages');
        const data = await res.json();
        if (data.success) {
            currentPackages = data.data;
            renderFeaturedPromo();
        }
    } catch (e) {
        console.error("Failed to load packages", e);
    }
}

// 2. Render Featured 3-Month Package (11,000 SAR) with Kafala eligibility
function renderFeaturedPromo() {
    const promo = currentPackages.find(p => p.kafala_eligible == 1 && p.duration_value == 3);
    const container = document.getElementById('featuredPromoContainer');
    if (!container || !promo) return;

    container.innerHTML = `
        <div class="promo-card">
            <span class="promo-badge"><i class="fa-solid fa-crown"></i> Exclusive Ministry Compliant Offer</span>
            <h3 class="promo-title">${promo.name}</h3>
            <div class="promo-price">${Number(promo.price_sar).toLocaleString()} SAR <small>/ 3 Months</small></div>
            <p class="promo-features">${promo.description}</p>
            <div class="kafala-tag mb-3">
                <i class="fa-solid fa-id-card-clip"></i>
                <span>Automatic Sponsorship Transfer (Kafala) Eligibility upon package maturity</span>
            </div>
            <button class="btn btn-primary w-100" style="background:#fbbf24; color:#78350f; font-weight:800; border:none;" onclick="initiatePackageCheckout(${promo.id})">
                <i class="fa-solid fa-bolt"></i> Book 3-Month Package (11,000 SAR)
            </button>
        </div>
    `;
}

// 3. Load Workers with Live Filters
async function loadWorkers(category = 'all') {
    const container = document.getElementById('workersListContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div style="text-align:center; padding:2rem; color:#64748b;">
            <i class="fa-solid fa-circle-notch fa-spin fa-2x"></i>
            <p style="margin-top:0.5rem;">Fetching available domestic labor & specialized staff...</p>
        </div>
    `;

    try {
        let url = '../api/workers.php';
        if (category === 'hourly') url += '?contract_type=hourly';
        else if (category === 'monthly') url += '?contract_type=monthly';
        else if (category === 'domestic') url += '?profession=Domestic+Worker';
        else if (category === 'specialized') url += '?profession=Nurse';

        const res = await fetch(url);
        const data = await res.json();
        if (data.success) {
            currentWorkers = data.data;
            renderWorkers(currentWorkers);
        }
    } catch (e) {
        container.innerHTML = `<p style="color:#ef4444; padding:1rem;">Failed to load workers list.</p>`;
    }
}

// 4. Render Workers Cards
function renderWorkers(workers) {
    const container = document.getElementById('workersListContainer');
    if (!container) return;

    if (workers.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding:2rem; color:#64748b;">No workers currently available under this category.</div>`;
        return;
    }

    container.innerHTML = workers.map(w => {
        const isDomestic = (w.profession === 'Domestic Worker');
        const capBadge = isDomestic 
            ? `<span class="price-cap-badge"><i class="fa-solid fa-shield-halved"></i> Capped: Max 123/hr & 3,230/mo</span>`
            : `<span class="uncapped-badge"><i class="fa-solid fa-star"></i> Uncapped Specialized Role</span>`;
        
        let riskBadge = '';
        if (w.absconding_risk === 'high') {
            riskBadge = `<span class="absconding-risk-indicator risk-high"><i class="fa-solid fa-triangle-exclamation"></i> Monitored Security</span>`;
        }

        return `
            <div class="app-card">
                <div class="app-card-header">
                    <div style="display:flex; gap:0.85rem; align-items:center;">
                        <div class="worker-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="worker-info">
                            <h4>${w.full_name}</h4>
                            <div class="worker-profession">${w.profession} • <span class="worker-nationality">${w.nationality}</span></div>
                            <div style="margin-top:0.35rem; display:flex; gap:0.4rem; flex-wrap:wrap;">
                                ${capBadge}
                                ${riskBadge}
                            </div>
                        </div>
                    </div>
                    <div class="card-pricing">
                        <div class="price-value">${w.contract_type === 'hourly' ? w.hourly_rate + ' <small>SAR/hr</small>' : w.monthly_rate + ' <small>SAR/mo</small>'}</div>
                        <span style="font-size:0.7rem; color:#64748b; text-transform:capitalize;">${w.contract_type} Contract</span>
                    </div>
                </div>

                <div style="font-size:0.75rem; color:#475569; background:#f8fafc; padding:0.6rem 0.8rem; border-radius:10px;">
                    <i class="fa-solid fa-bus text-primary"></i> <strong>Transportation:</strong> Free company bus/van pickup & drop-off included.
                </div>

                <div class="app-card-footer">
                    <div style="font-size:0.75rem; color:#059669; font-weight:600;">
                        <i class="fa-solid fa-circle-check"></i> Available & Medical Cleared
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="openWorkerBookingModal(${w.id})">
                        <i class="fa-solid fa-calendar-check"></i> Book Now
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// 5. Category Tab Click Handler
function filterCategory(btn, cat) {
    document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadWorkers(cat);
}

// 6. Initiate Checkout for 3-Month Package
function initiatePackageCheckout(packageId) {
    const pkg = currentPackages.find(p => p.id == packageId);
    if (!pkg) return;

    selectedBookingTarget = {
        type: 'package',
        package_id: pkg.id,
        worker_id: 2, // Default Indonesian worker for package
        name: pkg.name,
        amount: pkg.price_sar,
        duration: '3 Months (Kafala Transfer Eligible)',
        service_type: 'monthly'
    };

    openPaymentSheet();
}

// 7. Initiate Custom Worker Booking
function openWorkerBookingModal(workerId) {
    const worker = currentWorkers.find(w => w.id == workerId);
    if (!worker) return;

    const isHourly = (worker.contract_type === 'hourly');
    const amount = isHourly ? (worker.hourly_rate * 4) : worker.monthly_rate;

    selectedBookingTarget = {
        type: 'worker',
        worker_id: worker.id,
        package_id: null,
        name: `${worker.full_name} (${worker.profession})`,
        amount: amount,
        duration: isHourly ? '4 Hours Flexible Cleaning' : '1 Month Contract',
        service_type: isHourly ? 'hourly' : 'monthly',
        hours_allocated: isHourly ? 4 : 0
    };

    openPaymentSheet();
}

// 8. Open Payment Sheet (Apple Pay & Google Pay)
function openPaymentSheet() {
    if (!selectedBookingTarget) return;

    document.getElementById('summaryItemName').innerText = selectedBookingTarget.name;
    document.getElementById('summaryDuration').innerText = selectedBookingTarget.duration;
    document.getElementById('summaryTotal').innerText = Number(selectedBookingTarget.amount).toLocaleString() + ' SAR';

    // Show notice if Kafala eligible
    const kafalaRow = document.getElementById('summaryKafalaNotice');
    if (kafalaRow) {
        if (selectedBookingTarget.type === 'package' && selectedBookingTarget.package_id == 1) {
            kafalaRow.style.display = 'flex';
        } else {
            kafalaRow.style.display = 'none';
        }
    }

    document.getElementById('paymentSheetOverlay').classList.add('active');
}

function closePaymentSheet() {
    document.getElementById('paymentSheetOverlay').classList.remove('active');
}

// 9. Execute Payment via Apple Pay / Google Pay
async function processPayment(method) {
    if (!selectedBookingTarget) return;

    const clientName = document.getElementById('checkoutClientName').value || 'Mohammad Al-Otaibi';
    const clientPhone = document.getElementById('checkoutClientPhone').value || '+966 50 123 4567';
    const clientAddress = document.getElementById('checkoutClientAddress').value || 'Al-Malqa, Riyadh';

    // Show animated loading simulation
    const btn = event.currentTarget;
    const originalContent = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Processing with ${method === 'apple_pay' ? 'Apple Pay' : 'Google Pay'}...`;
    btn.disabled = true;

    try {
        const payload = {
            client_name: clientName,
            client_phone: clientPhone,
            client_address: clientAddress,
            worker_id: selectedBookingTarget.worker_id,
            package_id: selectedBookingTarget.package_id,
            service_type: selectedBookingTarget.service_type,
            hours_allocated: selectedBookingTarget.hours_allocated || 0,
            payment_method: method
        };

        const res = await fetch('../api/bookings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        btn.innerHTML = originalContent;
        btn.disabled = false;

        if (data.success) {
            closePaymentSheet();
            showPaymentSuccessModal(data, method);
            checkActiveTracking();
        } else {
            alert("Payment Error: " + data.message);
        }
    } catch (e) {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        alert("Failed to connect to payment server.");
    }
}

// 10. Payment Success Modal with Apple Pay / GPay confirmation
function showPaymentSuccessModal(data, method) {
    const modal = document.getElementById('paymentSuccessModal');
    if (!modal) return;

    document.getElementById('successBookingCode').innerText = data.booking_code;
    document.getElementById('successAmount').innerText = Number(data.total_amount).toLocaleString() + ' SAR';
    document.getElementById('successPaymentRef').innerText = data.payment_ref;
    document.getElementById('successMethodName').innerText = (method === 'apple_pay') ? 'Apple Pay' : 'Google Pay';

    const kafalaAlert = document.getElementById('successKafalaAlert');
    if (kafalaAlert) {
        if (data.kafala_eligible) {
            kafalaAlert.style.display = 'block';
            kafalaAlert.innerHTML = `<strong><i class="fa-solid fa-id-card-clip"></i> Kafala Transfer Notice:</strong> ${data.kafala_notice}`;
        } else {
            kafalaAlert.style.display = 'none';
        }
    }

    modal.classList.add('active');
}

function closeSuccessModal() {
    const modal = document.getElementById('paymentSuccessModal');
    if (modal) modal.classList.remove('active');
}

// 11. Check Active Real-Time Service Tracking
async function checkActiveTracking() {
    try {
        const res = await fetch('../api/time-tracker.php?action=live_active');
        const data = await res.json();
        const panel = document.getElementById('liveTrackingContainer');
        if (!panel) return;

        if (data.success && data.data.length > 0) {
            activeLiveLog = data.data[0];
            panel.style.display = 'block';
            document.getElementById('trackingWorkerName').innerText = activeLiveLog.worker_name;
            document.getElementById('trackingProfession').innerText = `${activeLiveLog.profession} (${activeLiveLog.nationality})`;
            document.getElementById('trackingAddress').innerText = activeLiveLog.client_address;
            document.getElementById('trackingRemaining').innerText = activeLiveLog.remaining_hours + ' hrs remaining';

            // Start live timer
            startLiveTimer(activeLiveLog.check_in_time);
        } else {
            panel.style.display = 'none';
            if (timerInterval) clearInterval(timerInterval);
        }
    } catch (e) {
        console.error("Tracking check failed", e);
    }
}

function startLiveTimer(checkInTimeStr) {
    if (timerInterval) clearInterval(timerInterval);
    const checkInDate = new Date(checkInTimeStr.replace(' ', 'T')).getTime();

    function update() {
        const now = new Date().getTime();
        const diffMs = Math.max(0, now - checkInDate);
        const hours = Math.floor(diffMs / 3600000);
        const minutes = Math.floor((diffMs % 3600000) / 60000);
        const seconds = Math.floor((diffMs % 60000) / 1000);

        const pad = (n) => String(n).padStart(2, '0');
        const el = document.getElementById('liveTimerDisplay');
        if (el) {
            el.innerText = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }
    }

    update();
    timerInterval = setInterval(update, 1000);
}

// 12. Check and Display Kafala Notification for Matured 3-Month Packages
async function checkKafalaEligibility() {
    try {
        const res = await fetch('../api/bookings.php?action=check_kafala_notifications');
        const data = await res.json();
        const banner = document.getElementById('kafalaNotificationBanner');
        if (!banner) return;

        if (data.success && data.data.length > 0) {
            const matured = data.data[0];
            banner.style.display = 'block';
            banner.innerHTML = `
                <div style="background:linear-gradient(135deg, #1e3a8a, #2563eb); color:white; padding:1rem; border-radius:16px; margin:1rem; box-shadow:0 6px 15px rgba(37,99,235,0.3);">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; gap:0.6rem; align-items:center;">
                            <i class="fa-solid fa-bell text-warning fa-lg"></i>
                            <div>
                                <h4 style="font-size:0.9rem; font-weight:700;">Eligible for Sponsorship Transfer (Kafala)!</h4>
                                <p style="font-size:0.75rem; opacity:0.9;">Your 3-month package with <strong>${matured.worker_name} (${matured.nationality})</strong> has concluded.</p>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-primary" style="background:#fbbf24; color:#78350f; font-weight:700;" onclick="openKafalaApplication(${matured.id})">
                            Apply Transfer
                        </button>
                    </div>
                </div>
            `;
        } else {
            banner.style.display = 'none';
        }
    } catch (e) {
        console.error("Kafala check failed", e);
    }
}

// 13. Open Kafala Application Modal & Enforce Ministry Quota
async function openKafalaApplication(bookingId) {
    try {
        const res = await fetch(`../api/kafala.php?action=check_booking_eligibility&booking_id=${bookingId}`);
        const data = await res.json();

        if (!data.success) {
            alert(data.message);
            return;
        }

        const modal = document.getElementById('kafalaModal');
        if (!modal) return;

        document.getElementById('kafalaWorkerName').innerText = data.booking.worker_name;
        document.getElementById('kafalaNationality').innerText = data.nationality;
        document.getElementById('kafalaQuotaRemaining').innerText = data.remaining_transfers_allowed + ' transfers remaining in Ministry quota';

        const warningDiv = document.getElementById('kafalaQuotaWarning');
        const submitBtn = document.getElementById('btnSubmitKafala');

        if (data.quota_blocked) {
            warningDiv.style.display = 'block';
            warningDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> <strong>Ministry Quota Exceeded:</strong> No additional sponsorship transfers are currently permitted for ${data.nationality} workers under Ministry rules.`;
            submitBtn.disabled = true;
        } else {
            warningDiv.style.display = 'none';
            submitBtn.disabled = false;
        }

        modal.dataset.bookingId = bookingId;
        modal.classList.add('active');
    } catch (e) {
        alert("Failed to load Kafala eligibility data.");
    }
}

function closeKafalaModal() {
    const modal = document.getElementById('kafalaModal');
    if (modal) modal.classList.remove('active');
}

async function submitKafalaTransfer() {
    const modal = document.getElementById('kafalaModal');
    const bookingId = modal.dataset.bookingId;
    const nationalId = document.getElementById('kafalaNationalId').value;

    if (!nationalId) {
        alert("Please enter Saudi National ID / Resident Iqama number.");
        return;
    }

    try {
        const res = await fetch('../api/kafala.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'request_transfer',
                booking_id: bookingId,
                client_id_number: nationalId
            })
        });

        const data = await res.json();
        if (data.success) {
            alert("Success: " + data.message);
            closeKafalaModal();
            checkKafalaEligibility();
        } else {
            alert("Ministry Quota Rule Warning: " + data.message);
        }
    } catch (e) {
        alert("Error submitting Kafala transfer request.");
    }
}
