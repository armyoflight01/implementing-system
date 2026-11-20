// assets/js/app.js - Client Mobile Application Logic & Payment Integrations (Static & PHP Compatible)

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

// 1. Load Packages
async function loadPackages() {
    if (typeof DB !== 'undefined') {
        currentPackages = DB.getPackages();
        renderFeaturedPromo();
        return;
    }
    try {
        const res = await fetch('../api/bookings.php?action=packages');
        const data = await res.json();
        if (data.success) {
            currentPackages = data.data;
            renderFeaturedPromo();
        }
    } catch (e) {
        if (typeof DB !== 'undefined') {
            currentPackages = DB.getPackages();
            renderFeaturedPromo();
        }
    }
}

// 2. Render Featured 3-Month Package (11,000 SAR) with Kafala eligibility
function renderFeaturedPromo() {
    const promo = currentPackages.find(p => p.kafala_eligible == 1 && p.duration_value == 3) || currentPackages[0];
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

    if (typeof DB !== 'undefined') {
        let workers = DB.getWorkers();
        if (category === 'hourly') workers = workers.filter(w => w.contract_type === 'hourly' || w.contract_type === 'both');
        else if (category === 'monthly') workers = workers.filter(w => w.contract_type === 'monthly' || w.contract_type === 'both');
        else if (category === 'domestic') workers = workers.filter(w => w.profession === 'Domestic Worker');
        else if (category === 'specialized') workers = workers.filter(w => w.profession !== 'Domestic Worker');
        currentWorkers = workers;
        renderWorkers(currentWorkers);
        return;
    }

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
        if (typeof DB !== 'undefined') {
            currentWorkers = DB.getWorkers();
            renderWorkers(currentWorkers);
        }
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
        worker_id: 2,
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

    const btn = event.currentTarget;
    const originalContent = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Processing with ${method === 'apple_pay' ? 'Apple Pay' : 'Google Pay'}...`;
    btn.disabled = true;

    setTimeout(() => {
        btn.innerHTML = originalContent;
        btn.disabled = false;

        const bookingCode = 'BK-SA-' + Math.floor(10000 + Math.random() * 90000);
        const paymentRef = (method === 'apple_pay' ? 'APL-' : (method === 'google_pay' ? 'GPAY-' : 'MADA-')) + Math.random().toString(36).substring(2, 9).toUpperCase() + '-KSA';
        const isKafala = (selectedBookingTarget.type === 'package' && selectedBookingTarget.package_id == 1);

        const newBooking = {
            booking_code: bookingCode,
            client_name: clientName,
            client_phone: clientPhone,
            client_address: clientAddress,
            worker_id: selectedBookingTarget.worker_id,
            worker_name: selectedBookingTarget.name.split(' (')[0],
            nationality: 'Indonesian',
            profession: selectedBookingTarget.service_type === 'hourly' ? 'Domestic Worker' : 'Domestic Specialist',
            package_id: selectedBookingTarget.package_id,
            package_name: selectedBookingTarget.name,
            service_type: selectedBookingTarget.service_type,
            hours_allocated: selectedBookingTarget.hours_allocated || 0,
            hours_consumed: 0,
            start_date: new Date().toISOString().split('T')[0],
            end_date: isKafala ? '2026-11-20' : new Date().toISOString().split('T')[0],
            total_amount: selectedBookingTarget.amount,
            payment_status: 'paid',
            payment_method: method,
            payment_ref: paymentRef,
            kafala_notified: isKafala ? 1 : 0,
            kafala_status: isKafala ? 'eligible' : 'none',
            status: 'active',
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        if (typeof DB !== 'undefined') {
            DB.addBooking(newBooking);

            // If hourly, create active shift
            if (selectedBookingTarget.service_type === 'hourly') {
                DB.addTimeLog({
                    booking_id: newBooking.id,
                    booking_code: bookingCode,
                    worker_id: selectedBookingTarget.worker_id,
                    worker_name: selectedBookingTarget.name,
                    nationality: 'Indonesian',
                    profession: 'Domestic Worker',
                    client_name: clientName,
                    client_address: clientAddress,
                    service_type: 'hourly',
                    hours_allocated: selectedBookingTarget.hours_allocated || 4.0,
                    check_in_time: new Date().toISOString().replace('T', ' ').substring(0, 19),
                    check_out_time: null,
                    hours_worked: 0,
                    location_lat: 24.7136,
                    location_lng: 46.6753,
                    supervisor_verified: 1,
                    client_signature_token: 'PENDING',
                    notes: 'Shift started via client app.',
                    status: 'active'
                });
            }
        }

        closePaymentSheet();
        showPaymentSuccessModal({
            booking_code: bookingCode,
            total_amount: selectedBookingTarget.amount,
            payment_ref: paymentRef,
            kafala_eligible: isKafala,
            kafala_notice: isKafala ? 'Package includes eligibility for Sponsorship Transfer (Kafala) upon expiration, subject to Ministry nationality caps.' : null
        }, method);

        checkActiveTracking();
        checkKafalaEligibility();
    }, 900);
}

// 10. Payment Success Modal
function showPaymentSuccessModal(data, method) {
    const modal = document.getElementById('paymentSuccessModal');
    if (!modal) return;

    document.getElementById('successBookingCode').innerText = data.booking_code;
    document.getElementById('successAmount').innerText = Number(data.total_amount).toLocaleString() + ' SAR';
    document.getElementById('successPaymentRef').innerText = data.payment_ref;
    document.getElementById('successMethodName').innerText = (method === 'apple_pay') ? 'Apple Pay' : (method === 'google_pay' ? 'Google Pay' : 'Mada');

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
    const panel = document.getElementById('liveTrackingContainer');
    if (!panel) return;

    if (typeof DB !== 'undefined') {
        const activeLogs = DB.getTimeLogs().filter(l => l.status === 'active');
        if (activeLogs.length > 0) {
            activeLiveLog = activeLogs[0];
            panel.style.display = 'block';
            document.getElementById('trackingWorkerName').innerText = activeLiveLog.worker_name;
            document.getElementById('trackingProfession').innerText = `${activeLiveLog.profession} (${activeLiveLog.nationality || 'Verified'})`;
            document.getElementById('trackingAddress').innerText = activeLiveLog.client_address;
            const remaining = (activeLiveLog.hours_allocated > 0) ? (activeLiveLog.hours_allocated - (activeLiveLog.hours_worked || 0)) : 'Monthly';
            document.getElementById('trackingRemaining').innerText = remaining + ' hrs remaining';
            startLiveTimer(activeLiveLog.check_in_time);
            return;
        } else {
            panel.style.display = 'none';
            if (timerInterval) clearInterval(timerInterval);
            return;
        }
    }

    try {
        const res = await fetch('../api/time-tracker.php?action=live_active');
        const data = await res.json();
        if (data.success && data.data.length > 0) {
            activeLiveLog = data.data[0];
            panel.style.display = 'block';
            document.getElementById('trackingWorkerName').innerText = activeLiveLog.worker_name;
            document.getElementById('trackingProfession').innerText = `${activeLiveLog.profession} (${activeLiveLog.nationality})`;
            document.getElementById('trackingAddress').innerText = activeLiveLog.client_address;
            document.getElementById('trackingRemaining').innerText = activeLiveLog.remaining_hours + ' hrs remaining';
            startLiveTimer(activeLiveLog.check_in_time);
        } else {
            panel.style.display = 'none';
            if (timerInterval) clearInterval(timerInterval);
        }
    } catch (e) {
        if (panel) panel.style.display = 'none';
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
    const banner = document.getElementById('kafalaNotificationBanner');
    if (!banner) return;

    let eligible = null;
    if (typeof DB !== 'undefined') {
        const bookings = DB.getBookings();
        eligible = bookings.find(b => b.kafala_status === 'eligible' || b.kafala_notified == 1);
    }

    if (eligible) {
        banner.style.display = 'block';
        banner.innerHTML = `
            <div style="background:linear-gradient(135deg, #1e3a8a, #2563eb); color:white; padding:1rem; border-radius:16px; margin:1rem; box-shadow:0 6px 15px rgba(37,99,235,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; gap:0.6rem; align-items:center;">
                        <i class="fa-solid fa-bell text-warning fa-lg"></i>
                        <div>
                            <h4 style="font-size:0.9rem; font-weight:700;">Eligible for Sponsorship Transfer (Kafala)!</h4>
                            <p style="font-size:0.75rem; opacity:0.9;">Your 3-month package with <strong>${eligible.worker_name} (${eligible.nationality})</strong> has concluded.</p>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-primary" style="background:#fbbf24; color:#78350f; font-weight:700;" onclick="openKafalaApplication(${eligible.id})">
                        Apply Transfer
                    </button>
                </div>
            </div>
        `;
    } else {
        banner.style.display = 'none';
    }
}

// 13. Open Kafala Application Modal & Enforce Ministry Quota
function openKafalaApplication(bookingId) {
    if (typeof DB !== 'undefined') {
        const bookings = DB.getBookings();
        const b = bookings.find(bk => bk.id == bookingId);
        if (!b) return;

        const quotas = DB.getQuotas();
        const q = quotas.find(qt => qt.nationality === b.nationality) || quotas[0];
        const remaining = Math.max(0, q.max_kafala_transfers_allowed - q.kafala_transfers_used);

        const modal = document.getElementById('kafalaModal');
        if (!modal) return;

        document.getElementById('kafalaWorkerName').innerText = b.worker_name;
        document.getElementById('kafalaNationality').innerText = b.nationality;
        document.getElementById('kafalaQuotaRemaining').innerText = remaining + ' transfers remaining in Ministry quota';

        const warningDiv = document.getElementById('kafalaQuotaWarning');
        const submitBtn = document.getElementById('btnSubmitKafala');

        if (remaining <= 0) {
            warningDiv.style.display = 'block';
            warningDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> <strong>Ministry Quota Exceeded:</strong> No additional sponsorship transfers are currently permitted for ${b.nationality} workers under Ministry rules.`;
            submitBtn.disabled = true;
        } else {
            warningDiv.style.display = 'none';
            submitBtn.disabled = false;
        }

        modal.dataset.bookingId = bookingId;
        modal.classList.add('active');
        return;
    }
}

function closeKafalaModal() {
    const modal = document.getElementById('kafalaModal');
    if (modal) modal.classList.remove('active');
}

function submitKafalaTransfer() {
    const modal = document.getElementById('kafalaModal');
    const bookingId = modal.dataset.bookingId;
    const nationalId = document.getElementById('kafalaNationalId').value;

    if (!nationalId) {
        alert("Please enter Saudi National ID / Resident Iqama number.");
        return;
    }

    if (typeof DB !== 'undefined') {
        const res = DB.requestKafala(bookingId, nationalId);
        if (res.success) {
            alert(res.message);
            closeKafalaModal();
            checkKafalaEligibility();
        } else {
            alert(res.message);
        }
    }
}
