<?php
// config/seed.php - Seeds realistic initial data meeting all prompt conditions

require_once __DIR__ . '/database.php';

function seedDatabase() {
    $pdo = getDBConnection();

    // Check if already seeded
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM workers");
    if ($stmt->fetch()['count'] > 0) {
        return; // Already seeded
    }

    // 1. Seed Housing Units
    $housingUnits = [
        ['building_name' => 'Al-Yasmin Compound Block A', 'room_number' => '101', 'capacity' => 6, 'current_occupancy' => 4, 'supervisor_name' => 'Fatimah Al-Ghamdi', 'supervisor_phone' => '+966 50 112 3456', 'security_status' => '24/7 Monitored - Secure Gate'],
        ['building_name' => 'Al-Yasmin Compound Block A', 'room_number' => '102', 'capacity' => 6, 'current_occupancy' => 5, 'supervisor_name' => 'Fatimah Al-Ghamdi', 'supervisor_phone' => '+966 50 112 3456', 'security_status' => '24/7 Monitored - Secure Gate'],
        ['building_name' => 'Al-Yasmin Compound Block B', 'room_number' => '201', 'capacity' => 6, 'current_occupancy' => 4, 'supervisor_name' => 'Nouf Al-Otaibi', 'supervisor_phone' => '+966 55 987 6543', 'security_status' => 'High Surveillance - Access Card Gate'],
        ['building_name' => 'Al-Yasmin Compound Block B', 'room_number' => '202', 'capacity' => 6, 'current_occupancy' => 3, 'supervisor_name' => 'Nouf Al-Otaibi', 'supervisor_phone' => '+966 55 987 6543', 'security_status' => 'High Surveillance - Access Card Gate']
    ];

    $insertHousing = $pdo->prepare("INSERT INTO housing_units (building_name, room_number, capacity, current_occupancy, supervisor_name, supervisor_phone, security_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($housingUnits as $h) {
        $insertHousing->execute([$h['building_name'], $h['room_number'], $h['capacity'], $h['current_occupancy'], $h['supervisor_name'], $h['supervisor_phone'], $h['security_status']]);
    }

    // 2. Seed Ministry Quotas (Diversity & Kafala caps)
    // Enforcing diversity: Max diversity percent 35%, ensuring no single nationality dominates.
    $quotas = [
        ['nationality' => 'Indonesian', 'total_visas' => 45, 'hourly_visas' => 8, 'monthly_visas' => 37, 'max_kafala_transfers_allowed' => 15, 'kafala_transfers_used' => 12, 'max_diversity_percent' => 30.0],
        ['nationality' => 'Filipino', 'total_visas' => 40, 'hourly_visas' => 6, 'monthly_visas' => 34, 'max_kafala_transfers_allowed' => 20, 'kafala_transfers_used' => 14, 'max_diversity_percent' => 30.0],
        ['nationality' => 'Kenyan', 'total_visas' => 35, 'hourly_visas' => 5, 'monthly_visas' => 30, 'max_kafala_transfers_allowed' => 25, 'kafala_transfers_used' => 9, 'max_diversity_percent' => 25.0],
        ['nationality' => 'Ethiopian', 'total_visas' => 25, 'hourly_visas' => 4, 'monthly_visas' => 21, 'max_kafala_transfers_allowed' => 15, 'kafala_transfers_used' => 8, 'max_diversity_percent' => 20.0],
        ['nationality' => 'Ugandan', 'total_visas' => 20, 'hourly_visas' => 3, 'monthly_visas' => 17, 'max_kafala_transfers_allowed' => 15, 'kafala_transfers_used' => 5, 'max_diversity_percent' => 15.0]
    ];

    $insertQuota = $pdo->prepare("INSERT INTO ministry_quotas (nationality, total_visas, hourly_visas, monthly_visas, max_kafala_transfers_allowed, kafala_transfers_used, max_diversity_percent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($quotas as $q) {
        $insertQuota->execute([$q['nationality'], $q['total_visas'], $q['hourly_visas'], $q['monthly_visas'], $q['max_kafala_transfers_allowed'], $q['kafala_transfers_used'], $q['max_diversity_percent']]);
    }

    // 3. Seed Workers
    // Note: Domestic Workers capped at 123 SAR/hr and 3230 SAR/mo.
    // Nurses, Caregivers, Cooks, Drivers have NO price cap.
    // Indonesian workers have High Absconding Risk tag and strict monitoring.
    $workers = [
        // Domestic Workers (Price Capped)
        ['full_name' => 'Siti Nurhaliza', 'nationality' => 'Indonesian', 'profession' => 'Domestic Worker', 'contract_type' => 'hourly', 'hourly_rate' => 95.0, 'monthly_rate' => 3200.0, 'passport_no' => 'W1928471', 'iqama_no' => '2489102831', 'housing_unit_id' => 1, 'absconding_risk' => 'high', 'status' => 'in_service', 'photo' => 'worker1.jpg', 'visa_type' => 'hourly'],
        ['full_name' => 'Dewi Lestari', 'nationality' => 'Indonesian', 'profession' => 'Domestic Worker', 'contract_type' => 'monthly', 'hourly_rate' => 90.0, 'monthly_rate' => 3150.0, 'passport_no' => 'W8273641', 'iqama_no' => '2491029381', 'housing_unit_id' => 1, 'absconding_risk' => 'high', 'status' => 'in_housing', 'photo' => 'worker2.jpg', 'visa_type' => 'monthly'],
        ['full_name' => 'Amina Wambui', 'nationality' => 'Kenyan', 'profession' => 'Domestic Worker', 'contract_type' => 'hourly', 'hourly_rate' => 85.0, 'monthly_rate' => 2900.0, 'passport_no' => 'K7483920', 'iqama_no' => '2471928374', 'housing_unit_id' => 2, 'absconding_risk' => 'low', 'status' => 'in_service', 'photo' => 'worker3.jpg', 'visa_type' => 'hourly'],
        ['full_name' => 'Fatuma Mohamed', 'nationality' => 'Ethiopian', 'profession' => 'Domestic Worker', 'contract_type' => 'monthly', 'hourly_rate' => 80.0, 'monthly_rate' => 2850.0, 'passport_no' => 'E6392019', 'iqama_no' => '2461928301', 'housing_unit_id' => 2, 'absconding_risk' => 'medium', 'status' => 'in_housing', 'photo' => 'worker4.jpg', 'visa_type' => 'monthly'],
        ['full_name' => 'Grace Nabwire', 'nationality' => 'Ugandan', 'profession' => 'Domestic Worker', 'contract_type' => 'hourly', 'hourly_rate' => 85.0, 'monthly_rate' => 2800.0, 'passport_no' => 'U5920182', 'iqama_no' => '2451920392', 'housing_unit_id' => 3, 'absconding_risk' => 'low', 'status' => 'in_transit', 'photo' => 'worker5.jpg', 'visa_type' => 'hourly'],
        
        // Specialized Roles (NO Price Cap)
        ['full_name' => 'Maria Santos', 'nationality' => 'Filipino', 'profession' => 'Nurse', 'contract_type' => 'monthly', 'hourly_rate' => 165.0, 'monthly_rate' => 4800.0, 'passport_no' => 'P9283746', 'iqama_no' => '2441928371', 'housing_unit_id' => 3, 'absconding_risk' => 'low', 'status' => 'in_service', 'photo' => 'worker6.jpg', 'visa_type' => 'monthly'],
        ['full_name' => 'Joy Dela Cruz', 'nationality' => 'Filipino', 'profession' => 'Caregiver', 'contract_type' => 'monthly', 'hourly_rate' => 150.0, 'monthly_rate' => 4300.0, 'passport_no' => 'P8192834', 'iqama_no' => '2431928340', 'housing_unit_id' => 3, 'absconding_risk' => 'low', 'status' => 'in_service', 'photo' => 'worker7.jpg', 'visa_type' => 'monthly'],
        ['full_name' => 'Anisa Kusuma', 'nationality' => 'Indonesian', 'profession' => 'Cook', 'contract_type' => 'monthly', 'hourly_rate' => 140.0, 'monthly_rate' => 3900.0, 'passport_no' => 'W7192837', 'iqama_no' => '2421928392', 'housing_unit_id' => 4, 'absconding_risk' => 'high', 'status' => 'in_housing', 'photo' => 'worker8.jpg', 'visa_type' => 'monthly'],
        ['full_name' => 'Rashid Al-Khatib', 'nationality' => 'Kenyan', 'profession' => 'Driver', 'contract_type' => 'monthly', 'hourly_rate' => 130.0, 'monthly_rate' => 3600.0, 'passport_no' => 'K6192830', 'iqama_no' => '2411928311', 'housing_unit_id' => 4, 'absconding_risk' => 'low', 'status' => 'in_service', 'photo' => 'worker9.jpg', 'visa_type' => 'monthly']
    ];

    $insertWorker = $pdo->prepare("INSERT INTO workers (full_name, nationality, profession, contract_type, hourly_rate, monthly_rate, passport_no, iqama_no, housing_unit_id, absconding_risk, status, photo, visa_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($workers as $w) {
        $insertWorker->execute([$w['full_name'], $w['nationality'], $w['profession'], $w['contract_type'], $w['hourly_rate'], $w['monthly_rate'], $w['passport_no'], $w['iqama_no'], $w['housing_unit_id'], $w['absconding_risk'], $w['status'], $w['photo'], $w['visa_type']]);
    }

    // 4. Seed Packages
    // Including promotional 3-month package @ 11,000 SAR with Kafala sponsorship transfer eligibility
    $packages = [
        [
            'name' => '3-Month Premium Domestic Package',
            'duration_type' => 'months',
            'duration_value' => 3,
            'price_sar' => 11000.0,
            'profession' => 'Domestic Worker',
            'is_discounted' => 1,
            'kafala_eligible' => 1,
            'description' => 'Special discounted 3-month package at 11,000 SAR. Upon package expiration, client receives full eligibility for Kafala (sponsorship transfer) subject to Ministry nationality quotas.'
        ],
        [
            'name' => '1-Month Standard Domestic Package',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price_sar' => 3230.0, // EXACT MINISTRY CAP
            'profession' => 'Domestic Worker',
            'is_discounted' => 0,
            'kafala_eligible' => 0,
            'description' => 'Monthly domestic worker service adhering strictly to the Saudi Ministry of Human Resources cap (3,230 SAR/month).'
        ],
        [
            'name' => 'Hourly Flex Cleaning (4 Hours)',
            'duration_type' => 'hours',
            'duration_value' => 4,
            'price_sar' => 380.0, // 95 SAR/hour (well below 123 SAR cap)
            'profession' => 'Domestic Worker',
            'is_discounted' => 1,
            'kafala_eligible' => 0,
            'description' => '4-hour on-demand domestic cleaning visit with bus transport provided directly to your residence.'
        ],
        [
            'name' => 'Specialized Private Nurse Monthly Package',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price_sar' => 4800.0, // No price cap
            'profession' => 'Nurse',
            'is_discounted' => 0,
            'kafala_eligible' => 0,
            'description' => 'Licensed elderly or post-operative nursing care. Exempt from domestic worker price caps under Ministry regulations.'
        ],
        [
            'name' => 'Specialized Private Cook Monthly Package',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price_sar' => 3900.0, // No price cap
            'profession' => 'Cook',
            'is_discounted' => 0,
            'kafala_eligible' => 0,
            'description' => 'Experienced culinary specialist for family meals and hospitality. Exempt from domestic worker price caps.'
        ]
    ];

    $insertPackage = $pdo->prepare("INSERT INTO packages (name, duration_type, duration_value, price_sar, profession, is_discounted, kafala_eligible, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($packages as $p) {
        $insertPackage->execute([$p['name'], $p['duration_type'], $p['duration_value'], $p['price_sar'], $p['profession'], $p['is_discounted'], $p['kafala_eligible'], $p['description']]);
    }

    // 5. Seed Fleet (Buses and Vans for transporting workers)
    $fleet = [
        ['vehicle_type' => 'Toyota Coaster Bus (30 Seats)', 'plate_number' => 'KSA-7821-B', 'capacity' => 30, 'driver_name' => 'Tariq Al-Harbi', 'driver_phone' => '+966 50 334 8899', 'current_status' => 'available'],
        ['vehicle_type' => 'Toyota HiAce Van (14 Seats)', 'plate_number' => 'KSA-4310-H', 'capacity' => 14, 'driver_name' => 'Sulaiman Al-Dawsari', 'driver_phone' => '+966 54 221 7766', 'current_status' => 'on_trip'],
        ['vehicle_type' => 'Hyundai H1 Van (12 Seats)', 'plate_number' => 'KSA-9012-V', 'capacity' => 12, 'driver_name' => 'Bandar Al-Mutairi', 'driver_phone' => '+966 56 778 9900', 'current_status' => 'available'],
        ['vehicle_type' => 'Toyota Coaster Bus (30 Seats)', 'plate_number' => 'KSA-1144-B', 'capacity' => 30, 'driver_name' => 'Khaled Al-Shahrani', 'driver_phone' => '+966 53 445 1122', 'current_status' => 'available']
    ];

    $insertFleet = $pdo->prepare("INSERT INTO fleet (vehicle_type, plate_number, capacity, driver_name, driver_phone, current_status) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($fleet as $f) {
        $insertFleet->execute([$f['vehicle_type'], $f['plate_number'], $f['capacity'], $f['driver_name'], $f['driver_phone'], $f['current_status']]);
    }

    // 6. Seed Dispatch Trips
    $trips = [
        ['vehicle_id' => 2, 'trip_type' => 'Hourly Dropoff Route', 'departure_time' => date('Y-m-d 07:30:00'), 'arrival_time' => date('Y-m-d 08:30:00'), 'destination_area' => 'Riyadh - Al-Malqa & Al-Sahafa Districts', 'worker_count' => 8, 'status' => 'in_progress'],
        ['vehicle_id' => 1, 'trip_type' => 'Monthly Worker Placement', 'departure_time' => date('Y-m-d 09:00:00'), 'arrival_time' => date('Y-m-d 10:15:00'), 'destination_area' => 'Riyadh - Al-Nakheel & Hittin Districts', 'worker_count' => 4, 'status' => 'completed'],
        ['vehicle_id' => 3, 'trip_type' => 'Hourly Pickup & Return to Housing', 'departure_time' => date('Y-m-d 14:00:00'), 'arrival_time' => null, 'destination_area' => 'Riyadh - Al-Yasmin & Al-Narjis', 'worker_count' => 6, 'status' => 'scheduled']
    ];

    $insertTrip = $pdo->prepare("INSERT INTO dispatch_trips (vehicle_id, trip_type, departure_time, arrival_time, destination_area, worker_count, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($trips as $t) {
        $insertTrip->execute([$t['vehicle_id'], $t['trip_type'], $t['departure_time'], $t['arrival_time'], $t['destination_area'], $t['worker_count'], $t['status']]);
    }

    // 7. Seed Bookings (Including a 3-month package that just completed, triggering Kafala notification)
    $today = date('Y-m-d');
    $threeMonthsAgo = date('Y-m-d', strtotime('-90 days'));
    $bookings = [
        [
            'booking_code' => 'BK-SA-11092',
            'client_name' => 'Fahad Abdulaziz Al-Saud',
            'client_phone' => '+966 50 554 1234',
            'client_address' => 'Villa 14, Al-Malqa District, Riyadh',
            'worker_id' => 2, // Dewi Lestari (Indonesian)
            'package_id' => 1, // 3-month 11,000 SAR package
            'service_type' => 'monthly',
            'hours_allocated' => 0,
            'hours_consumed' => 0,
            'start_date' => $threeMonthsAgo,
            'end_date' => $today, // Matured today!
            'total_amount' => 11000.0,
            'payment_status' => 'paid',
            'payment_method' => 'apple_pay',
            'payment_ref' => 'APL-PAY-98124801-KSA',
            'kafala_notified' => 1,
            'kafala_status' => 'eligible',
            'status' => 'completed'
        ],
        [
            'booking_code' => 'BK-SA-20419',
            'client_name' => 'Sarah Abdullah Al-Ghamdi',
            'client_phone' => '+966 55 443 8970',
            'client_address' => 'Apt 4B, Al-Sulaimaniyah, Riyadh',
            'worker_id' => 1, // Siti Nurhaliza
            'package_id' => 3, // Hourly Flex (4 Hours)
            'service_type' => 'hourly',
            'hours_allocated' => 4.0,
            'hours_consumed' => 2.5,
            'start_date' => $today,
            'end_date' => $today,
            'total_amount' => 380.0,
            'payment_status' => 'paid',
            'payment_method' => 'google_pay',
            'payment_ref' => 'GPAY-TRX-5510294-SA',
            'kafala_notified' => 0,
            'kafala_status' => 'none',
            'status' => 'in_progress'
        ],
        [
            'booking_code' => 'BK-SA-33918',
            'client_name' => 'Dr. Khalid Al-Otaibi',
            'client_phone' => '+966 56 781 2299',
            'client_address' => 'Villa 88, Diplomatic Quarter, Riyadh',
            'worker_id' => 6, // Maria Santos (Nurse)
            'package_id' => 4, // Nurse Monthly
            'service_type' => 'monthly',
            'hours_allocated' => 0,
            'hours_consumed' => 0,
            'start_date' => date('Y-m-d', strtotime('-15 days')),
            'end_date' => date('Y-m-d', strtotime('+15 days')),
            'total_amount' => 4800.0,
            'payment_status' => 'paid',
            'payment_method' => 'apple_pay',
            'payment_ref' => 'APL-PAY-771920-KSA',
            'kafala_notified' => 0,
            'kafala_status' => 'none',
            'status' => 'active'
        ]
    ];

    $insertBooking = $pdo->prepare("INSERT INTO bookings (booking_code, client_name, client_phone, client_address, worker_id, package_id, service_type, hours_allocated, hours_consumed, start_date, end_date, total_amount, payment_status, payment_method, payment_ref, kafala_notified, kafala_status, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($bookings as $b) {
        $insertBooking->execute([$b['booking_code'], $b['client_name'], $b['client_phone'], $b['client_address'], $b['worker_id'], $b['package_id'], $b['service_type'], $b['hours_allocated'], $b['hours_consumed'], $b['start_date'], $b['end_date'], $b['total_amount'], $b['payment_status'], $b['payment_method'], $b['payment_ref'], $b['kafala_notified'], $b['kafala_status'], $b['status']]);
    }

    // 8. Seed Time Logs (Hourly check-in/out tracking)
    $timeLogs = [
        ['booking_id' => 2, 'worker_id' => 1, 'check_in_time' => date('Y-m-d 09:00:00'), 'check_out_time' => null, 'hours_worked' => 2.5, 'location_lat' => 24.7136, 'location_lng' => 46.6753, 'supervisor_verified' => 1, 'client_signature_token' => 'SIG-TOKEN-77124', 'notes' => 'Shift ongoing at client residence in Al-Sulaimaniyah.', 'status' => 'active'],
        ['booking_id' => 2, 'worker_id' => 3, 'check_in_time' => date('Y-m-d 08:00:00', strtotime('-1 day')), 'check_out_time' => date('Y-m-d 12:00:00', strtotime('-1 day')), 'hours_worked' => 4.0, 'location_lat' => 24.7743, 'location_lng' => 46.6385, 'supervisor_verified' => 1, 'client_signature_token' => 'SIG-TOKEN-99412', 'notes' => 'Shift completed successfully. Returned safely via Bus KSA-7821-B.', 'status' => 'completed']
    ];

    $insertTime = $pdo->prepare("INSERT INTO time_logs (booking_id, worker_id, check_in_time, check_out_time, hours_worked, location_lat, location_lng, supervisor_verified, client_signature_token, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($timeLogs as $t) {
        $insertTime->execute([$t['booking_id'], $t['worker_id'], $t['check_in_time'], $t['check_out_time'], $t['hours_worked'], $t['location_lat'], $t['location_lng'], $t['supervisor_verified'], $t['client_signature_token'], $t['notes'], $t['status']]);
    }

    // 9. Seed Security Logs (Supervisor oversight & absconding protection)
    $securityLogs = [
        ['worker_id' => 1, 'event_type' => 'authorized_gate_dispatch', 'logged_at' => date('Y-m-d 07:45:00'), 'supervisor_name' => 'Fatimah Al-Ghamdi', 'notes' => 'Boarded Van KSA-4310-H for scheduled hourly client booking BK-SA-20419.', 'reported_to_ministry' => 0],
        ['worker_id' => 2, 'event_type' => 'curfew_roll_call', 'logged_at' => date('Y-m-d 21:00:00', strtotime('-1 day')), 'supervisor_name' => 'Fatimah Al-Ghamdi', 'notes' => 'Evening roll call verified present in Room 101. No unauthorized movement.', 'reported_to_ministry' => 0],
        ['worker_id' => 8, 'event_type' => 'high_risk_monitoring', 'logged_at' => date('Y-m-d 18:30:00', strtotime('-2 days')), 'supervisor_name' => 'Nouf Al-Otaibi', 'notes' => 'Indonesian nationality routine welfare check conducted. High runaway risk protocol maintained; passport secured in company vault.', 'reported_to_ministry' => 0],
        ['worker_id' => 2, 'event_type' => 'unauthorized_exit_prevented', 'logged_at' => date('Y-m-d 22:15:00', strtotime('-5 days')), 'supervisor_name' => 'Fatimah Al-Ghamdi', 'notes' => 'Attempted to exit compound perimeter gate without valid dispatch pass. Security officer intervened; counselled and escorted to housing unit.', 'reported_to_ministry' => 0]
    ];

    $insertSec = $pdo->prepare("INSERT INTO security_logs (worker_id, event_type, logged_at, supervisor_name, notes, reported_to_ministry) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($securityLogs as $s) {
        $insertSec->execute([$s['worker_id'], $s['event_type'], $s['logged_at'], $s['supervisor_name'], $s['notes'], $s['reported_to_ministry']]);
    }

    // 10. Seed HR Recruitment (Marketing: 2 positions, Customer Service: 2 positions via "Maharah")
    // Market salary ~7,000 SAR, females preferred as requested.
    $candidates = [
        ['candidate_name' => 'Reem Khalid Al-Zahrani', 'role' => 'Marketing Specialist', 'gender' => 'Female', 'expected_salary' => 7000.0, 'platform_source' => 'Maharah', 'interview_status' => 'Offered', 'qualifications' => 'BSc in Digital Marketing from KSU. 3 years experience handling social media ad campaigns and recruitment lead gen.', 'notes' => 'Excellent profile. Offer extended at standard market benchmark (7,000 SAR/month). Acceptance expected.'],
        ['candidate_name' => 'Lama Mansour Al-Husseini', 'role' => 'Marketing Specialist', 'gender' => 'Female', 'expected_salary' => 7200.0, 'platform_source' => 'Maharah', 'interview_status' => 'Interview Scheduled', 'qualifications' => 'Certified Performance Marketer, experienced with Google Ads, TikTok Ads, and domestic labor promotions.', 'notes' => 'Shortlisted for second round technical interview with Marketing Director.'],
        ['candidate_name' => 'Hadeel Saad Al-Dosari', 'role' => 'Customer Service Representative', 'gender' => 'Female', 'expected_salary' => 6800.0, 'platform_source' => 'Maharah', 'interview_status' => 'Hired', 'qualifications' => 'Fluent in Arabic & English. 4 years client resolution experience in domestic recruitment and hospitality apps.', 'notes' => 'Officially signed contract. Joining date next Sunday. Will manage client ticketing and booking inquiries.'],
        ['candidate_name' => 'Shahad Ibrahim Al-Subaie', 'role' => 'Customer Service Representative', 'gender' => 'Female', 'expected_salary' => 7000.0, 'platform_source' => 'Maharah', 'interview_status' => 'Interview Scheduled', 'qualifications' => 'Customer success background in logistics platform. High empathy, excellent phone and WhatsApp Business skills.', 'notes' => 'Interview scheduled for tomorrow at 11:00 AM. Meets target budget benchmark.'],
        ['candidate_name' => 'Mona Fahad Al-Mutlaq', 'role' => 'Customer Service Representative', 'gender' => 'Female', 'expected_salary' => 7000.0, 'platform_source' => 'Maharah', 'interview_status' => 'Screened', 'qualifications' => 'Diploma in Business Administration. Experience with CRM tools and client onboarding.', 'notes' => 'Reserve candidate for Customer Service role #2 if interviewees decline.']
    ];

    $insertHR = $pdo->prepare("INSERT INTO hr_recruitment (candidate_name, role, gender, expected_salary, platform_source, interview_status, qualifications, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($candidates as $c) {
        $insertHR->execute([$c['candidate_name'], $c['role'], $c['gender'], $c['expected_salary'], $c['platform_source'], $c['interview_status'], $c['qualifications'], $c['notes']]);
    }
}
