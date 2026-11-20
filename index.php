<?php
// index.php - Main Gateway Hub & System Architecture Overview
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/seed.php';

$pdo = getDBConnection();
seedDatabase();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAFWA Domestic Worker Tracking & Labor Management System | منصة صفوة لإدارة وتتبع العمالة المنزلية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #042f2e 0%, #0f766e 60%, #14b8a6 100%);
            color: white;
            padding: 4.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .hero-title {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1.2;
            max-width: 900px;
            margin: 0 auto 1.25rem;
            letter-spacing: -0.5px;
        }
        .hero-subtitle {
            font-size: 1.15rem;
            opacity: 0.95;
            max-width: 780px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }
        .hero-cta-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .cta-btn-main {
            background: #ffffff;
            color: #0f766e;
            padding: 0.85rem 1.75rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }
        .cta-btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.2);
            color: #115e59;
        }
        .cta-btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 0.85rem 1.75rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }
        .cta-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: -2.5rem;
            position: relative;
            z-index: 10;
        }
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        .feature-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<header class="hero-section">
    <div class="hero-badge">
        <i class="fa-solid fa-shield-halved"></i> Saudi Ministry of Human Resources (HRSD / Musaned) Compliant
    </div>
    <h1 class="hero-title">
        Domestic Labor Tracking & Company Fleet Management System
    </h1>
    <p class="hero-subtitle">
        A comprehensive enterprise platform for monitoring hourly & monthly domestic labor service hours, enforcing statutory price caps, preventing runaway risks in housing, managing transport buses, and processing Apple Pay & Google Pay mobile subscriptions with Kafala sponsorship transfer.
    </p>

    <div class="hero-cta-group">
        <a href="app/index.php" class="cta-btn-main" target="_blank">
            <i class="fa-solid fa-mobile-screen-button"></i> Launch Client Mobile App View
        </a>
        <a href="admin/index.php" class="cta-btn-secondary">
            <i class="fa-solid fa-shield-halved"></i> Open Executive Compliance Dashboard
        </a>
    </div>
</header>

<!-- Main Container -->
<div style="max-width: 1200px; margin: 0 auto; padding: 0 1.5rem 4rem;">

    <!-- System Highlights Grid -->
    <div class="feature-grid">

        <!-- 1. Price Caps & Service Tracking -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#ccfbf1; color:#0f766e;">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Price Caps & Hours Tracking</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Strict enforcement of statutory price caps: <strong>123 SAR/hour</strong> and <strong>3,230 SAR/month</strong> for domestic workers. Specialized professions (Nurses, Caregivers, Cooks, Drivers) remain exempt from caps.
            </p>
            <a href="admin/time-tracking.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-clock"></i> View Live Shifts & Timers</a>
        </div>

        <!-- 2. Mobile App, Apple Pay & Google Pay -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#f1f5f9; color:#0f172a;">
                <i class="fa-brands fa-apple"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Mobile App & Modern Payments</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Responsive mobile and desktop client application offering monthly service packages with fast, secure checkout via <strong>Apple Pay</strong> and <strong>Google Pay</strong>, plus Mada and credit card processing.
            </p>
            <a href="app/index.php" class="btn btn-primary btn-sm w-100" target="_blank"><i class="fa-solid fa-mobile-screen"></i> Experience Mobile Checkout</a>
        </div>

        <!-- 3. Housing Supervision & Indonesian Runaway Protection -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#fee2e2; color:#b91c1c;">
                <i class="fa-solid fa-building-shield"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Housing Security & Runaway Alert</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Suitable compound accommodation with dedicated supervisors to oversee workers and prevent them from leaving premises. Special surveillance protocols for high-demand <strong>Indonesian workers</strong> with elevated absconding risks.
            </p>
            <a href="admin/housing.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-door-closed"></i> View Housing & Curfew Logs</a>
        </div>

        <!-- 4. 11,000 SAR 3-Month Package & Kafala Quotas -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#e0f2fe; color:#0284c7;">
                <i class="fa-solid fa-id-card-clip"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">3-Month Promo & Kafala Quotas</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Discounted 3-month package at <strong>11,000 SAR</strong>. Upon expiration, clients are notified of eligibility to transfer sponsorship (Kafala), while strictly enforcing Ministry per-nationality transfer caps.
            </p>
            <a href="admin/kafala.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-arrows-split-up-and-left"></i> Manage Sponsorship Quotas</a>
        </div>

        <!-- 5. 10% Hourly Visa & Nationality Diversity -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#ecfdf5; color:#047857;">
                <i class="fa-solid fa-globe"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Ministry Visa Quotas & Diversity</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Ensures at least <strong>10% of utilized visas</strong> are dedicated to hourly-based workers. Live diversity monitoring guarantees no single nationality dominates company visa issuance.
            </p>
            <a href="admin/index.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-chart-pie"></i> View Compliance Audits</a>
        </div>

        <!-- 6. Fleet & Bus Transportation -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#fef3c7; color:#b45309;">
                <i class="fa-solid fa-bus"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Buses & Transportation Fleet</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Toyota Coaster buses and vans for daily scheduled pickup and drop-off of hourly and monthly domestic personnel between the housing compound and client residences.
            </p>
            <a href="admin/fleet.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-route"></i> Manage Vehicle Dispatches</a>
        </div>

        <!-- 7. Maharah HR Recruitment Tracker -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#f3e8ff; color:#7e22ce;">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Maharah HR Recruitment</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Recruitment pipeline tracker for <strong>2 Marketing Specialists</strong> and <strong>2 Customer Service Representatives</strong> (female preference, ~7,000 SAR salary benchmark) sourced via the Maharah platform.
            </p>
            <a href="admin/hr-maharah.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-user-plus"></i> View Candidate Pipeline</a>
        </div>

        <!-- 8. Workers Directory & Roster -->
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:#f8fafc; color:#0f766e; border:1px solid #e2e8f0;">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:0.5rem;">Personnel Roster & Housing</h3>
            <p style="font-size:0.875rem; color:#475569; line-height:1.6; margin-bottom:1rem;">
                Comprehensive database of domestic workers, nurses, caregivers, cooks, and drivers with passport/Iqama vault verification, room assignments, and real-time status.
            </p>
            <a href="admin/workers.php" class="btn btn-outline btn-sm w-100"><i class="fa-solid fa-address-book"></i> Browse Worker Registry</a>
        </div>

    </div>

    <!-- Quick Navigation Bar for Reviewers -->
    <div style="margin-top:3rem; background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; padding:2rem; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
        <h3 style="font-size:1.2rem; font-weight:800; margin-bottom:1rem; color:#0f172a;">
            <i class="fa-solid fa-compass text-primary"></i> Direct Navigation Hub
        </h3>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="app/index.php" class="btn btn-primary" target="_blank"><i class="fa-solid fa-mobile-screen"></i> Client Mobile App</a>
            <a href="admin/index.php" class="btn btn-outline"><i class="fa-solid fa-shield"></i> Executive Compliance</a>
            <a href="admin/workers.php" class="btn btn-outline"><i class="fa-solid fa-users"></i> Workers & Risk Matrix</a>
            <a href="admin/time-tracking.php" class="btn btn-outline"><i class="fa-solid fa-stopwatch"></i> Hours & Time Logs</a>
            <a href="admin/housing.php" class="btn btn-outline"><i class="fa-solid fa-building-shield"></i> Housing Supervision</a>
            <a href="admin/fleet.php" class="btn btn-outline"><i class="fa-solid fa-bus"></i> Transportation Fleet</a>
            <a href="admin/kafala.php" class="btn btn-outline"><i class="fa-solid fa-id-card-clip"></i> Kafala Quotas</a>
            <a href="admin/hr-maharah.php" class="btn btn-outline"><i class="fa-solid fa-briefcase"></i> Maharah HR (7k SAR)</a>
            <a href="admin/finance.php" class="btn btn-outline"><i class="fa-solid fa-wallet"></i> Payments Ledger</a>
        </div>
    </div>

</div>

</body>
</html>
