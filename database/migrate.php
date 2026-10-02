<?php
/**
 * Sagar Advertising CRM - Database Migration & Seeder
 * Runs schema creation and seeds realistic data for demo & production.
 */

declare(strict_types=1);

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'sagar_advertising_crm';

echo "=== Sagar Advertising CRM Migration & Seeder ===\n";

try {
    // 1. Connect without db to ensure database exists
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "[1/4] Creating database {$dbname} if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbname}`;");

    echo "[2/4] Running schema.sql...\n";
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schemaSql);
    echo "  -> Schema created successfully.\n";

    echo "[3/4] Seeding core users, permissions, and settings...\n";

    // Hash passwords
    $adminHash   = password_hash('Admin@123', PASSWORD_BCRYPT);
    $managerHash = password_hash('Manager@123', PASSWORD_BCRYPT);
    $salesHash   = password_hash('Sales@123', PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `phone`, `role`, `department`, `username`, `password_hash`, `status`) VALUES
        ('Vageesh H Hugar (Admin)', 'admin@sagaradvertising.com', '9611620862', 'admin', 'Management', 'admin', ?, 'active'),
        ('Sagar V Hugar (Manager)', 'manager@sagaradvertising.com', '8904184867', 'manager', 'Operations', 'manager', ?, 'active'),
        ('Ramesh Patil (Sales)', 'sales@sagaradvertising.com', '9845123456', 'employee', 'Sales & Marketing', 'sales', ?, 'active')");
    $stmt->execute([$adminHash, $managerHash, $salesHash]);

    // Permissions
    $perms = [
        ['customers.view', 'View Customers', 'Customers', 'View customer list and details'],
        ['customers.create', 'Create Customers', 'Customers', 'Add new customers'],
        ['customers.edit', 'Edit Customers', 'Customers', 'Update customer information'],
        ['customers.delete', 'Delete Customers', 'Customers', 'Remove or soft delete customers'],
        ['quotations.view', 'View Quotations', 'Quotations', 'View quotation list and details'],
        ['quotations.create', 'Create Quotations', 'Quotations', 'Create new quotations'],
        ['quotations.edit', 'Edit Quotations', 'Quotations', 'Edit draft or active quotations'],
        ['quotations.delete', 'Delete Quotations', 'Quotations', 'Delete quotations'],
        ['quotations.send', 'Send Quotation Email', 'Quotations', 'Send quotations directly via email'],
        ['excel.import', 'Import Excel', 'Data Tools', 'Import customers and items from Excel'],
        ['excel.export', 'Export Excel', 'Data Tools', 'Export records to Excel/CSV'],
        ['campaigns.send', 'Send Bulk Campaigns', 'Marketing', 'Create and schedule bulk email campaigns'],
        ['reports.view', 'View Reports', 'Analytics', 'Access business and commission reports'],
        ['services.manage', 'Manage Services', 'Settings', 'Create and update service master catalog'],
        ['employees.manage', 'Manage Employees', 'Settings', 'Create and manage users & permissions'],
        ['settings.manage', 'Manage System Settings', 'Settings', 'Configure company profile, SMTP, backups']
    ];

    $stmtPerm = $pdo->prepare("INSERT INTO `permissions` (`slug`, `name`, `module`, `description`) VALUES (?, ?, ?, ?)");
    foreach ($perms as $p) {
        $stmtPerm->execute($p);
    }

    // Role Permissions mapping
    $rolePermStmt = $pdo->prepare("INSERT INTO `role_permissions` (`role`, `permission_slug`) VALUES (?, ?)");
    // Admin gets all
    foreach ($perms as $p) {
        $rolePermStmt->execute(['admin', $p[0]]);
    }
    // Manager
    $managerPerms = ['customers.view', 'customers.create', 'customers.edit', 'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.send', 'excel.import', 'excel.export', 'campaigns.send', 'reports.view', 'services.manage'];
    foreach ($managerPerms as $mp) {
        $rolePermStmt->execute(['manager', $mp]);
    }
    // Employee
    $empPerms = ['customers.view', 'customers.create', 'quotations.view', 'quotations.create', 'quotations.send'];
    foreach ($empPerms as $ep) {
        $rolePermStmt->execute(['employee', $ep]);
    }

    // Settings
    $settings = [
        ['company_name', 'SAGAR ADVERTISING', 'company'],
        ['company_tagline', 'Your Brand. Our Passion.', 'company'],
        ['contact_person_1', 'Vageesh H Hugar', 'company'],
        ['contact_phone_1', '9611620862', 'company'],
        ['contact_person_2', 'Sagar V Hugar', 'company'],
        ['contact_phone_2', '8904184867', 'company'],
        ['company_address', '#18096, "Shanti Kunj", Akkasaligar Oni, Old-Hubballi, HUBBALLI - 580 024', 'company'],
        ['company_email', 'sagaradvertising7@gmail.com', 'company'],
        ['company_gstin', '29AVPH4223R1ZV', 'company'],
        ['company_msme', 'UDYAM-KR-13-0061429', 'company'],
        ['quotation_prefix', 'SA/QTN/', 'quotation'],
        ['quotation_next_number', '1016', 'quotation'],
        ['default_validity_days', '15', 'quotation'],
        ['default_gst_rate', '18.00', 'quotation'],
        ['default_commission_rate', '15.00', 'quotation'],
        ['commission_mode', 'markup', 'quotation'], // markup = actual + commission, margin = commission inside selling price
        ['default_payment_terms', '50% Advance with Purchase Order, 50% on completion/delivery', 'quotation'],
        ['default_delivery_terms', '3 to 7 working days from artwork approval', 'quotation'],
        ['default_terms_conditions', "1. Rates are valid for 15 days from quotation date.\n2. GST will be charged extra as applicable (18%).\n3. Client to provide high-resolution vector logo and artwork (CDR / AI / PDF).\n4. Civil work, municipal permissions, and electrical point supply at site shall be by client unless specified.\n5. Warranty covers LED power supply & fabrication defects for 1 year.", 'quotation'],
        ['smtp_host', 'smtp.gmail.com', 'smtp'],
        ['smtp_port', '587', 'smtp'],
        ['smtp_encryption', 'tls', 'smtp'],
        ['smtp_username', 'sagaradvertising7@gmail.com', 'smtp'],
        ['smtp_password', '', 'smtp'],
        ['smtp_from_name', 'Sagar Advertising', 'smtp'],
        ['smtp_from_email', 'sagaradvertising7@gmail.com', 'smtp'],
        ['smtp_reply_to', 'sagaradvertising7@gmail.com', 'smtp'],
        ['smtp_mode', 'simulate', 'smtp'] // simulate (stores in storage/mail_logs for offline testing) or live
    ];

    $stmtSet = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `category`) VALUES (?, ?, ?)");
    foreach ($settings as $s) {
        $stmtSet->execute($s);
    }

    echo "[4/4] Seeding Services, Customers, Quotations & Templates...\n";

    // Service Categories
    $categories = [
        ['Signage & Glow Boards', 'High impact outdoor and indoor illuminated signs'],
        ['Digital & LED Displays', '2D and 3D acrylic LED boards, channel letters'],
        ['Flex & Large Format', 'High resolution vinyl, flex banners and hoardings'],
        ['Fabrication & Cladding', 'ACP interior cladding, structural architectural panels'],
        ['Branding & Promotion', 'Inshop promotions, mobile van advertising, wall painting, event branding']
    ];
    $catStmt = $pdo->prepare("INSERT INTO `service_categories` (`name`, `description`) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $catStmt->execute($cat);
    }

    // 11 Sagar Advertising Services from reference
    $services = [
        [1, 'Glow Sign Boards', 'Premium back-lit vinyl with heavy gauge GI frame and energy efficient LED/tube lighting', 'Sq Ft', 180.00, 18.00, 15.00],
        [2, '3D/2D LED Boards', 'Custom fabricated 3D acrylic channel letters with Samsung LED modules & Meanwell power supply', 'Sq Ft', 450.00, 18.00, 18.00],
        [3, 'Flex Boards', 'Star light-blocking flex with vibrant eco-solvent digital printing on sturdy MS tubular frame', 'Sq Ft', 65.00, 18.00, 12.00],
        [1, 'Embossing Boards', 'High relief embossed lettering and metallic finished architectural nameplates', 'Sq Ft', 320.00, 18.00, 15.00],
        [4, 'ACP Interiors & Cladding', 'Aluminium Composite Panel cladding for facade and corporate interior wall lining', 'Sq Ft', 260.00, 18.00, 15.00],
        [5, 'Wall/Shop Painting', 'Durable weather-resistant exterior emulsion painting with high precision brand stencil artwork', 'Sq Ft', 35.00, 18.00, 10.00],
        [5, 'Event Management & Stage Branding', 'Complete corporate expo booth, stage backdrop, podiums, registration desks & arches', 'Day', 15000.00, 18.00, 20.00],
        [5, 'Van Floating & Mobile Advertising', 'Mobile advertising vehicle with custom fabricated acoustic frame, sound and multi-side banners', 'Day', 4500.00, 18.00, 15.00],
        [5, 'Gift Branding & Corporate Merchandise', 'Personalized laser-engraved corporate diaries, pens, trophies, backpacks and mementos', 'Piece', 250.00, 18.00, 20.00],
        [3, 'Hoardings / Pole Boards', 'Prime highway and city junction steel hoarding unipole structures with print mounting', 'Sq Ft', 120.00, 18.00, 15.00],
        [5, 'Inshop Branding & Promotions', 'End-to-end retail store visual merchandising, vinyl floorings, standees, and acrylic counter displays', 'Unit', 8500.00, 18.00, 18.00]
    ];
    $srvStmt = $pdo->prepare("INSERT INTO `services` (`category_id`, `name`, `description`, `default_unit`, `default_price`, `default_gst_rate`, `default_commission_rate`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($services as $srv) {
        $srvStmt->execute($srv);
    }

    // 22 Realistic Customers (Hubballi / Dharwad / Belagavi / Bengaluru businesses)
    $customers = [
        ['SA-CUST-1001', 'KLE Technological University', 'Dr. Ashok Shettar', '9845012341', '08362378100', 'ashok@kletech.ac.in', '9845012341', 'BVB Campus, Vidyanagar', 'Hubballi', 'Karnataka', '580031', '29AAATK0123E1Z4', 'Corporate', 'Referral', 'Premier educational campus. Regular requirements for convocation stages & signages.', 1, 'Active'],
        ['SA-CUST-1002', 'Bapat Jewellers & Sons', 'Vinayak Bapat', '9448123451', '9448123452', 'bapatjewellers.hubli@gmail.com', '9448123451', 'Station Road, Durgad Bail', 'Hubballi', 'Karnataka', '580020', '29AAAFB1234K1Z2', 'Business', 'Direct Visit', 'Flagship showroom LED glow sign and festival promotional campaigns.', 2, 'Active'],
        ['SA-CUST-1003', 'Hotel Naveen Lakeside', 'Sunil Deshpande', '9880123453', '08362379999', 'reservations@hotelnaveen.com', '9880123453', 'Unkal Lake, PB Road', 'Hubballi', 'Karnataka', '580025', '29AAACH4567M1Z8', 'Corporate', 'Sales Call', 'Luxury hospitality. Requires restaurant menu boards, event backdrops and entrance ACP.', 3, 'Active'],
        ['SA-CUST-1004', 'Suchir India Infratech', 'Mahesh Reddy', '9900123454', '9900123455', 'mreddy@suchirindia.in', '9900123454', 'Koppikar Road', 'Hubballi', 'Karnataka', '580020', '29AAECS9876Q1Z1', 'Corporate', 'Direct Visit', 'Real estate layout hoardings, site signage boards and direction arrows.', 1, 'Active'],
        ['SA-CUST-1005', 'Dharwad District Co-Op Milk Union (KMF)', 'Gopal Kulkarni', '9449876541', '08362441234', 'procurement@kmfnandini.coop', '9449876541', 'Lakkamanahalli Industrial Area', 'Dharwad', 'Karnataka', '580004', '29AAAKD0987H1Z6', 'Existing Client', 'Government Tender', 'Nandini parlours glow sign boards across Hubli-Dharwad twin cities.', 2, 'Active'],
        ['SA-CUST-1006', 'VRL Logistics Limited', 'Pradeep Shenoy', '9844012345', '08362237511', 'branding@vrllogistics.com', '9844012345', 'Giriraj Annexe, Circuit House Road', 'Hubballi', 'Karnataka', '580029', '29AAACV5432B1Z5', 'Corporate', 'Corporate Network', 'Pan-Karnataka transport hub signs, warehouse bay numbering and fleet branding.', 1, 'Active'],
        ['SA-CUST-1007', 'Shree Renuka Sugars Ltd', 'Sanjay Patil', '9731012346', '9731012347', 'spatil@renukasugars.com', '9731012346', 'BC 105, Havelock Road', 'Belagavi', 'Karnataka', '590001', '29AAACR3456F1Z3', 'Corporate', 'Existing Client', 'Annual agro-trade expos, safety embossing boards inside mills.', 2, 'Active'],
        ['SA-CUST-1008', 'Girish Medical & Diagnostics', 'Dr. Girish Joshi', '9448054321', '08362267890', 'drgirish.clinic@gmail.com', '9448054321', 'Near KIMS Hospital, Vidyanagar', 'Hubballi', 'Karnataka', '580022', '29AABCG7788P1Z9', 'Business', 'Direct Visit', 'Emergency signage, glow board and doctor nameplates.', 3, 'Active'],
        ['SA-CUST-1009', 'Nityanand Sweets & Namkeen', 'Ramesh Pujar', '9845876512', '9845876513', 'nityanandsweets.hbl@gmail.com', '9845876512', 'Dajiban Peth, Old Hubli', 'Hubballi', 'Karnataka', '580028', '29AABFN2233M1Z0', 'Existing Client', 'Direct Visit', 'Storefront ACP cladding and 3D acrylic glowing letters.', 2, 'Active'],
        ['SA-CUST-1010', 'Karnataka Khadi Gramodyoga Samyukta Sangha', 'Shivanand Hegde', '9448332211', '08362288334', 'bengeriairport@khadiorg.in', '9448332211', 'Bengeri', 'Hubballi', 'Karnataka', '580023', '', 'Corporate', 'Referral', 'National flag heritage gallery exhibitions and festival event banners.', 1, 'Active'],
        ['SA-CUST-1011', 'Siddheshwar Motors (Hero Dealership)', 'Basavaraj Patil', '9880567891', '08362354455', 'siddheshwarmotors@gmail.com', '9880567891', 'Gokul Road Industrial Estate', 'Hubballi', 'Karnataka', '580030', '29AAMFS1122R1Z7', 'Dealer', 'Cold Calling', 'Hero corporate showroom facade renovation and test drive event branding.', 3, 'Active'],
        ['SA-CUST-1012', 'Cotton County Resort', 'Chetan Desai', '9611234567', '9611234568', 'info@cottoncountyresort.com', '9611234567', 'Airport Road', 'Hubballi', 'Karnataka', '580030', '29AAACC9988D1Z4', 'Business', 'Instagram Ad', 'Resort internal direction boards, poolside LED letters, banquet signage.', 2, 'Active'],
        ['SA-CUST-1013', 'Maratha Mandal Engineering College', 'Prof. Rajendra Rao', '9986012398', '08312478901', 'mmec.belgaum@gmail.com', '9986012398', 'RS No. 477/2, Shivabasav Nagar', 'Belagavi', 'Karnataka', '590010', '', 'New Client', 'Website Enquiry', 'Campus entrance gate 3D metal LED lettering and departmental signages.', 1, 'Lead'],
        ['SA-CUST-1014', 'City Clinic & Dialysis Center', 'Dr. Deepa Kulkarni', '9741234560', '08362245566', 'cityclinic.hubli@gmail.com', '9741234560', 'Court Circle, PB Road', 'Hubballi', 'Karnataka', '580029', '29AADFC5544L1Z1', 'Business', 'Direct Visit', 'LED ambulance signage and clinic interior wall panels.', 3, 'Active'],
        ['SA-CUST-1015', 'Apex Agro Chemicals', 'Venkatesh Rao', '9448998877', '9448998878', 'apexagro.hubli@yahoo.com', '9448998877', 'APMC Yard, Amargol', 'Hubballi', 'Karnataka', '580025', '29AAAFA3322K1Z5', 'Business', 'Direct Visit', 'Dealer shop boards in Gadag, Haveri, Bagalkot and Dharwad districts.', 2, 'Active'],
        ['SA-CUST-1016', 'Shri Siddharoodha Swamy Math Trust', 'Mallikarjun Trustee', '9448123999', '08362203456', 'trust@siddharoodhamath.org', '9448123999', 'Siddharoodha Nagar', 'Hubballi', 'Karnataka', '580024', '', 'Existing Client', 'Direct Visit', 'Jathra festival hoardings, parking direction flex boards, arch gates.', 1, 'Active'],
        ['SA-CUST-1017', 'Urban Trends Men Style', 'Farooq Ahmed', '9845667788', '', 'urbantrends.hbl@gmail.com', '9845667788', 'Broad Way, Subhash Road', 'Hubballi', 'Karnataka', '580020', '', 'Individual', 'Direct Visit', 'Storefront LED sign board and window vinyl cutouts.', 3, 'Active'],
        ['SA-CUST-1018', 'Hubballi-Dharwad Smart City Ltd', 'Project Engineer', '9448001122', '08362355555', 'smartcity.hbd@karnataka.gov.in', '9448001122', 'Lamigton Road, Corporation Bldg', 'Hubballi', 'Karnataka', '580020', '29AAACH7766S1Z0', 'Corporate', 'Government Tender', 'Heritage walk signages, civic display totems and garden entry boards.', 1, 'Under Discussion'],
        ['SA-CUST-1019', 'Rajarajeshwari Sarees & Silks', 'Chandrashekhar', '9844221100', '08362261122', 'rr.silks.hubli@gmail.com', '9844221100', 'Durgad Bail, Old Hubli', 'Hubballi', 'Karnataka', '580028', '29AABFR9988H1Z8', 'Business', 'Direct Visit', 'Festival promotion flex hoardings at Chennamma Circle and CBT.', 2, 'Active'],
        ['SA-CUST-1020', 'Pavitra Supermarket', 'Anand Vernekar', '9611889900', '', 'pavitra.supermarket@gmail.com', '9611889900', 'Shirur Park, Vidyanagar', 'Hubballi', 'Karnataka', '580031', '29AAAFP4433E1Z2', 'Business', 'Direct Visit', 'Aisle hanging signages, cash counter branding, entrance LED board.', 3, 'Active'],
        ['SA-CUST-1021', 'Belagavi Biofuels Private Limited', 'Nitin Kadam', '9886001144', '08312456789', 'admin@belagavibiofuels.com', '9886001144', 'Auto Nagar', 'Belagavi', 'Karnataka', '590016', '29AAACB1100F1Z6', 'Corporate', 'Cold Calling', 'Plant safety warning boards, reflective vinyl hazard stickers.', 1, 'Active'],
        ['SA-CUST-1022', 'Smart Edge Coaching Academy', 'Sujata Hiremath', '9740112233', '', 'smartedge.academy@gmail.com', '9740112233', 'Near Keshwapur Circle', 'Hubballi', 'Karnataka', '580023', '', 'Business', 'Referral', 'New batch enrollment road flex banners and canopy tent branding.', 2, 'Active']
    ];

    $custStmt = $pdo->prepare("INSERT INTO `customers` (`customer_code`, `company_name`, `contact_person`, `mobile`, `alternate_mobile`, `email`, `whatsapp`, `address`, `city`, `state`, `pincode`, `gstin`, `customer_type`, `source`, `notes`, `assigned_employee_id`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($customers as $c) {
        $custStmt->execute($c);
    }

    // Follow-ups
    $followups = [
        [1, 1, date('Y-m-d'), '11:00:00', 'Meeting', 'Review convocation stage branding proofs and material samples with Registrar.', 'pending'],
        [2, 2, date('Y-m-d'), '15:30:00', 'Call', 'Confirm acrylic letter thickness and color swatch for Durgad Bail store board.', 'pending'],
        [3, 3, date('Y-m-d', strtotime('-2 days')), '10:00:00', 'WhatsApp', 'Follow-up on poolside LED quotation sent on Monday.', 'pending'],
        [4, 1, date('Y-m-d', strtotime('+2 days')), '16:00:00', 'Meeting', 'Finalize site location for Suchir Infratech highway billboard installation.', 'pending'],
        [5, 2, date('Y-m-d', strtotime('+3 days')), '12:00:00', 'Email', 'Send revised quote for 10 Nandini milk parlour back-lit glow signages.', 'pending'],
        [11, 3, date('Y-m-d', strtotime('+5 days')), '14:00:00', 'Call', 'Showroom facade approval from Hero Regional Office update check.', 'pending']
    ];
    $foStmt = $pdo->prepare("INSERT INTO `customer_followups` (`customer_id`, `user_id`, `followup_date`, `followup_time`, `followup_type`, `notes`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($followups as $fo) {
        $foStmt->execute($fo);
    }

    // Customer Notes
    $notes = [
        [1, 1, 'Client prefers heavy gauge GI frames with anti-rust zinc coating due to open campus exposure.'],
        [2, 2, 'Requires urgent delivery before Akshaya Tritiya festival rush.'],
        [6, 1, 'Transport fleet graphics need high-grade 3M vinyl with 3-year warranty certificate.']
    ];
    $noteStmt = $pdo->prepare("INSERT INTO `customer_notes` (`customer_id`, `user_id`, `note`) VALUES (?, ?, ?)");
    foreach ($notes as $n) {
        $noteStmt->execute($n);
    }

    // 16 Realistic Quotations
    $quotes = [
        // 1. KLE Tech
        [
            'SA/QTN/2026/0001', '2026-09-10', '2026-09-25', 1, 1, 'Convocation 2026 Stage & Campus Branding', 'Direct Inquiry',
            'All installations to be completed 24 hours prior to main event.',
            'markup', 125000.00, 18750.00, 5000.00, 120000.00, 18.00, 21600.00, 2500.00, 4500.00, 0.00, 148600.00, 'Approved',
            'token_001_kletech', '2026-09-11 10:14:00', '2026-09-10 16:30:00',
            [
                ['Event Management & Stage Branding', 'Main auditorium mega stage backdrop with modular aluminum trussing', '40 x 15 ft', 1.00, 'Day', 35000.00, 15.00, 5250.00, 40250.00, 40250.00],
                ['3D/2D LED Boards', 'KLE University Golden Monogram with warm white LED front lighting', '8 x 8 ft', 1.00, 'Sq Ft', 28800.00, 18.00, 5184.00, 33984.00, 33984.00],
                ['Flex Boards', 'Star Flex registration arches and VIP walkway route directional boards', '10 x 5 ft', 6.00, 'Piece', 2800.00, 15.00, 420.00, 3220.00, 19320.00],
                ['Gift Branding & Corporate Merchandise', 'Custom engraved walnut wood commemorative mementos with gold foil crest', 'Standard', 125.00, 'Piece', 200.00, 20.00, 40.00, 240.00, 30000.00]
            ]
        ],
        // 2. Bapat Jewellers
        [
            'SA/QTN/2026/0002', '2026-09-12', '2026-09-27', 2, 2, 'Main Showroom 3D LED Sign & Facade ACP', 'Durgad Bail Store',
            'Night work allowed after 9:30 PM store closing.',
            'markup', 84000.00, 13440.00, 3000.00, 81000.00, 18.00, 14580.00, 1500.00, 3500.00, 0.00, 100580.00, 'Converted',
            'token_002_bapat', '2026-09-13 14:22:00', '2026-09-12 17:00:00',
            [
                ['3D/2D LED Boards', 'BAPAT JEWELLERS 3D Acrylic channel letters with high lum Samsung LEDs', '24 x 4 ft', 96.00, 'Sq Ft', 480.00, 18.00, 86.40, 566.40, 54374.40],
                ['ACP Interiors & Cladding', 'Dark Champagne metallic ACP background facade cladding with steel subframe', '28 x 8 ft', 224.00, 'Sq Ft', 240.00, 15.00, 36.00, 276.00, 61824.00]
            ]
        ],
        // 3. Hotel Naveen
        [
            'SA/QTN/2026/0003', '2026-09-15', '2026-09-30', 3, 3, 'Lakeside Restaurant Glowing Totem & Room Numbers', 'GM Meeting',
            'Weatherproof outdoor silicone sealant mandatory for lakeside moisture.',
            'markup', 62000.00, 9300.00, 0.00, 62000.00, 18.00, 11160.00, 1200.00, 2800.00, 0.00, 77160.00, 'Under Discussion',
            'token_003_naveen', '2026-09-16 09:30:00', '2026-09-15 18:00:00',
            [
                ['Glow Sign Boards', 'Double sided highway entry pylon totem with internal LED lighting', '6 x 15 ft', 90.00, 'Sq Ft', 220.00, 15.00, 33.00, 253.00, 22770.00],
                ['Embossing Boards', 'Cast brass finish metallic embossed room numbers and suite floor directories', '6 x 8 inch', 65.00, 'Piece', 550.00, 15.00, 82.50, 632.50, 41112.50]
            ]
        ],
        // 4. Suchir India
        [
            'SA/QTN/2026/0004', '2026-09-18', '2026-10-03', 4, 1, 'Mega Highway Unipole Hoarding & Site Flags', 'Koppikar Road Office',
            'Structural stability certification by certified civil engineer included.',
            'markup', 145000.00, 21750.00, 7000.00, 138000.00, 18.00, 24840.00, 3000.00, 6000.00, 0.00, 171840.00, 'Approved',
            'token_004_suchir', '2026-09-19 11:45:00', '2026-09-18 15:00:00',
            [
                ['Hoardings / Pole Boards', 'Unipole front-lit billboard structure with digital black-back flex', '40 x 20 ft', 800.00, 'Sq Ft', 130.00, 15.00, 19.50, 149.50, 119600.00],
                ['Flex Boards', 'Plot layout site fencing printed boundary flex sheets with eyelets', '150 x 6 ft', 900.00, 'Sq Ft', 55.00, 15.00, 8.25, 63.25, 56925.00]
            ]
        ],
        // 5. KMF Nandini
        [
            'SA/QTN/2026/0005', '2026-09-20', '2026-10-05', 5, 2, 'KMF 15 Nandini Parlours Glow Sign Board Package', 'Tender 2026/Q3',
            'Standardized KMF blue and white livery as per dairy manual.',
            'markup', 165000.00, 24750.00, 10000.00, 155000.00, 18.00, 27900.00, 4000.00, 7500.00, 0.00, 194400.00, 'Sent',
            'token_005_kmf', NULL, '2026-09-20 14:10:00',
            [
                ['Glow Sign Boards', 'Standard KMF Franchisee glow sign boards with 1" MS square pipe frame', '10 x 3 ft', 15.00, 'Piece', 5800.00, 15.00, 870.00, 6670.00, 100050.00],
                ['Inshop Branding & Promotions', 'Ice cream freezer branding vinyl wrappers & product tariff display boards', 'Unit', 15.00, 'Unit', 4500.00, 15.00, 675.00, 5175.00, 77625.00]
            ]
        ],
        // 6. VRL Logistics
        [
            'SA/QTN/2026/0006', '2026-09-22', '2026-10-07', 6, 1, 'VRL Hubballi Central Hub Neon LED & Bay Signs', 'Transport Division',
            'High visibility reflective sheeting for night operation safety.',
            'markup', 98000.00, 14700.00, 4000.00, 94000.00, 18.00, 16920.00, 2000.00, 4000.00, 0.00, 116920.00, 'Draft',
            'token_006_vrl', NULL, NULL,
            [
                ['3D/2D LED Boards', 'VRL LOGISTICS 3D Acrylic letters in signature deep blue and red colors', '20 x 4 ft', 80.00, 'Sq Ft', 420.00, 15.00, 63.00, 483.00, 38640.00],
                ['Embossing Boards', 'Retro-reflective loading bay directional and danger overhead boards', '4 x 3 ft', 25.00, 'Piece', 2600.00, 15.00, 390.00, 2990.00, 74750.00]
            ]
        ],
        // 7. Renuka Sugars
        [
            'SA/QTN/2026/0007', '2026-09-24', '2026-10-09', 7, 2, 'Safety Embossed Boards & Sugar Mill Facade Board', 'Belagavi Mill',
            'Industrial chemical corrosion resistant PU coating on all frames.',
            'markup', 54000.00, 8100.00, 2000.00, 52000.00, 18.00, 9360.00, 1500.00, 2500.00, 0.00, 65360.00, 'Sent',
            'token_007_renuka', '2026-09-25 15:40:00', '2026-09-24 18:00:00',
            [
                ['Embossing Boards', 'OSHA Standard safety warning embossed aluminum boards with pictograms', '2 x 3 ft', 40.00, 'Piece', 1100.00, 15.00, 165.00, 1265.00, 50600.00],
                ['Flex Boards', 'Boiler house procedural flex charts with heavy duty matte lamination', '8 x 4 ft', 6.00, 'Piece', 1600.00, 15.00, 240.00, 1840.00, 11040.00]
            ]
        ],
        // 8. Siddheshwar Motors Hero
        [
            'SA/QTN/2026/0008', '2026-09-25', '2026-10-10', 11, 3, 'Showroom Facade Redesign & Hero Mobile Float Van', 'Diwali Launch',
            'Mobile van driver and acoustic sound system included for 3 days.',
            'markup', 78000.00, 11700.00, 3000.00, 75000.00, 18.00, 13500.00, 2000.00, 3500.00, 0.00, 94000.00, 'Under Discussion',
            'token_008_hero', '2026-09-26 12:15:00', '2026-09-25 16:45:00',
            [
                ['Van Floating & Mobile Advertising', 'Mobile advertising canopy vehicle with 4-side HD flex and public address system', 'Vehicle', 3.00, 'Day', 4800.00, 15.00, 720.00, 5520.00, 16560.00],
                ['Inshop Branding & Promotions', 'Hero motorcycle launch podium display standees, cutouts and balloon arch', 'Store Unit', 1.00, 'Unit', 12000.00, 15.00, 1800.00, 13800.00, 13800.00],
                ['Glow Sign Boards', 'Hero Genuine Spare Parts back-lit glow sign box', '15 x 3 ft', 45.00, 'Sq Ft', 190.00, 15.00, 28.50, 218.50, 9832.50]
            ]
        ],
        // 9. Cotton County Resort
        [
            'SA/QTN/2026/0009', '2026-09-26', '2026-10-11', 12, 2, 'Luxury Resort Entry Arch & Poolside Glow Signage', 'Airport Road',
            'Warm white LED ambient lighting matching resort architecture.',
            'markup', 112000.00, 16800.00, 5000.00, 107000.00, 18.00, 19260.00, 2500.00, 4500.00, 0.00, 133260.00, 'Approved',
            'token_009_cotton', '2026-09-27 10:00:00', '2026-09-26 17:30:00',
            [
                ['3D/2D LED Boards', 'COTTON COUNTY RESORT & SPA Solid acrylic back-lit illuminated lettering', '22 x 3.5 ft', 77.00, 'Sq Ft', 480.00, 18.00, 86.40, 566.40, 43612.80],
                ['ACP Interiors & Cladding', 'Wood grain finish ACP cladding for resort reception and gate security cabin', '25 x 10 ft', 250.00, 'Sq Ft', 280.00, 15.00, 42.00, 322.00, 80500.00]
            ]
        ],
        // 10. Girish Medical
        [
            'SA/QTN/2026/0010', '2026-09-27', '2026-10-12', 8, 3, 'Clinic Glow Board & Medical Department Nameplates', 'Vidyanagar',
            'Emergency red and green cross LED modules with high brightness.',
            'markup', 28000.00, 4200.00, 1000.00, 27000.00, 18.00, 4860.00, 500.00, 1500.00, 0.00, 33860.00, 'Converted',
            'token_010_girish', '2026-09-28 08:30:00', '2026-09-27 19:00:00',
            [
                ['Glow Sign Boards', 'GIRISH CLINIC 24x7 EMERGENCY Glow Sign Board with LED tube rods', '12 x 3 ft', 36.00, 'Sq Ft', 190.00, 15.00, 28.50, 218.50, 7866.00],
                ['Embossing Boards', 'Acrylic golden embossed nameplates for Doctor consulting cabins', '12 x 4 inch', 12.00, 'Piece', 450.00, 15.00, 67.50, 517.50, 6210.00]
            ]
        ],
        // 11. Nityanand Sweets
        [
            'SA/QTN/2026/0011', '2026-09-28', '2026-10-13', 9, 2, 'Sweet Stall Golden Mirror Acrylic 3D LED Board', 'Dajiban Peth',
            'Festive sweet gift boxes packaging branding samples included.',
            'markup', 51000.00, 7650.00, 2000.00, 49000.00, 18.00, 8820.00, 1000.00, 2000.00, 0.00, 60820.00, 'Approved',
            'token_011_nityanand', '2026-09-29 11:10:00', '2026-09-28 16:00:00',
            [
                ['3D/2D LED Boards', 'NITYANAND SWEETS Golden mirror acrylic 3D letters with warm ambient backlight', '18 x 3 ft', 54.00, 'Sq Ft', 490.00, 18.00, 88.20, 578.20, 31222.80],
                ['Gift Branding & Corporate Merchandise', 'Custom embossed sweet box tin containers with gold stamping', 'Box', 200.00, 'Piece', 95.00, 20.00, 19.00, 114.00, 22800.00]
            ]
        ],
        // 12. Siddharoodha Math
        [
            'SA/QTN/2026/0012', '2026-09-28', '2026-10-13', 16, 1, 'Jathra Mahotsava Welcome Arches & City Hoardings', 'Annual Jathra',
            'Volunteers to coordinate site traffic clearance with police department.',
            'markup', 89000.00, 13350.00, 5000.00, 84000.00, 18.00, 15120.00, 2000.00, 4000.00, 0.00, 105120.00, 'Sent',
            'token_012_math', NULL, '2026-09-28 17:30:00',
            [
                ['Flex Boards', 'Heavy iron arch welcome flex banners for Old Hubballi approach roads', '30 x 10 ft', 3.00, 'Piece', 9500.00, 15.00, 1425.00, 10925.00, 32775.00],
                ['Wall/Shop Painting', 'Math entrance pathway wall devotional mural painting & donor names', 'Wall', 450.00, 'Sq Ft', 38.00, 10.00, 3.80, 41.80, 18810.00]
            ]
        ],
        // 13. Smart City Ltd
        [
            'SA/QTN/2026/0013', '2026-09-29', '2026-10-14', 18, 1, 'Smart City Civic Information Totems & Directional Signs', 'Phase 3 Tender',
            'Solar powered top lighting modules with IP67 waterproofing.',
            'markup', 210000.00, 31500.00, 12000.00, 198000.00, 18.00, 35640.00, 5000.00, 10000.00, 0.00, 248640.00, 'Under Discussion',
            'token_013_smartcity', '2026-09-30 14:00:00', '2026-09-29 18:00:00',
            [
                ['3D/2D LED Boards', 'Stainless Steel 304 Grade Smart City Welcome Monolith with laser cutout branding', '5 x 12 ft', 6.00, 'Piece', 32000.00, 15.00, 4800.00, 36800.00, 220800.00]
            ]
        ],
        // 14. Rajarajeshwari Silks
        [
            'SA/QTN/2026/0014', '2026-09-30', '2026-10-15', 19, 2, 'Festival Saree Sale Central Circle Hoardings', 'Diwali Campaign',
            'Prime location booking for 30 consecutive days.',
            'markup', 42000.00, 6300.00, 1500.00, 40500.00, 18.00, 7290.00, 1000.00, 2000.00, 0.00, 50790.00, 'Draft',
            'token_014_rrsilks', NULL, NULL,
            [
                ['Hoardings / Pole Boards', 'Chennamma Circle prime angle frontlit display printing and mounting', '30 x 15 ft', 450.00, 'Sq Ft', 75.00, 15.00, 11.25, 86.25, 38812.50]
            ]
        ],
        // 15. Maratha Mandal MMEC
        [
            'SA/QTN/2026/0015', '2026-09-30', '2026-10-15', 13, 1, 'College Main Gate Arch 3D Metal Lettering', 'Belagavi Campus',
            'Solid 3mm aluminum letters with weather-resistant gold powder coating.',
            'markup', 68000.00, 10200.00, 2500.00, 65500.00, 18.00, 11790.00, 2000.00, 3500.00, 0.00, 82790.00, 'Rejected',
            'token_015_mmec', '2026-10-01 09:15:00', '2026-09-30 17:00:00',
            [
                ['3D/2D LED Boards', 'MARATHA MANDAL ENGINEERING COLLEGE Golden 3D Acrylic Metal Letters', '32 x 3 ft', 96.00, 'Sq Ft', 520.00, 15.00, 78.00, 598.00, 57408.00]
            ]
        ],
        // 16. Urban Trends Men
        [
            'SA/QTN/2026/0016', '2026-10-01', '2026-10-16', 17, 3, 'Men Apparel Store Acrylic Glow Board & Vinyl Glass Art', 'Broadway Shop',
            'Frost vinyl privacy graphics on changing room glass included.',
            'markup', 24000.00, 3600.00, 500.00, 23500.00, 18.00, 4230.00, 500.00, 1200.00, 0.00, 29430.00, 'Draft',
            'token_016_urban', NULL, NULL,
            [
                ['Glow Sign Boards', 'URBAN TRENDS Sleek LED ultra-slim back-lit lightbox', '10 x 2.5 ft', 25.00, 'Sq Ft', 240.00, 15.00, 36.00, 276.00, 6900.00],
                ['Inshop Branding & Promotions', 'Etched glass vinyl frosted pattern for trial cabins and cashier background', 'Store', 1.00, 'Unit', 5500.00, 15.00, 825.00, 6325.00, 6325.00]
            ]
        ]
    ];

    $qStmt = $pdo->prepare("INSERT INTO `quotations` (
        `quotation_number`, `quotation_date`, `valid_until`, `customer_id`, `sales_person_id`, `project_name`, `reference`,
        `notes`, `commission_mode`, `subtotal`, `total_commission`, `discount_amount`, `taxable_amount`,
        `gst_rate`, `gst_amount`, `transportation_charges`, `installation_charges`, `round_off`, `grand_total`,
        `status`, `view_token`, `viewed_at`, `sent_at`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $qiStmt = $pdo->prepare("INSERT INTO `quotation_items` (
        `quotation_id`, `service_id`, `item_name`, `description`, `size_dimension`, `quantity`, `unit`,
        `actual_price`, `commission_rate`, `commission_amount`, `selling_price`, `total_amount`, `sort_order`
    ) VALUES (?, (SELECT id FROM `services` WHERE `name` = ? LIMIT 1), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $qhStmt = $pdo->prepare("INSERT INTO `quotation_status_history` (`quotation_id`, `user_id`, `old_status`, `new_status`, `comments`) VALUES (?, ?, ?, ?, ?)");

    foreach ($quotes as $q) {
        $items = array_pop($q);
        $qStmt->execute($q);
        $qid = (int)$pdo->lastInsertId();

        // Items
        $sort = 0;
        foreach ($items as $item) {
            $qiStmt->execute([
                $qid,
                $item[0], // service name
                $item[0], // item name
                $item[1], // description
                $item[2], // size
                $item[3], // qty
                $item[4], // unit
                $item[5], // actual price
                $item[6], // comm %
                $item[7], // comm amt
                $item[8], // sell price
                $item[9], // total
                $sort++
            ]);
        }

        // Timeline history
        $qhStmt->execute([$qid, 1, 'Draft', $q[19], 'Initial quotation created with calculated margin and terms.']);
        if ($q[19] !== 'Draft') {
            $qhStmt->execute([$qid, 1, 'Draft', 'Sent', 'Quotation PDF dispatched to client email.']);
            if (in_array($q[19], ['Approved', 'Converted', 'Under Discussion', 'Rejected'])) {
                $qhStmt->execute([$qid, 1, 'Sent', $q[19], 'Client responded and status updated accordingly.']);
            }
        }
    }

    // Email Templates
    $emailTemplates = [
        [
            'Festival Greeting (Diwali)',
            'festival',
            'Happy Diwali Wishes from Sagar Advertising! 🪔✨',
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                <div style="background: #121417; padding: 25px; text-align: center; border-bottom: 4px solid #FF5500;">
                    <h1 style="color: #FF5500; margin: 0; font-size: 24px;">SAGAR ADVERTISING</h1>
                    <p style="color: #aaa; margin: 5px 0 0 0; font-size: 13px; letter-spacing: 1px;">"Your Brand. Our Passion."</p>
                </div>
                <div style="padding: 30px; background: #ffffff;">
                    <h2 style="color: #121417; margin-top: 0;">Dear {{customer_name}},</h2>
                    <p style="color: #444; line-height: 1.6; font-size: 15px;">On the joyous occasion of <strong>Diwali</strong>, the festival of lights, everyone at <strong>Sagar Advertising</strong> extends warm greetings to you, your family, and the entire team at <strong>{{company_name}}</strong>!</p>
                    <p style="color: #444; line-height: 1.6; font-size: 15px;">May the auspicious lights illuminate your brand\'s journey, bringing prosperity, groundbreaking success, and boundless opportunities in the coming year.</p>
                    <div style="background: #FFF5EE; border-left: 4px solid #FF5500; padding: 15px; margin: 20px 0; border-radius: 4px;">
                        <p style="color: #333; margin: 0; font-weight: bold;">Special Festival Offer For You:</p>
                        <p style="color: #666; margin: 5px 0 0 0; font-size: 14px;">Enjoy an exclusive <strong>15% privilege discount</strong> on all our Glow Sign, 3D LED Board, and In-shop promotions through this festive month.</p>
                    </div>
                    <p style="color: #444; line-height: 1.6; font-size: 15px;">Thank you for your valued partnership. We look forward to creating more inspiring brand milestones together.</p>
                    <p style="color: #121417; margin-top: 30px; font-weight: bold;">Warm Regards,<br><span style="color: #FF5500;">Vageesh H Hugar & Sagar V Hugar</span><br><span style="font-weight: normal; color: #777; font-size: 13px;">Sagar Advertising, Hubballi | Phone: 9611620862 / 8904184867</span></p>
                </div>
                <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee;">
                    #18096, "Shanti Kunj", Akkasaligar Oni, Old-Hubballi, HUBBALLI - 580 024<br>
                    <a href="{{unsubscribe_url}}" style="color: #999; text-decoration: underline; margin-top: 8px; display: inline-block;">Unsubscribe from marketing emails</a>
                </div>
            </div>'
        ],
        [
            'New Year Greeting',
            'festival',
            'Wishing You a Prosperous & Successful New Year 2026! 🚀',
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                <div style="background: #121417; padding: 25px; text-align: center; border-bottom: 4px solid #FF5500;">
                    <h1 style="color: #FF5500; margin: 0; font-size: 24px;">SAGAR ADVERTISING</h1>
                    <p style="color: #aaa; margin: 5px 0 0 0; font-size: 13px;">"Your Brand. Our Passion."</p>
                </div>
                <div style="padding: 30px; background: #ffffff;">
                    <h2 style="color: #121417;">Happy New Year, {{customer_name}}!</h2>
                    <p style="color: #444; line-height: 1.6;">As we enter a brand new year, we want to thank you for placing your trust in <strong>Sagar Advertising</strong> for your branding and signage needs.</p>
                    <p style="color: #444; line-height: 1.6;">May this year bring greater heights, vibrant business growth, and stellar accomplishments to <strong>{{company_name}}</strong> in {{city}}!</p>
                    <p style="color: #121417; margin-top: 30px; font-weight: bold;">Team Sagar Advertising</p>
                </div>
                <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee;">
                    <a href="{{unsubscribe_url}}" style="color: #999;">Unsubscribe</a>
                </div>
            </div>'
        ],
        [
            'Promotional Offer (Signage & LED)',
            'promotional',
            'Elevate Your Business Facade: Special Monsoon Signage Upgrade Package',
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                <div style="background: #121417; padding: 25px; text-align: center; border-bottom: 4px solid #FF5500;">
                    <h1 style="color: #FF5500; margin: 0; font-size: 24px;">SAGAR ADVERTISING</h1>
                    <p style="color: #aaa; margin: 5px 0 0 0; font-size: 13px;">"Your Brand. Our Passion."</p>
                </div>
                <div style="padding: 30px; background: #ffffff;">
                    <h2 style="color: #121417;">Hello {{customer_name}},</h2>
                    <p style="color: #444; line-height: 1.6;">Is your storefront ready to captivate every passerby? Upgrade your business exterior with our cutting-edge <strong>3D LED Acrylic Letters</strong> and <strong>ACP Architecture Facade Panels</strong>.</p>
                    <p style="color: #444; line-height: 1.6;">✅ Free 3D visual mockup of your shopfront<br>✅ Genuine Samsung LED modules with 2-year warranty<br>✅ High-speed fabrication and safe on-site installation in {{city}}</p>
                    <p style="color: #444; line-height: 1.6;">Call us today at <strong>9611620862</strong> or reply directly to this email to book your free site measurement.</p>
                    <p style="color: #121417; margin-top: 30px; font-weight: bold;">Best Regards,<br>Sagar Advertising</p>
                </div>
                <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee;">
                    <a href="{{unsubscribe_url}}" style="color: #999;">Unsubscribe</a>
                </div>
            </div>'
        ],
        [
            'Quotation Dispatch Note',
            'quotation',
            'Quotation {{quotation_number}} – Sagar Advertising',
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                <div style="background: #121417; padding: 25px; text-align: center; border-bottom: 4px solid #FF5500;">
                    <h1 style="color: #FF5500; margin: 0; font-size: 24px;">SAGAR ADVERTISING</h1>
                    <p style="color: #aaa; margin: 5px 0 0 0; font-size: 13px;">"Your Brand. Our Passion."</p>
                </div>
                <div style="padding: 30px; background: #ffffff;">
                    <h2 style="color: #121417;">Dear {{customer_name}},</h2>
                    <p style="color: #444; line-height: 1.6;">Thank you for reaching out to <strong>Sagar Advertising</strong>. We are pleased to submit our formal quotation for your project <strong>{{project_name}}</strong>.</p>
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #eee;">
                        <p style="margin: 4px 0;"><strong>Quotation No:</strong> {{quotation_number}}</p>
                        <p style="margin: 4px 0;"><strong>Total Amount:</strong> ₹{{grand_total}} (Incl. GST)</p>
                        <p style="margin: 4px 0;"><strong>Valid Until:</strong> {{valid_until}}</p>
                    </div>
                    <p style="color: #444; line-height: 1.6;">We have attached the detailed PDF quotation with complete line item specifications, sizing, and commercial terms to this email.</p>
                    <p style="color: #444; line-height: 1.6;">You can also view your live digital quotation online anytime: <a href="{{quotation_web_url}}" style="color: #FF5500; font-weight: bold;">Click Here to View Online</a></p>
                    <p style="color: #444; line-height: 1.6;">Please feel free to contact us at <strong>9611620862 / 8904184867</strong> if you have any questions or require custom adjustments.</p>
                    <p style="color: #121417; margin-top: 30px; font-weight: bold;">Warm Regards,<br>Vageesh H Hugar<br><span style="font-weight: normal; color: #777; font-size: 13px;">Sagar Advertising, Hubballi</span></p>
                </div>
            </div>'
        ],
        [
            'Thank You Note & Feedback',
            'custom',
            'Thank You for Partnering with Sagar Advertising! 🙏',
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                <div style="background: #121417; padding: 25px; text-align: center; border-bottom: 4px solid #FF5500;">
                    <h1 style="color: #FF5500; margin: 0; font-size: 24px;">SAGAR ADVERTISING</h1>
                    <p style="color: #aaa; margin: 5px 0 0 0; font-size: 13px;">"Your Brand. Our Passion."</p>
                </div>
                <div style="padding: 30px; background: #ffffff;">
                    <h2 style="color: #121417;">Dear {{customer_name}},</h2>
                    <p style="color: #444; line-height: 1.6;">It was a privilege executing your signage project for <strong>{{company_name}}</strong>. Our team was delighted to bring your brand identity to life!</p>
                    <p style="color: #444; line-height: 1.6;">If you need any maintenance support or upcoming advertising work, we are always just a phone call away.</p>
                    <p style="color: #121417; margin-top: 30px; font-weight: bold;">With Gratitude,<br>Team Sagar Advertising</p>
                </div>
            </div>'
        ]
    ];

    $tplStmt = $pdo->prepare("INSERT INTO `email_templates` (`name`, `category`, `subject`, `body_html`) VALUES (?, ?, ?, ?)");
    foreach ($emailTemplates as $tpl) {
        $tplStmt->execute($tpl);
    }

    // Sample Campaign: Diwali 2026 Customer Wishes
    $campStmt = $pdo->prepare("INSERT INTO `email_campaigns` (`name`, `subject`, `template_id`, `from_name`, `from_email`, `body_html`, `recipient_filter`, `total_recipients`, `pending_count`, `sent_count`, `failed_count`, `status`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $campStmt->execute([
        'Diwali 2026 Customer Wishes',
        'Happy Diwali Wishes from Sagar Advertising! 🪔✨',
        1,
        'Sagar Advertising',
        'sagaradvertising7@gmail.com',
        $emailTemplates[0][3],
        'all',
        22,
        4,
        18,
        0,
        'completed',
        date('Y-m-d H:i:s', strtotime('-5 days'))
    ]);
    $campId = (int)$pdo->lastInsertId();

    // Populate campaign recipients queue
    $recipStmt = $pdo->prepare("INSERT INTO `email_campaign_recipients` (`campaign_id`, `customer_id`, `recipient_email`, `recipient_name`, `status`, `sent_at`) VALUES (?, ?, ?, ?, ?, ?)");
    $custs = $pdo->query("SELECT id, contact_person, email FROM `customers` WHERE email IS NOT NULL AND email != ''")->fetchAll();
    foreach ($custs as $idx => $cu) {
        $status = ($idx < 18) ? 'sent' : 'pending';
        $sentAt = ($status === 'sent') ? date('Y-m-d H:i:s', strtotime('-5 days + ' . ($idx * 3) . ' minutes')) : NULL;
        $recipStmt->execute([$campId, $cu['id'], $cu['email'], $cu['contact_person'], $status, $sentAt]);
    }

    // Quotation Templates
    $qTemplates = [
        [
            'Standard Commercial LED Board Quotation',
            'Ready template for shopfront 3D LED letters with ACP cladding background',
            'Installation includes all electrical connections to client supplied mains box.',
            "1. 50% Advance with Purchase Order.\n2. Balance on physical site handover.\n3. 1 Year warranty on Samsung LED modules.",
            json_encode([
                ['service_id' => 2, 'item_name' => '3D/2D LED Boards', 'description' => 'Fabricated acrylic channel letters with Samsung LED', 'size_dimension' => '15 x 3 ft', 'quantity' => 45, 'unit' => 'Sq Ft', 'actual_price' => 450, 'commission_rate' => 15],
                ['service_id' => 5, 'item_name' => 'ACP Interiors & Cladding', 'description' => 'Metallic exterior facade ACP cladding sheet', 'size_dimension' => '18 x 5 ft', 'quantity' => 90, 'unit' => 'Sq Ft', 'actual_price' => 240, 'commission_rate' => 15]
            ])
        ],
        [
            'Retail Store Glow Signboard Package',
            'Cost-effective back-lit flex glow signboard for retail franchisees',
            'Tube lights replaced with high-efficiency long life LED batten lamps.',
            "1. Delivery within 4 working days.\n2. Rates exclusive of 18% GST.",
            json_encode([
                ['service_id' => 1, 'item_name' => 'Glow Sign Boards', 'description' => 'GI Box back-lit glow sign board with digital print', 'size_dimension' => '12 x 3 ft', 'quantity' => 36, 'unit' => 'Sq Ft', 'actual_price' => 180, 'commission_rate' => 15]
            ])
        ]
    ];
    $qTplStmt = $pdo->prepare("INSERT INTO `quotation_templates` (`name`, `description`, `default_notes`, `default_terms`, `items_json`) VALUES (?, ?, ?, ?, ?)");
    foreach ($qTemplates as $qt) {
        $qTplStmt->execute($qt);
    }

    // Seed activity logs
    $actStmt = $pdo->prepare("INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `record_id`, `details`, `ip_address`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $actStmt->execute([1, 'LOGIN', 'Auth', 1, 'Admin user Vageesh H Hugar logged into the system.', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-1 hour'))]);
    $actStmt->execute([1, 'CREATE', 'Quotations', 1, 'Created new quotation SA/QTN/2026/0001 for KLE Technological University (₹1,48,600).', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-50 minutes'))]);
    $actStmt->execute([2, 'STATUS_CHANGE', 'Quotations', 2, 'Updated quotation SA/QTN/2026/0002 status to Approved.', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-30 minutes'))]);
    $actStmt->execute([1, 'CAMPAIGN_SEND', 'Campaigns', $campId, 'Initiated bulk festival email campaign "Diwali 2026 Customer Wishes" (22 recipients).', '127.0.0.1', date('Y-m-d H:i:s', strtotime('-15 minutes'))]);

    echo "\n=== MIGRATION & SEEDING COMPLETED SUCCESSFULLY! ===\n";
    echo "Summary of seeded records:\n";
    echo " - Users: 3 (Admin, Manager, Employee)\n";
    echo " - Permissions: " . count($perms) . "\n";
    echo " - Services: " . count($services) . " (Full Sagar Advertising catalog)\n";
    echo " - Customers: " . count($customers) . " (Karnataka & Hubballi regional clients)\n";
    echo " - Quotations: " . count($quotes) . " with multi-line items and calculation matrices\n";
    echo " - Email Templates: " . count($emailTemplates) . "\n";
    echo " - Email Campaign: 1 (with queue entries)\n";
    echo " - Quotation Templates: " . count($qTemplates) . "\n";
    echo " - System Settings: " . count($settings) . "\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
