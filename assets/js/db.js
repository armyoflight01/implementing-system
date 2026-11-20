// assets/js/db.js - Standalone Client-Side Data & Storage Engine for GitHub Pages & Localhost

const DEFAULT_STATE = {
    workers: [
        { id: 1, full_name: 'Siti Nurhaliza', nationality: 'Indonesian', profession: 'Domestic Worker', contract_type: 'hourly', hourly_rate: 95.0, monthly_rate: 3200.0, passport_no: 'W1928471', iqama_no: '2489102831', housing_unit_id: 1, building_name: 'Al-Yasmin Compound Block A', room_number: '101', supervisor_name: 'Fatimah Al-Ghamdi', supervisor_phone: '+966 50 112 3456', absconding_risk: 'high', status: 'in_service', visa_type: 'hourly' },
        { id: 2, full_name: 'Dewi Lestari', nationality: 'Indonesian', profession: 'Domestic Worker', contract_type: 'monthly', hourly_rate: 90.0, monthly_rate: 3150.0, passport_no: 'W8273641', iqama_no: '2491029381', housing_unit_id: 1, building_name: 'Al-Yasmin Compound Block A', room_number: '101', supervisor_name: 'Fatimah Al-Ghamdi', supervisor_phone: '+966 50 112 3456', absconding_risk: 'high', status: 'in_housing', visa_type: 'monthly' },
        { id: 3, full_name: 'Amina Wambui', nationality: 'Kenyan', profession: 'Domestic Worker', contract_type: 'hourly', hourly_rate: 85.0, monthly_rate: 2900.0, passport_no: 'K7483920', iqama_no: '2471928374', housing_unit_id: 2, building_name: 'Al-Yasmin Compound Block A', room_number: '102', supervisor_name: 'Fatimah Al-Ghamdi', supervisor_phone: '+966 50 112 3456', absconding_risk: 'low', status: 'in_service', visa_type: 'hourly' },
        { id: 4, full_name: 'Fatuma Mohamed', nationality: 'Ethiopian', profession: 'Domestic Worker', contract_type: 'monthly', hourly_rate: 80.0, monthly_rate: 2850.0, passport_no: 'E6392019', iqama_no: '2461928301', housing_unit_id: 2, building_name: 'Al-Yasmin Compound Block A', room_number: '102', supervisor_name: 'Fatimah Al-Ghamdi', supervisor_phone: '+966 50 112 3456', absconding_risk: 'medium', status: 'in_housing', visa_type: 'monthly' },
        { id: 5, full_name: 'Grace Nabwire', nationality: 'Ugandan', profession: 'Domestic Worker', contract_type: 'hourly', hourly_rate: 85.0, monthly_rate: 2800.0, passport_no: 'U5920182', iqama_no: '2451920392', housing_unit_id: 3, building_name: 'Al-Yasmin Compound Block B', room_number: '201', supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', absconding_risk: 'low', status: 'in_transit', visa_type: 'hourly' },
        { id: 6, full_name: 'Maria Santos', nationality: 'Filipino', profession: 'Nurse', contract_type: 'monthly', hourly_rate: 165.0, monthly_rate: 4800.0, passport_no: 'P9283746', iqama_no: '2441928371', housing_unit_id: 3, building_name: 'Al-Yasmin Compound Block B', room_number: '201', supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', absconding_risk: 'low', status: 'in_service', visa_type: 'monthly' },
        { id: 7, full_name: 'Joy Dela Cruz', nationality: 'Filipino', profession: 'Caregiver', contract_type: 'monthly', hourly_rate: 150.0, monthly_rate: 4300.0, passport_no: 'P8192834', iqama_no: '2431928340', housing_unit_id: 3, building_name: 'Al-Yasmin Compound Block B', room_number: '201', supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', absconding_risk: 'low', status: 'in_service', visa_type: 'monthly' },
        { id: 8, full_name: 'Anisa Kusuma', nationality: 'Indonesian', profession: 'Cook', contract_type: 'monthly', hourly_rate: 140.0, monthly_rate: 3900.0, passport_no: 'W7192837', iqama_no: '2421928392', housing_unit_id: 4, building_name: 'Al-Yasmin Compound Block B', room_number: '202', supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', absconding_risk: 'high', status: 'in_housing', visa_type: 'monthly' },
        { id: 9, full_name: 'Rashid Al-Khatib', nationality: 'Kenyan', profession: 'Driver', contract_type: 'monthly', hourly_rate: 130.0, monthly_rate: 3600.0, passport_no: 'K6192830', iqama_no: '2411928311', housing_unit_id: 4, building_name: 'Al-Yasmin Compound Block B', room_number: '202', supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', absconding_risk: 'low', status: 'in_service', visa_type: 'monthly' }
    ],
    packages: [
        {
            id: 1,
            name: '3-Month Premium Domestic Package',
            duration_type: 'months',
            duration_value: 3,
            price_sar: 11000.0,
            profession: 'Domestic Worker',
            is_discounted: 1,
            kafala_eligible: 1,
            description: 'Special discounted 3-month package at 11,000 SAR. Upon package expiration, client receives full eligibility for Kafala (sponsorship transfer) subject to Ministry nationality quotas.'
        },
        {
            id: 2,
            name: '1-Month Standard Domestic Package',
            duration_type: 'months',
            duration_value: 1,
            price_sar: 3230.0,
            profession: 'Domestic Worker',
            is_discounted: 0,
            kafala_eligible: 0,
            description: 'Monthly domestic worker service adhering strictly to the Saudi Ministry of Human Resources cap (3,230 SAR/month).'
        },
        {
            id: 3,
            name: 'Hourly Flex Cleaning (4 Hours)',
            duration_type: 'hours',
            duration_value: 4,
            price_sar: 380.0,
            profession: 'Domestic Worker',
            is_discounted: 1,
            kafala_eligible: 0,
            description: '4-hour on-demand domestic cleaning visit with bus transport provided directly to your residence (95 SAR/hr, well below 123 SAR cap).'
        },
        {
            id: 4,
            name: 'Specialized Private Nurse Monthly Package',
            duration_type: 'months',
            duration_value: 1,
            price_sar: 4800.0,
            profession: 'Nurse',
            is_discounted: 0,
            kafala_eligible: 0,
            description: 'Licensed elderly or post-operative nursing care. Exempt from domestic worker price caps under Ministry regulations.'
        },
        {
            id: 5,
            name: 'Specialized Private Cook Monthly Package',
            duration_type: 'months',
            duration_value: 1,
            price_sar: 3900.0,
            profession: 'Cook',
            is_discounted: 0,
            kafala_eligible: 0,
            description: 'Experienced culinary specialist for family meals and hospitality. Exempt from domestic worker price caps.'
        }
    ],
    bookings: [
        {
            id: 1,
            booking_code: 'BK-SA-11092',
            client_name: 'Fahad Abdulaziz Al-Saud',
            client_phone: '+966 50 554 1234',
            client_address: 'Villa 14, Al-Malqa District, Riyadh',
            worker_id: 2,
            worker_name: 'Dewi Lestari',
            nationality: 'Indonesian',
            profession: 'Domestic Worker',
            package_id: 1,
            package_name: '3-Month Premium Domestic Package',
            service_type: 'monthly',
            hours_allocated: 0,
            hours_consumed: 0,
            start_date: '2026-08-20',
            end_date: '2026-11-20',
            total_amount: 11000.0,
            payment_status: 'paid',
            payment_method: 'apple_pay',
            payment_ref: 'APL-PAY-98124801-KSA',
            kafala_notified: 1,
            kafala_status: 'eligible',
            status: 'completed',
            created_at: '2026-08-20 09:00:00'
        },
        {
            id: 2,
            booking_code: 'BK-SA-20419',
            client_name: 'Sarah Abdullah Al-Ghamdi',
            client_phone: '+966 55 443 8970',
            client_address: 'Apt 4B, Al-Sulaimaniyah, Riyadh',
            worker_id: 1,
            worker_name: 'Siti Nurhaliza',
            nationality: 'Indonesian',
            profession: 'Domestic Worker',
            package_id: 3,
            package_name: 'Hourly Flex Cleaning (4 Hours)',
            service_type: 'hourly',
            hours_allocated: 4.0,
            hours_consumed: 2.5,
            start_date: new Date().toISOString().split('T')[0],
            end_date: new Date().toISOString().split('T')[0],
            total_amount: 380.0,
            payment_status: 'paid',
            payment_method: 'google_pay',
            payment_ref: 'GPAY-TRX-5510294-SA',
            kafala_notified: 0,
            kafala_status: 'none',
            status: 'active',
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        },
        {
            id: 3,
            booking_code: 'BK-SA-33918',
            client_name: 'Dr. Khalid Al-Otaibi',
            client_phone: '+966 56 781 2299',
            client_address: 'Villa 88, Diplomatic Quarter, Riyadh',
            worker_id: 6,
            worker_name: 'Maria Santos',
            nationality: 'Filipino',
            profession: 'Nurse',
            package_id: 4,
            package_name: 'Specialized Private Nurse Monthly Package',
            service_type: 'monthly',
            hours_allocated: 0,
            hours_consumed: 0,
            start_date: '2026-11-05',
            end_date: '2026-12-05',
            total_amount: 4800.0,
            payment_status: 'paid',
            payment_method: 'apple_pay',
            payment_ref: 'APL-PAY-771920-KSA',
            kafala_notified: 0,
            kafala_status: 'none',
            status: 'active',
            created_at: '2026-11-05 10:15:00'
        }
    ],
    time_logs: [
        {
            id: 1,
            booking_id: 2,
            booking_code: 'BK-SA-20419',
            worker_id: 1,
            worker_name: 'Siti Nurhaliza',
            nationality: 'Indonesian',
            profession: 'Domestic Worker',
            client_name: 'Sarah Abdullah Al-Ghamdi',
            client_address: 'Apt 4B, Al-Sulaimaniyah, Riyadh',
            service_type: 'hourly',
            hours_allocated: 4.0,
            check_in_time: new Date(Date.now() - 2.5 * 3600 * 1000).toISOString().replace('T', ' ').substring(0, 19),
            check_out_time: null,
            hours_worked: 2.5,
            location_lat: 24.7136,
            location_lng: 46.6753,
            supervisor_verified: 1,
            client_signature_token: 'SIG-TOKEN-77124',
            notes: 'Shift ongoing at client residence in Al-Sulaimaniyah.',
            status: 'active'
        },
        {
            id: 2,
            booking_id: 2,
            booking_code: 'BK-SA-20419',
            worker_id: 3,
            worker_name: 'Amina Wambui',
            nationality: 'Kenyan',
            profession: 'Domestic Worker',
            client_name: 'Sarah Abdullah Al-Ghamdi',
            client_address: 'Al-Malqa, Riyadh',
            service_type: 'hourly',
            hours_allocated: 4.0,
            check_in_time: '2026-11-19 08:00:00',
            check_out_time: '2026-11-19 12:00:00',
            hours_worked: 4.0,
            location_lat: 24.7743,
            location_lng: 46.6385,
            supervisor_verified: 1,
            client_signature_token: 'SIG-TOKEN-99412',
            notes: 'Shift completed successfully. Returned safely via Bus KSA-7821-B.',
            status: 'completed'
        }
    ],
    housing_units: [
        { id: 1, building_name: 'Al-Yasmin Compound Block A', room_number: '101', capacity: 6, actual_occupants: 4, high_risk_count: 2, supervisor_name: 'Fatimah Al-Ghamdi', supervisor_phone: '+966 50 112 3456', security_status: '24/7 Monitored - Secure Gate' },
        { id: 2, building_name: 'Al-Yasmin Compound Block A', room_number: '102', capacity: 6, actual_occupants: 5, high_risk_count: 1, supervisor_name: 'Fatimah Al-Ghamdi', supervisor_phone: '+966 50 112 3456', security_status: '24/7 Monitored - Secure Gate' },
        { id: 3, building_name: 'Al-Yasmin Compound Block B', room_number: '201', capacity: 6, actual_occupants: 4, high_risk_count: 0, supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', security_status: 'High Surveillance - Access Card Gate' },
        { id: 4, building_name: 'Al-Yasmin Compound Block B', room_number: '202', capacity: 6, actual_occupants: 3, high_risk_count: 1, supervisor_name: 'Nouf Al-Otaibi', supervisor_phone: '+966 55 987 6543', security_status: 'High Surveillance - Access Card Gate' }
    ],
    security_logs: [
        { id: 1, worker_id: 1, worker_name: 'Siti Nurhaliza', nationality: 'Indonesian', profession: 'Domestic Worker', event_type: 'authorized_gate_dispatch', logged_at: new Date().toISOString().replace('T', ' ').substring(0, 19), supervisor_name: 'Fatimah Al-Ghamdi', notes: 'Boarded Van KSA-4310-H for scheduled hourly client booking BK-SA-20419.', reported_to_ministry: 0 },
        { id: 2, worker_id: 2, worker_name: 'Dewi Lestari', nationality: 'Indonesian', profession: 'Domestic Worker', event_type: 'curfew_roll_call', logged_at: '2026-11-19 21:00:00', supervisor_name: 'Fatimah Al-Ghamdi', notes: 'Evening roll call verified present in Room 101. No unauthorized movement.', reported_to_ministry: 0 },
        { id: 3, worker_id: 8, worker_name: 'Anisa Kusuma', nationality: 'Indonesian', profession: 'Cook', event_type: 'high_risk_monitoring', logged_at: '2026-11-18 18:30:00', supervisor_name: 'Nouf Al-Otaibi', notes: 'Indonesian nationality routine welfare check conducted. High runaway risk protocol maintained; passport secured in company vault.', reported_to_ministry: 0 },
        { id: 4, worker_id: 2, worker_name: 'Dewi Lestari', nationality: 'Indonesian', profession: 'Domestic Worker', event_type: 'unauthorized_exit_prevented', logged_at: '2026-11-15 22:15:00', supervisor_name: 'Fatimah Al-Ghamdi', notes: 'Attempted to exit compound perimeter gate without valid dispatch pass. Security officer intervened; counselled and escorted to housing unit.', reported_to_ministry: 0 }
    ],
    fleet: [
        { id: 1, vehicle_type: 'Toyota Coaster Bus (30 Seats)', plate_number: 'KSA-7821-B', capacity: 30, driver_name: 'Tariq Al-Harbi', driver_phone: '+966 50 334 8899', current_status: 'available' },
        { id: 2, vehicle_type: 'Toyota HiAce Van (14 Seats)', plate_number: 'KSA-4310-H', capacity: 14, driver_name: 'Sulaiman Al-Dawsari', driver_phone: '+966 54 221 7766', current_status: 'on_trip' },
        { id: 3, vehicle_type: 'Hyundai H1 Van (12 Seats)', plate_number: 'KSA-9012-V', capacity: 12, driver_name: 'Bandar Al-Mutairi', driver_phone: '+966 56 778 9900', current_status: 'available' },
        { id: 4, vehicle_type: 'Toyota Coaster Bus (30 Seats)', plate_number: 'KSA-1144-B', capacity: 30, driver_name: 'Khaled Al-Shahrani', driver_phone: '+966 53 445 1122', current_status: 'available' }
    ],
    dispatch_trips: [
        { id: 1, vehicle_id: 2, vehicle_type: 'Toyota HiAce Van (14 Seats)', plate_number: 'KSA-4310-H', driver_name: 'Sulaiman Al-Dawsari', driver_phone: '+966 54 221 7766', trip_type: 'Hourly Dropoff Route', departure_time: '2026-11-20 07:30:00', arrival_time: '2026-11-20 08:30:00', destination_area: 'Riyadh - Al-Malqa & Al-Sahafa Districts', worker_count: 8, status: 'in_progress' },
        { id: 2, vehicle_id: 1, vehicle_type: 'Toyota Coaster Bus (30 Seats)', plate_number: 'KSA-7821-B', driver_name: 'Tariq Al-Harbi', driver_phone: '+966 50 334 8899', trip_type: 'Monthly Worker Placement', departure_time: '2026-11-20 09:00:00', arrival_time: '2026-11-20 10:15:00', destination_area: 'Riyadh - Al-Nakheel & Hittin Districts', worker_count: 4, status: 'completed' },
        { id: 3, vehicle_id: 3, vehicle_type: 'Hyundai H1 Van (12 Seats)', plate_number: 'KSA-9012-V', driver_name: 'Bandar Al-Mutairi', driver_phone: '+966 56 778 9900', trip_type: 'Hourly Pickup & Return to Housing', departure_time: '2026-11-20 14:00:00', arrival_time: null, destination_area: 'Riyadh - Al-Yasmin & Al-Narjis', worker_count: 6, status: 'scheduled' }
    ],
    ministry_quotas: [
        { id: 1, nationality: 'Indonesian', total_visas: 45, hourly_visas: 8, monthly_visas: 37, max_kafala_transfers_allowed: 15, kafala_transfers_used: 12, max_diversity_percent: 30.0 },
        { id: 2, nationality: 'Filipino', total_visas: 40, hourly_visas: 6, monthly_visas: 34, max_kafala_transfers_allowed: 20, kafala_transfers_used: 14, max_diversity_percent: 30.0 },
        { id: 3, nationality: 'Kenyan', total_visas: 35, hourly_visas: 5, monthly_visas: 30, max_kafala_transfers_allowed: 25, kafala_transfers_used: 9, max_diversity_percent: 25.0 },
        { id: 4, nationality: 'Ethiopian', total_visas: 25, hourly_visas: 4, monthly_visas: 21, max_kafala_transfers_allowed: 15, kafala_transfers_used: 8, max_diversity_percent: 20.0 },
        { id: 5, nationality: 'Ugandan', total_visas: 20, hourly_visas: 3, monthly_visas: 17, max_kafala_transfers_allowed: 15, kafala_transfers_used: 5, max_diversity_percent: 15.0 }
    ],
    kafala_transfers: [
        { id: 1, booking_id: 1, worker_id: 2, worker_name: 'Dewi Lestari', client_name: 'Fahad Abdulaziz Al-Saud', client_id_number: '1098273645', nationality: 'Indonesian', transfer_fee: 2500.0, request_date: '2026-11-20 11:30:00', ministry_status: 'quota_approved', notes: 'Transferred upon 3-month package completion.' }
    ],
    hr_recruitment: [
        { id: 1, candidate_name: 'Reem Khalid Al-Zahrani', role: 'Marketing Specialist', gender: 'Female', expected_salary: 7000.0, platform_source: 'Maharah', interview_status: 'Offered', qualifications: 'BSc in Digital Marketing from KSU. 3 years experience handling social media ad campaigns and recruitment lead gen.', notes: 'Excellent profile. Offer extended at standard market benchmark (7,000 SAR/month).' },
        { id: 2, candidate_name: 'Lama Mansour Al-Husseini', role: 'Marketing Specialist', gender: 'Female', expected_salary: 7200.0, platform_source: 'Maharah', interview_status: 'Interview Scheduled', qualifications: 'Certified Performance Marketer, experienced with Google Ads, TikTok Ads, and domestic labor promotions.', notes: 'Shortlisted for second round technical interview with Marketing Director.' },
        { id: 3, candidate_name: 'Hadeel Saad Al-Dosari', role: 'Customer Service Representative', gender: 'Female', expected_salary: 6800.0, platform_source: 'Maharah', interview_status: 'Hired', qualifications: 'Fluent in Arabic & English. 4 years client resolution experience in domestic recruitment and hospitality apps.', notes: 'Officially signed contract. Joining date next Sunday. Will manage client ticketing and booking inquiries.' },
        { id: 4, candidate_name: 'Shahad Ibrahim Al-Subaie', role: 'Customer Service Representative', gender: 'Female', expected_salary: 7000.0, platform_source: 'Maharah', interview_status: 'Interview Scheduled', qualifications: 'Customer success background in logistics platform. High empathy, excellent phone and WhatsApp Business skills.', notes: 'Interview scheduled for tomorrow at 11:00 AM. Meets target budget benchmark.' },
        { id: 5, candidate_name: 'Mona Fahad Al-Mutlaq', role: 'Customer Service Representative', gender: 'Female', expected_salary: 7000.0, platform_source: 'Maharah', interview_status: 'Screened', qualifications: 'Diploma in Business Administration. Experience with CRM tools and client onboarding.', notes: 'Reserve candidate for Customer Service role #2 if interviewees decline.' }
    ]
};

const DB = {
    getKey(key) {
        try {
            const data = localStorage.getItem('safwa_' + key);
            if (!data) {
                localStorage.setItem('safwa_' + key, JSON.stringify(DEFAULT_STATE[key]));
                return DEFAULT_STATE[key];
            }
            return JSON.parse(data);
        } catch (e) {
            return DEFAULT_STATE[key] || [];
        }
    },
    setKey(key, val) {
        try {
            localStorage.setItem('safwa_' + key, JSON.stringify(val));
        } catch (e) {
            console.error("Storage error", e);
        }
    },
    getWorkers() { return this.getKey('workers'); },
    addWorker(worker) {
        const workers = this.getWorkers();
        worker.id = workers.length > 0 ? Math.max(...workers.map(w => w.id)) + 1 : 1;
        workers.push(worker);
        this.setKey('workers', workers);
        return worker;
    },
    getPackages() { return this.getKey('packages'); },
    getBookings() { return this.getKey('bookings'); },
    addBooking(booking) {
        const bookings = this.getBookings();
        booking.id = bookings.length > 0 ? Math.max(...bookings.map(b => b.id)) + 1 : 1;
        bookings.unshift(booking);
        this.setKey('bookings', bookings);
        return booking;
    },
    getTimeLogs() { return this.getKey('time_logs'); },
    addTimeLog(log) {
        const logs = this.getTimeLogs();
        log.id = logs.length > 0 ? Math.max(...logs.map(l => l.id)) + 1 : 1;
        logs.unshift(log);
        this.setKey('time_logs', logs);
        return log;
    },
    checkOutShift(logId, sig, notes) {
        const logs = this.getTimeLogs();
        const log = logs.find(l => l.id == logId);
        if (log) {
            log.check_out_time = new Date().toISOString().replace('T', ' ').substring(0, 19);
            log.status = 'completed';
            log.client_signature_token = sig;
            log.notes = notes;
            this.setKey('time_logs', logs);

            // Update worker status
            const workers = this.getWorkers();
            const w = workers.find(w => w.id == log.worker_id);
            if (w) {
                w.status = 'in_transit';
                this.setKey('workers', workers);
            }
        }
        return log;
    },
    getHousing() { return this.getKey('housing_units'); },
    getSecurityLogs() { return this.getKey('security_logs'); },
    addSecurityLog(entry) {
        const logs = this.getSecurityLogs();
        entry.id = logs.length > 0 ? Math.max(...logs.map(l => l.id)) + 1 : 1;
        logs.unshift(entry);
        this.setKey('security_logs', logs);
        return entry;
    },
    getFleet() { return this.getKey('fleet'); },
    getTrips() { return this.getKey('dispatch_trips'); },
    addTrip(trip) {
        const trips = this.getTrips();
        trip.id = trips.length > 0 ? Math.max(...trips.map(t => t.id)) + 1 : 1;
        trips.unshift(trip);
        this.setKey('dispatch_trips', trips);

        // Mark vehicle as on_trip
        const fleet = this.getFleet();
        const v = fleet.find(f => f.id == trip.vehicle_id);
        if (v) {
            v.current_status = 'on_trip';
            this.setKey('fleet', fleet);
        }
        return trip;
    },
    completeTrip(tripId) {
        const trips = this.getTrips();
        const t = trips.find(tr => tr.id == tripId);
        if (t) {
            t.status = 'completed';
            t.arrival_time = new Date().toISOString().replace('T', ' ').substring(0, 19);
            this.setKey('dispatch_trips', trips);

            const fleet = this.getFleet();
            const v = fleet.find(f => f.id == t.vehicle_id);
            if (v) {
                v.current_status = 'available';
                this.setKey('fleet', fleet);
            }
        }
        return t;
    },
    getQuotas() { return this.getKey('ministry_quotas'); },
    getKafalaTransfers() { return this.getKey('kafala_transfers'); },
    requestKafala(bookingId, nationalId) {
        const bookings = this.getBookings();
        const b = bookings.find(bk => bk.id == bookingId);
        if (!b) return { success: false, message: 'Booking not found' };

        const quotas = this.getQuotas();
        const q = quotas.find(qt => qt.nationality === b.nationality);
        if (!q) return { success: false, message: 'Nationality quota record not found' };

        if (q.kafala_transfers_used >= q.max_kafala_transfers_allowed) {
            return {
                success: false,
                quota_exceeded: true,
                message: `CRITICAL ERROR: Ministry of Labor quota for ${b.nationality} sponsorship transfers has reached its statutory cap (${q.max_kafala_transfers_allowed} transfers). No additional transfers permitted under Ministry rules.`
            };
        }

        // Increment quota
        q.kafala_transfers_used++;
        this.setKey('ministry_quotas', quotas);

        // Add transfer log
        const transfers = this.getKafalaTransfers();
        const newTrf = {
            id: transfers.length > 0 ? Math.max(...transfers.map(t => t.id)) + 1 : 1,
            booking_id: b.id,
            worker_id: b.worker_id,
            worker_name: b.worker_name,
            client_name: b.client_name,
            client_id_number: nationalId,
            nationality: b.nationality,
            transfer_fee: 2500.0,
            request_date: new Date().toISOString().replace('T', ' ').substring(0, 19),
            ministry_status: 'quota_approved',
            notes: 'Transferred under approved company Ministry quota.'
        };
        transfers.unshift(newTrf);
        this.setKey('kafala_transfers', transfers);

        b.kafala_status = 'transfer_approved';
        this.setKey('bookings', bookings);

        return {
            success: true,
            message: `Sponsorship transfer for ${b.nationality} domestic worker approved under Ministry quota! Reference #TRF-${newTrf.id} generated.`,
            remaining_quota: q.max_kafala_transfers_allowed - q.kafala_transfers_used
        };
    },
    getHR() { return this.getKey('hr_recruitment'); },
    addCandidate(cand) {
        const list = this.getHR();
        cand.id = list.length > 0 ? Math.max(...list.map(c => c.id)) + 1 : 1;
        list.push(cand);
        this.setKey('hr_recruitment', list);
        return cand;
    },
    updateCandidateStatus(id, newStatus, notes) {
        const list = this.getHR();
        const c = list.find(item => item.id == id);
        if (c) {
            c.interview_status = newStatus;
            if (notes) c.notes = notes;
            this.setKey('hr_recruitment', list);
        }
        return c;
    },
    getCompliance() {
        const quotas = this.getQuotas();
        let totalVisas = 0;
        let totalHourly = 0;
        let totalMonthly = 0;

        quotas.forEach(q => {
            totalVisas += q.total_visas;
            totalHourly += q.hourly_visas;
            totalMonthly += q.monthly_visas;
        });

        const hourlyPercent = totalVisas > 0 ? Number(((totalHourly / totalVisas) * 100).toFixed(2)) : 0;
        const is10PercentMet = hourlyPercent >= 10.0;

        const nationalities = quotas.map(q => {
            const percent = totalVisas > 0 ? Number(((q.total_visas / totalVisas) * 100).toFixed(2)) : 0;
            const isCompliant = percent <= q.max_diversity_percent;
            return {
                ...q,
                percentage_of_total: percent,
                max_allowed_percent: q.max_diversity_percent,
                is_compliant: isCompliant,
                max_kafala_transfers: q.max_kafala_transfers_allowed,
                kafala_transfers_remaining: Math.max(0, q.max_kafala_transfers_allowed - q.kafala_transfers_used),
                kafala_cap_reached: q.kafala_transfers_used >= q.max_kafala_transfers_allowed
            };
        });

        return {
            summary: {
                total_visas: totalVisas,
                hourly_visas: totalHourly,
                monthly_visas: totalMonthly,
                hourly_percentage: hourlyPercent,
                hourly_10_percent_rule_met: is10PercentMet,
                diversity_rule_met: nationalities.every(n => n.is_compliant),
                price_cap_rule_met: true,
                overall_compliance_status: is10PercentMet ? 'FULLY_COMPLIANT' : 'ATTENTION_REQUIRED'
            },
            nationalities: nationalities
        };
    }
};

// Initialize DB on script load
['workers', 'packages', 'bookings', 'time_logs', 'housing_units', 'security_logs', 'fleet', 'dispatch_trips', 'ministry_quotas', 'kafala_transfers', 'hr_recruitment'].forEach(key => {
    DB.getKey(key);
});
