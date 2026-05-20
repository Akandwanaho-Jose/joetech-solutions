USE `joetech_db`;

START TRANSACTION;

/*
  Sample content seed for the current Joetech build.

  What this covers now:
  - site_settings
  - services
  - product_categories
  - products
  - blog_categories
  - blog_posts
  - blog_comments
  - portfolio_projects
  - testimonials
  - users
  - contact_messages
  - service_requests
  - repair_jobs
  - orders
  - order_items
  - payments

  Important:
  - This seeds the pages that already read from the database.
  - Home, About, Services hero copy, and Contact page marketing copy are still
    hardcoded in PHP right now, so SQL alone will not make those pages fully
    database-driven. We would need one more refactor for that.
*/

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`)
VALUES
('site_name', 'Joetech Solutions', 'text', 'Website display name'),
('site_tagline', 'Technology support and digital solutions', 'text', 'Tagline shown in header/SEO'),
('site_email', 'hello@joetechug.com', 'text', 'Primary contact email'),
('site_phone', '+256 700 000000', 'text', 'Primary phone number'),
('whatsapp_number', '+256700000000', 'text', 'WhatsApp contact number'),
('site_address', 'Mbarara City, Uganda', 'text', 'Physical address'),
('facebook_url', 'https://facebook.com/joetechsolutionsug', 'text', 'Facebook page URL'),
('instagram_url', 'https://instagram.com/joetechsolutionsug', 'text', 'Instagram profile URL'),
('linkedin_url', 'https://linkedin.com/company/joetech-solutions', 'text', 'LinkedIn page URL'),
('meta_description', 'Joetech Solutions provides repairs, business IT support, devices, networking, and practical digital solutions in Uganda.', 'text', 'Default SEO meta description'),
('delivery_fee_ugx', '15000', 'number', 'Default delivery fee in UGX'),
('free_delivery_above', '750000', 'number', 'Free delivery threshold in UGX')
ON DUPLICATE KEY UPDATE
  `setting_value` = VALUES(`setting_value`),
  `setting_type` = VALUES(`setting_type`),
  `description` = VALUES(`description`);

INSERT INTO `users` (`full_name`, `email`, `phone`, `password_hash`, `email_verified`, `status`)
VALUES
('Sarah Ainomugisha', 'customer@joetechug.com', '0701000001', '$2y$12$QmAtN5gl8DnhC6ypOHQ8vO40AgZi55pIeXlUFDbCoz7hj8cMgGF8u', 1, 'active')
ON DUPLICATE KEY UPDATE
  `full_name` = VALUES(`full_name`),
  `phone` = VALUES(`phone`),
  `password_hash` = VALUES(`password_hash`),
  `email_verified` = VALUES(`email_verified`),
  `status` = VALUES(`status`);

INSERT INTO `services`
(`title`, `slug`, `description`, `icon`, `image_url`, `price_from`, `currency`, `features`, `status`, `sort_order`)
VALUES
(
  'Computer Repair and Maintenance',
  'computer-repair-maintenance',
  'Diagnostics, hardware repair, software cleanup, and performance upgrades for laptops and desktops used at home, school, and work.',
  'tool',
  NULL,
  50000.00,
  'UGX',
  JSON_ARRAY('Diagnostics and fault tracing', 'Laptop and desktop repair', 'SSD, RAM, and storage upgrades'),
  'active',
  1
),
(
  'Networking and Office Setup',
  'networking-office-setup',
  'Reliable setup for routers, Wi-Fi, printers, shared devices, and small office connectivity.',
  'wifi',
  NULL,
  80000.00,
  'UGX',
  JSON_ARRAY('Router and Wi-Fi setup', 'Printer and device integration', 'Office network support'),
  'active',
  2
),
(
  'Business IT Support',
  'business-it-support',
  'Practical support for teams, schools, shops, and growing businesses that need dependable technology on a daily basis.',
  'briefcase',
  NULL,
  120000.00,
  'UGX',
  JSON_ARRAY('On-site support', 'Remote troubleshooting', 'Device setup and maintenance'),
  'active',
  3
),
(
  'Website and Digital Support',
  'website-digital-support',
  'Simple websites, technical updates, and practical digital support for businesses that want something manageable and professional.',
  'globe',
  NULL,
  350000.00,
  'UGX',
  JSON_ARRAY('Business website setup', 'Content and technical updates', 'Digital support for small teams'),
  'active',
  4
),
(
  'Device Supply and Procurement Advice',
  'device-supply-procurement-advice',
  'Support for choosing and sourcing laptops, accessories, and business-ready technology without guesswork.',
  'shopping-bag',
  NULL,
  NULL,
  'UGX',
  JSON_ARRAY('Laptop recommendations', 'Accessory sourcing', 'Business purchase guidance'),
  'active',
  5
),
(
  'Creative and Brand Design Support',
  'creative-brand-design-support',
  'Brand graphics, marketing visuals, and business support materials designed for practical use across print and digital channels.',
  'palette',
  NULL,
  150000.00,
  'UGX',
  JSON_ARRAY('Social media graphics', 'Flyers and posters', 'Basic brand collateral'),
  'active',
  6
)
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `description` = VALUES(`description`),
  `icon` = VALUES(`icon`),
  `image_url` = VALUES(`image_url`),
  `price_from` = VALUES(`price_from`),
  `currency` = VALUES(`currency`),
  `features` = VALUES(`features`),
  `status` = VALUES(`status`),
  `sort_order` = VALUES(`sort_order`),
  `deleted_at` = NULL;

INSERT INTO `product_categories`
(`name`, `slug`, `description`, `image_url`, `sort_order`, `status`)
VALUES
('Laptops', 'laptops', 'Business laptops, study machines, and dependable everyday computers.', NULL, 1, 'active'),
('Accessories', 'accessories', 'Keyboards, mice, bags, adapters, and essential computer accessories.', NULL, 2, 'active'),
('Components', 'components', 'Storage, memory, and upgrade components for better device performance.', NULL, 3, 'active'),
('Networking', 'networking', 'Routers, cables, and connectivity equipment for home and office setups.', NULL, 4, 'active'),
('Power and Backup', 'power-backup', 'UPS devices, surge protection, and business continuity accessories.', NULL, 5, 'active')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `image_url` = VALUES(`image_url`),
  `sort_order` = VALUES(`sort_order`),
  `status` = VALUES(`status`);

INSERT INTO `products`
(`category_id`, `created_by`, `updated_by`, `name`, `slug`, `sku`, `description`, `specifications`, `price`, `old_price`, `stock_qty`, `min_stock_level`, `condition_type`, `brand`, `model`, `status`)
VALUES
(
  (SELECT `id` FROM `product_categories` WHERE `slug` = 'laptops' LIMIT 1),
  1,
  1,
  'Lenovo ThinkPad E14',
  'lenovo-thinkpad-e14',
  'JT-LAP-001',
  'A dependable business laptop for office productivity, online work, school administration, and day-to-day business tasks.',
  JSON_OBJECT('Processor', 'Intel Core i5', 'RAM', '8GB', 'Storage', '512GB SSD', 'Display', '14-inch FHD'),
  2450000.00,
  2650000.00,
  6,
  2,
  'new',
  'Lenovo',
  'ThinkPad E14',
  'active'
),
(
  (SELECT `id` FROM `product_categories` WHERE `slug` = 'laptops' LIMIT 1),
  1,
  1,
  'HP ProBook 440 G8',
  'hp-probook-440-g8',
  'JT-LAP-002',
  'A professional laptop built for accounting, operations, administration, and general office workloads.',
  JSON_OBJECT('Processor', 'Intel Core i5', 'RAM', '8GB', 'Storage', '256GB SSD', 'Display', '14-inch HD'),
  2250000.00,
  NULL,
  4,
  2,
  'refurbished',
  'HP',
  'ProBook 440 G8',
  'active'
),
(
  (SELECT `id` FROM `product_categories` WHERE `slug` = 'accessories' LIMIT 1),
  1,
  1,
  'Logitech Wireless Mouse M185',
  'logitech-wireless-mouse-m185',
  'JT-ACC-001',
  'Reliable wireless mouse for office desks, travel setups, and general computer use.',
  JSON_OBJECT('Connectivity', '2.4GHz wireless', 'Battery', 'AA', 'Use Case', 'Office and mobile work'),
  65000.00,
  75000.00,
  18,
  5,
  'new',
  'Logitech',
  'M185',
  'active'
),
(
  (SELECT `id` FROM `product_categories` WHERE `slug` = 'components' LIMIT 1),
  1,
  1,
  'Kingston 512GB SSD Upgrade',
  'kingston-512gb-ssd-upgrade',
  'JT-CMP-001',
  'Solid-state storage upgrade for faster boot times, better application performance, and improved reliability.',
  JSON_OBJECT('Capacity', '512GB', 'Interface', 'SATA', 'Warranty', '1 year'),
  230000.00,
  NULL,
  15,
  4,
  'new',
  'Kingston',
  'A400 512GB',
  'active'
),
(
  (SELECT `id` FROM `product_categories` WHERE `slug` = 'networking' LIMIT 1),
  1,
  1,
  'TP-Link Dual Band Router',
  'tp-link-dual-band-router',
  'JT-NET-001',
  'Practical home and small office router for better wireless coverage and everyday internet stability.',
  JSON_OBJECT('Bands', '2.4GHz and 5GHz', 'Ports', '4 LAN', 'Use Case', 'Home and office networking'),
  185000.00,
  NULL,
  9,
  3,
  'new',
  'TP-Link',
  'Archer C64',
  'active'
),
(
  (SELECT `id` FROM `product_categories` WHERE `slug` = 'power-backup' LIMIT 1),
  1,
  1,
  '650VA UPS Backup Unit',
  '650va-ups-backup-unit',
  'JT-PWR-001',
  'Entry-level power backup for desktop stations, routers, and critical office electronics.',
  JSON_OBJECT('Capacity', '650VA', 'Use Case', 'Desktop and router backup', 'Output', 'AC backup'),
  320000.00,
  NULL,
  5,
  2,
  'new',
  'Mercury',
  '650VA',
  'active'
)
ON DUPLICATE KEY UPDATE
  `category_id` = VALUES(`category_id`),
  `updated_by` = VALUES(`updated_by`),
  `name` = VALUES(`name`),
  `sku` = VALUES(`sku`),
  `description` = VALUES(`description`),
  `specifications` = VALUES(`specifications`),
  `price` = VALUES(`price`),
  `old_price` = VALUES(`old_price`),
  `stock_qty` = VALUES(`stock_qty`),
  `min_stock_level` = VALUES(`min_stock_level`),
  `condition_type` = VALUES(`condition_type`),
  `brand` = VALUES(`brand`),
  `model` = VALUES(`model`),
  `status` = VALUES(`status`),
  `deleted_at` = NULL;

DELETE FROM `product_images`
WHERE `product_id` IN (
  SELECT `id`
  FROM `products`
  WHERE `slug` IN (
    'lenovo-thinkpad-e14',
    'hp-probook-440-g8',
    'logitech-wireless-mouse-m185',
    'kingston-512gb-ssd-upgrade',
    'tp-link-dual-band-router',
    '650va-ups-backup-unit'
  )
);

/*
  Set real image paths after uploading files into /uploads/products/.
  Leaving product_images empty is safer than pointing the UI at broken files.
*/

INSERT INTO `blog_categories` (`name`, `slug`, `description`)
VALUES
('Tech Tips', 'tech-tips', 'Short practical advice for device care, productivity, and routine support decisions.'),
('Business IT', 'business-it', 'Articles focused on office technology, connectivity, and support workflows.'),
('Buying Guides', 'buying-guides', 'Advice for choosing devices, upgrades, and accessories without guesswork.')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

INSERT INTO `blog_posts`
(`staff_id`, `category_id`, `title`, `slug`, `excerpt`, `body`, `cover_image`, `status`, `is_featured`, `views`, `read_time_min`, `published_at`)
VALUES
(
  1,
  (SELECT `id` FROM `blog_categories` WHERE `slug` = 'tech-tips' LIMIT 1),
  'How to keep a business laptop fast and reliable',
  'keep-business-laptop-fast-reliable',
  'A few simple maintenance habits can reduce slowdowns, overheating, and expensive downtime.',
  'A business laptop does not need complicated maintenance to stay reliable. Start by keeping storage free, installing updates on schedule, and replacing failing drives before they cause serious data loss.

For machines used every day, adding more RAM or moving from an old hard drive to an SSD often gives the biggest improvement. The goal is not to chase specifications. The goal is to keep the machine responsive for the work it must do.

If the device is already running hot, freezing, or taking too long to start, it is usually time for diagnosis instead of guessing. Early support is cheaper than major failure.',
  NULL,
  'published',
  1,
  24,
  4,
  NOW() - INTERVAL 9 DAY
),
(
  1,
  (SELECT `id` FROM `blog_categories` WHERE `slug` = 'business-it' LIMIT 1),
  'What a small office needs for stable internet and shared devices',
  'small-office-stable-internet-shared-devices',
  'Reliable office connectivity usually comes from good setup choices, not expensive complexity.',
  'A small office usually needs three things to work well: stable internet, a properly placed router, and a clear plan for shared devices such as printers and scanners.

Weak Wi-Fi often comes from poor placement, overloaded consumer equipment, or too many devices competing on the same setup. A better layout and the right router often solve more problems than buying random hardware.

If your team depends on internet for communication, billing, or cloud tools, downtime quickly becomes a business cost. That is why good office setup should be treated as part of operations, not an afterthought.',
  NULL,
  'published',
  1,
  18,
  5,
  NOW() - INTERVAL 6 DAY
),
(
  1,
  (SELECT `id` FROM `blog_categories` WHERE `slug` = 'buying-guides' LIMIT 1),
  'How to choose the right laptop for office work',
  'choose-right-laptop-for-office-work',
  'The best office laptop is the one that fits the actual workload, support needs, and budget.',
  'When buying a laptop for office work, focus first on workload. Accounting, browser-heavy tasks, online meetings, and day-to-day administration do not all require the same machine.

Look at processor class, RAM, storage type, battery health, and repairability. In many cases, a dependable refurbished business laptop is a smarter purchase than a flashy consumer model with poor durability.

If you are buying for a team, consistency matters too. Similar models are easier to support, easier to repair, and easier to keep productive over time.',
  NULL,
  'published',
  0,
  10,
  4,
  NOW() - INTERVAL 3 DAY
),
(
  1,
  (SELECT `id` FROM `blog_categories` WHERE `slug` = 'tech-tips' LIMIT 1),
  'Signs your computer needs an SSD upgrade',
  'signs-computer-needs-ssd-upgrade',
  'Slow startup, long loading times, and system lag often point to storage as the real problem.',
  'A machine that takes too long to start, freezes during simple tasks, or struggles with updates may not need replacing yet. Often, it needs faster storage.

An SSD upgrade improves boot time, responsiveness, and the overall feel of a machine without forcing a full device replacement. That makes it one of the most practical upgrades for business and everyday users.

If your current computer is otherwise in good condition, upgrading storage can extend its life and reduce replacement cost.',
  NULL,
  'published',
  0,
  7,
  3,
  NOW() - INTERVAL 1 DAY
)
ON DUPLICATE KEY UPDATE
  `staff_id` = VALUES(`staff_id`),
  `category_id` = VALUES(`category_id`),
  `title` = VALUES(`title`),
  `excerpt` = VALUES(`excerpt`),
  `body` = VALUES(`body`),
  `cover_image` = VALUES(`cover_image`),
  `status` = VALUES(`status`),
  `is_featured` = VALUES(`is_featured`),
  `views` = VALUES(`views`),
  `read_time_min` = VALUES(`read_time_min`),
  `published_at` = VALUES(`published_at`),
  `deleted_at` = NULL;

DELETE FROM `blog_comments`
WHERE `guest_email` IN ('patrick@kash.co.ug', 'admin@hillview.ac.ug');

INSERT INTO `blog_comments`
(`post_id`, `user_id`, `guest_name`, `guest_email`, `content`, `status`, `ip_address`, `moderation_note`, `created_at`)
VALUES
(
  (SELECT `id` FROM `blog_posts` WHERE `slug` = 'keep-business-laptop-fast-reliable' LIMIT 1),
  NULL,
  'Patrick K.',
  'patrick@kash.co.ug',
  'This is exactly what we needed. Upgrading storage made a huge difference for our office machine.',
  'approved',
  '127.0.0.1',
  'Approved sample comment',
  NOW() - INTERVAL 5 DAY
),
(
  (SELECT `id` FROM `blog_posts` WHERE `slug` = 'small-office-stable-internet-shared-devices' LIMIT 1),
  NULL,
  'Hillview School Admin',
  'admin@hillview.ac.ug',
  'Very practical advice. Router placement and device setup solved issues we had blamed on the internet provider.',
  'approved',
  '127.0.0.1',
  'Approved sample comment',
  NOW() - INTERVAL 2 DAY
);

INSERT INTO `portfolio_projects`
(`service_id`, `title`, `slug`, `description`, `image_url`, `gallery_json`, `client_name`, `project_url`, `technologies`, `completed_date`, `is_featured`, `sort_order`)
VALUES
(
  (SELECT `id` FROM `services` WHERE `slug` = 'networking-office-setup' LIMIT 1),
  'Small Office Network Refresh',
  'small-office-network-refresh',
  'Improved Wi-Fi coverage, printer access, and day-to-day internet stability for a busy office with shared workstations.',
  NULL,
  JSON_ARRAY(),
  'Kaaro Legal Associates',
  NULL,
  JSON_ARRAY('Router setup', 'Wi-Fi optimisation', 'Shared printer configuration'),
  CURDATE() - INTERVAL 60 DAY,
  1,
  1
),
(
  (SELECT `id` FROM `services` WHERE `slug` = 'computer-repair-maintenance' LIMIT 1),
  'Laptop Upgrade and Recovery Programme',
  'laptop-upgrade-recovery-programme',
  'Recovered staff laptops with SSD upgrades, cleanup, and replacement of failing accessories to extend device life and reduce replacement cost.',
  NULL,
  JSON_ARRAY(),
  'Ankole Retail Supplies',
  NULL,
  JSON_ARRAY('SSD upgrades', 'System cleanup', 'Diagnostics and repair'),
  CURDATE() - INTERVAL 35 DAY,
  1,
  2
),
(
  (SELECT `id` FROM `services` WHERE `slug` = 'website-digital-support' LIMIT 1),
  'Business Website Launch Support',
  'business-website-launch-support',
  'Created and launched a clear online presence for a local business that needed a professional website and structured support after launch.',
  NULL,
  JSON_ARRAY(),
  'Rwebiko Trading',
  'https://example.com',
  JSON_ARRAY('Content structure', 'Business website setup', 'Launch support'),
  CURDATE() - INTERVAL 18 DAY,
  0,
  3
)
ON DUPLICATE KEY UPDATE
  `service_id` = VALUES(`service_id`),
  `title` = VALUES(`title`),
  `description` = VALUES(`description`),
  `image_url` = VALUES(`image_url`),
  `gallery_json` = VALUES(`gallery_json`),
  `client_name` = VALUES(`client_name`),
  `project_url` = VALUES(`project_url`),
  `technologies` = VALUES(`technologies`),
  `completed_date` = VALUES(`completed_date`),
  `is_featured` = VALUES(`is_featured`),
  `sort_order` = VALUES(`sort_order`),
  `deleted_at` = NULL;

DELETE FROM `testimonials`
WHERE `full_name` IN ('Mercy Tumusiime', 'Brian Twinomujuni');

INSERT INTO `testimonials`
(`user_id`, `project_id`, `full_name`, `company_name`, `content`, `rating`, `image_url`, `status`, `is_featured`)
VALUES
(
  NULL,
  (SELECT `id` FROM `portfolio_projects` WHERE `slug` = 'small-office-network-refresh' LIMIT 1),
  'Mercy Tumusiime',
  'Kaaro Legal Associates',
  'Joetech gave us a cleaner, more stable office setup and explained every step clearly. The support felt practical and reliable.',
  5,
  NULL,
  'approved',
  1
),
(
  NULL,
  (SELECT `id` FROM `portfolio_projects` WHERE `slug` = 'laptop-upgrade-recovery-programme' LIMIT 1),
  'Brian Twinomujuni',
  'Ankole Retail Supplies',
  'The team helped us recover older devices instead of forcing a full replacement. It saved money and improved performance immediately.',
  5,
  NULL,
  'approved',
  1
);

DELETE FROM `contact_messages`
WHERE `email` IN ('info@kaarolegal.com', 'director@hillview.ac.ug');

INSERT INTO `contact_messages`
(`user_id`, `assigned_staff_id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `ip_address`, `created_at`)
VALUES
(
  NULL,
  1,
  'Mercy Tumusiime',
  'info@kaarolegal.com',
  '0782000011',
  'Office support retainer',
  'We would like monthly support for our office devices, internet setup, and printer maintenance.',
  'read',
  '127.0.0.1',
  NOW() - INTERVAL 7 DAY
),
(
  NULL,
  NULL,
  'Hillview School',
  'director@hillview.ac.ug',
  '0782000012',
  'Computer lab upgrade guidance',
  'We need help choosing laptops and networking equipment for a small training lab.',
  'unread',
  '127.0.0.1',
  NOW() - INTERVAL 1 DAY
);

INSERT INTO `service_requests`
(`user_id`, `service_id`, `assigned_staff_id`, `request_ref`, `full_name`, `email`, `phone`, `company_name`, `project_title`, `description`, `budget_min`, `budget_max`, `deadline_date`, `status`, `source`, `created_at`)
VALUES
(
  (SELECT `id` FROM `users` WHERE `email` = 'customer@joetechug.com' LIMIT 1),
  (SELECT `id` FROM `services` WHERE `slug` = 'business-it-support' LIMIT 1),
  1,
  'REQ-202603-1001',
  'Sarah Ainomugisha',
  'customer@joetechug.com',
  '0701000001',
  'Ainomu Stationers',
  'Office support setup',
  'We need support for three office laptops, one shared printer, and more stable Wi-Fi in our shop office.',
  200000.00,
  600000.00,
  CURDATE() + INTERVAL 10 DAY,
  'reviewing',
  'website',
  NOW() - INTERVAL 5 DAY
),
(
  NULL,
  (SELECT `id` FROM `services` WHERE `slug` = 'website-digital-support' LIMIT 1),
  NULL,
  'REQ-202603-1002',
  'Prossy Kiconco',
  'prossy@rwebiko.com',
  '0701000002',
  'Rwebiko Trading',
  'Business website redesign',
  'We want a cleaner website with service pages, contact forms, and a more professional presentation of our business.',
  800000.00,
  1800000.00,
  CURDATE() + INTERVAL 20 DAY,
  'new',
  'website',
  NOW() - INTERVAL 2 DAY
)
ON DUPLICATE KEY UPDATE
  `user_id` = VALUES(`user_id`),
  `service_id` = VALUES(`service_id`),
  `assigned_staff_id` = VALUES(`assigned_staff_id`),
  `full_name` = VALUES(`full_name`),
  `email` = VALUES(`email`),
  `phone` = VALUES(`phone`),
  `company_name` = VALUES(`company_name`),
  `project_title` = VALUES(`project_title`),
  `description` = VALUES(`description`),
  `budget_min` = VALUES(`budget_min`),
  `budget_max` = VALUES(`budget_max`),
  `deadline_date` = VALUES(`deadline_date`),
  `status` = VALUES(`status`),
  `source` = VALUES(`source`);

INSERT INTO `repair_jobs`
(`user_id`, `assigned_staff_id`, `repair_ref`, `customer_name`, `customer_email`, `customer_phone`, `device_type`, `brand`, `model`, `serial_number`, `issue_description`, `diagnosis`, `repair_status`, `estimated_cost`, `final_cost`, `received_at`, `notes`)
VALUES
(
  (SELECT `id` FROM `users` WHERE `email` = 'customer@joetechug.com' LIMIT 1),
  1,
  'REP-202603-1001',
  'Sarah Ainomugisha',
  'customer@joetechug.com',
  '0701000001',
  'Laptop',
  'HP',
  'ProBook 440 G8',
  'HP-440G8-1001',
  'The laptop takes too long to boot and freezes during browser use.',
  'Drive health is poor and the machine needs an SSD replacement plus system cleanup.',
  'awaiting_approval',
  230000.00,
  NULL,
  NOW() - INTERVAL 4 DAY,
  'Customer approved diagnosis pending parts confirmation.'
),
(
  NULL,
  1,
  'REP-202603-1002',
  'Paul Kanyesigye',
  'paul@ankoleretail.com',
  '0701000003',
  'Desktop',
  'Dell',
  'OptiPlex 7050',
  'DL-7050-2050',
  'The desktop powers on but shows no display and emits warning beeps.',
  'Fault traced to RAM and display connection. Replacement and testing completed.',
  'ready',
  90000.00,
  90000.00,
  NOW() - INTERVAL 8 DAY,
  'Client informed that the machine is ready for collection.'
)
ON DUPLICATE KEY UPDATE
  `user_id` = VALUES(`user_id`),
  `assigned_staff_id` = VALUES(`assigned_staff_id`),
  `customer_name` = VALUES(`customer_name`),
  `customer_email` = VALUES(`customer_email`),
  `customer_phone` = VALUES(`customer_phone`),
  `device_type` = VALUES(`device_type`),
  `brand` = VALUES(`brand`),
  `model` = VALUES(`model`),
  `serial_number` = VALUES(`serial_number`),
  `issue_description` = VALUES(`issue_description`),
  `diagnosis` = VALUES(`diagnosis`),
  `repair_status` = VALUES(`repair_status`),
  `estimated_cost` = VALUES(`estimated_cost`),
  `final_cost` = VALUES(`final_cost`),
  `received_at` = VALUES(`received_at`),
  `notes` = VALUES(`notes`);

INSERT INTO `orders`
(`user_id`, `assigned_staff_id`, `cart_id`, `order_ref`, `full_name`, `email`, `phone`, `delivery_address`, `city`, `country`, `payment_method`, `payment_status`, `delivery_status`, `subtotal`, `discount_amount`, `delivery_fee`, `total_amount`, `notes`, `created_at`)
VALUES
(
  (SELECT `id` FROM `users` WHERE `email` = 'customer@joetechug.com' LIMIT 1),
  1,
  NULL,
  'JT-2026-10001',
  'Sarah Ainomugisha',
  'customer@joetechug.com',
  '0701000001',
  'High Street Plot 14, Mbarara',
  'Mbarara',
  'Uganda',
  'mobile_money',
  'paid',
  'confirmed',
  2515000.00,
  0.00,
  15000.00,
  2530000.00,
  'Deliver during working hours.',
  NOW() - INTERVAL 12 DAY
),
(
  NULL,
  1,
  NULL,
  'JT-2026-10002',
  'Crestline Consult',
  'admin@crestline.ug',
  '0701000004',
  'Boma Road, Mbarara',
  'Mbarara',
  'Uganda',
  'cash_on_delivery',
  'pending',
  'processing',
  415000.00,
  0.00,
  15000.00,
  430000.00,
  'Please call before dispatch.',
  NOW() - INTERVAL 3 DAY
)
ON DUPLICATE KEY UPDATE
  `user_id` = VALUES(`user_id`),
  `assigned_staff_id` = VALUES(`assigned_staff_id`),
  `full_name` = VALUES(`full_name`),
  `email` = VALUES(`email`),
  `phone` = VALUES(`phone`),
  `delivery_address` = VALUES(`delivery_address`),
  `city` = VALUES(`city`),
  `country` = VALUES(`country`),
  `payment_method` = VALUES(`payment_method`),
  `payment_status` = VALUES(`payment_status`),
  `delivery_status` = VALUES(`delivery_status`),
  `subtotal` = VALUES(`subtotal`),
  `discount_amount` = VALUES(`discount_amount`),
  `delivery_fee` = VALUES(`delivery_fee`),
  `total_amount` = VALUES(`total_amount`),
  `notes` = VALUES(`notes`),
  `created_at` = VALUES(`created_at`);

DELETE FROM `order_items`
WHERE `order_id` IN (
  SELECT `id` FROM `orders` WHERE `order_ref` IN ('JT-2026-10001', 'JT-2026-10002')
);

INSERT INTO `order_items`
(`order_id`, `product_id`, `product_name`, `product_sku`, `unit_price`, `quantity`, `subtotal`)
VALUES
(
  (SELECT `id` FROM `orders` WHERE `order_ref` = 'JT-2026-10001' LIMIT 1),
  (SELECT `id` FROM `products` WHERE `slug` = 'lenovo-thinkpad-e14' LIMIT 1),
  'Lenovo ThinkPad E14',
  'JT-LAP-001',
  2450000.00,
  1,
  2450000.00
),
(
  (SELECT `id` FROM `orders` WHERE `order_ref` = 'JT-2026-10001' LIMIT 1),
  (SELECT `id` FROM `products` WHERE `slug` = 'logitech-wireless-mouse-m185' LIMIT 1),
  'Logitech Wireless Mouse M185',
  'JT-ACC-001',
  65000.00,
  1,
  65000.00
),
(
  (SELECT `id` FROM `orders` WHERE `order_ref` = 'JT-2026-10002' LIMIT 1),
  (SELECT `id` FROM `products` WHERE `slug` = 'kingston-512gb-ssd-upgrade' LIMIT 1),
  'Kingston 512GB SSD Upgrade',
  'JT-CMP-001',
  230000.00,
  1,
  230000.00
),
(
  (SELECT `id` FROM `orders` WHERE `order_ref` = 'JT-2026-10002' LIMIT 1),
  (SELECT `id` FROM `products` WHERE `slug` = 'tp-link-dual-band-router' LIMIT 1),
  'TP-Link Dual Band Router',
  'JT-NET-001',
  185000.00,
  1,
  185000.00
);

INSERT INTO `payments`
(`order_id`, `repair_id`, `quote_id`, `recorded_by`, `payment_ref`, `provider`, `payment_method`, `payer_phone`, `amount`, `currency`, `status`, `transaction_time`, `provider_response`, `note`)
VALUES
(
  (SELECT `id` FROM `orders` WHERE `order_ref` = 'JT-2026-10001' LIMIT 1),
  NULL,
  NULL,
  1,
  'PAY-JT-2026-10001',
  'MTN',
  'mobile_money',
  '0701000001',
  2530000.00,
  'UGX',
  'successful',
  NOW() - INTERVAL 12 DAY,
  JSON_OBJECT('status', 'successful', 'provider_ref', 'MTN-778812'),
  'Sample successful payment for seeded order.'
)
ON DUPLICATE KEY UPDATE
  `order_id` = VALUES(`order_id`),
  `recorded_by` = VALUES(`recorded_by`),
  `provider` = VALUES(`provider`),
  `payment_method` = VALUES(`payment_method`),
  `payer_phone` = VALUES(`payer_phone`),
  `amount` = VALUES(`amount`),
  `currency` = VALUES(`currency`),
  `status` = VALUES(`status`),
  `transaction_time` = VALUES(`transaction_time`),
  `provider_response` = VALUES(`provider_response`),
  `note` = VALUES(`note`);

COMMIT;
