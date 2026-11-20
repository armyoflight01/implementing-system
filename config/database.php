<?php
// config/database.php - Database connection & automatic table schema setup

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbFile = __DIR__ . '/system.sqlite';
    $isNew = !file_exists($dbFile);

    try {
        $pdo = new PDO("sqlite:" . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Initialize schema if newly created or missing tables
        initSchema($pdo);

        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function initSchema($pdo) {
    // Workers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS workers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        nationality TEXT NOT NULL,
        profession TEXT NOT NULL,
        contract_type TEXT NOT NULL DEFAULT 'hourly',
        hourly_rate REAL NOT NULL DEFAULT 100,
        monthly_rate REAL NOT NULL DEFAULT 3000,
        passport_no TEXT,
        iqama_no TEXT,
        housing_unit_id INTEGER,
        absconding_risk TEXT NOT NULL DEFAULT 'low',
        status TEXT NOT NULL DEFAULT 'in_housing',
        photo TEXT,
        visa_type TEXT DEFAULT 'hourly',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Packages Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        duration_type TEXT NOT NULL,
        duration_value INTEGER NOT NULL,
        price_sar REAL NOT NULL,
        profession TEXT NOT NULL,
        is_discounted INTEGER DEFAULT 0,
        kafala_eligible INTEGER DEFAULT 0,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Bookings & Contracts Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_code TEXT UNIQUE NOT NULL,
        client_name TEXT NOT NULL,
        client_phone TEXT NOT NULL,
        client_address TEXT NOT NULL,
        worker_id INTEGER,
        package_id INTEGER,
        service_type TEXT NOT NULL,
        hours_allocated REAL DEFAULT 0,
        hours_consumed REAL DEFAULT 0,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        total_amount REAL NOT NULL,
        payment_status TEXT NOT NULL DEFAULT 'paid',
        payment_method TEXT NOT NULL DEFAULT 'apple_pay',
        payment_ref TEXT,
        kafala_notified INTEGER DEFAULT 0,
        kafala_status TEXT DEFAULT 'none',
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (worker_id) REFERENCES workers(id),
        FOREIGN KEY (package_id) REFERENCES packages(id)
    )");

    // Time Logs Table (Check-in/out, hours tracking)
    $pdo->exec("CREATE TABLE IF NOT EXISTS time_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_id INTEGER NOT NULL,
        worker_id INTEGER NOT NULL,
        check_in_time DATETIME NOT NULL,
        check_out_time DATETIME,
        hours_worked REAL DEFAULT 0,
        location_lat REAL,
        location_lng REAL,
        supervisor_verified INTEGER DEFAULT 1,
        client_signature_token TEXT,
        notes TEXT,
        status TEXT DEFAULT 'completed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id),
        FOREIGN KEY (worker_id) REFERENCES workers(id)
    )");

    // Housing Units & Accommodation
    $pdo->exec("CREATE TABLE IF NOT EXISTS housing_units (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        building_name TEXT NOT NULL,
        room_number TEXT NOT NULL,
        capacity INTEGER NOT NULL DEFAULT 4,
        current_occupancy INTEGER NOT NULL DEFAULT 0,
        supervisor_name TEXT NOT NULL,
        supervisor_phone TEXT NOT NULL,
        security_status TEXT NOT NULL DEFAULT 'secure'
    )");

    // Security & Curfew Logs (Monitoring exits, runaway alerts)
    $pdo->exec("CREATE TABLE IF NOT EXISTS security_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        worker_id INTEGER NOT NULL,
        event_type TEXT NOT NULL,
        logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        supervisor_name TEXT NOT NULL,
        notes TEXT,
        reported_to_ministry INTEGER DEFAULT 0,
        FOREIGN KEY (worker_id) REFERENCES workers(id)
    )");

    // Fleet & Transportation
    $pdo->exec("CREATE TABLE IF NOT EXISTS fleet (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vehicle_type TEXT NOT NULL,
        plate_number TEXT NOT NULL,
        capacity INTEGER NOT NULL DEFAULT 14,
        driver_name TEXT NOT NULL,
        driver_phone TEXT NOT NULL,
        current_status TEXT NOT NULL DEFAULT 'available'
    )");

    // Dispatch Trips
    $pdo->exec("CREATE TABLE IF NOT EXISTS dispatch_trips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        vehicle_id INTEGER NOT NULL,
        trip_type TEXT NOT NULL,
        departure_time DATETIME NOT NULL,
        arrival_time DATETIME,
        destination_area TEXT NOT NULL,
        worker_count INTEGER NOT NULL DEFAULT 1,
        status TEXT NOT NULL DEFAULT 'scheduled',
        FOREIGN KEY (vehicle_id) REFERENCES fleet(id)
    )");

    // Sponsorship Transfers (Kafala)
    $pdo->exec("CREATE TABLE IF NOT EXISTS kafala_transfers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_id INTEGER NOT NULL,
        worker_id INTEGER NOT NULL,
        client_name TEXT NOT NULL,
        client_id_number TEXT NOT NULL,
        nationality TEXT NOT NULL,
        transfer_fee REAL NOT NULL DEFAULT 2500,
        request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        ministry_status TEXT NOT NULL DEFAULT 'pending_review',
        notes TEXT,
        FOREIGN KEY (booking_id) REFERENCES bookings(id),
        FOREIGN KEY (worker_id) REFERENCES workers(id)
    )");

    // Ministry Quotas & Compliance
    $pdo->exec("CREATE TABLE IF NOT EXISTS ministry_quotas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nationality TEXT UNIQUE NOT NULL,
        total_visas INTEGER NOT NULL DEFAULT 0,
        hourly_visas INTEGER NOT NULL DEFAULT 0,
        monthly_visas INTEGER NOT NULL DEFAULT 0,
        max_kafala_transfers_allowed INTEGER NOT NULL DEFAULT 50,
        kafala_transfers_used INTEGER NOT NULL DEFAULT 0,
        max_diversity_percent REAL NOT NULL DEFAULT 35.0
    )");

    // HR Recruitment (Marketing & Customer Service via Maharah)
    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_recruitment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        candidate_name TEXT NOT NULL,
        role TEXT NOT NULL,
        gender TEXT NOT NULL DEFAULT 'Female',
        expected_salary REAL NOT NULL DEFAULT 7000,
        platform_source TEXT NOT NULL DEFAULT 'Maharah',
        interview_status TEXT NOT NULL DEFAULT 'Screened',
        qualifications TEXT,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}
