USE `joetech_db`;

INSERT INTO `page_content`
(`page_key`, `section_key`, `title`, `subtitle`, `body`, `json_data`, `status`, `sort_order`)
VALUES
(
  'home',
  'hero',
  'Technology support that keeps people working and businesses moving.',
  'Joetech Solutions',
  'From repairs and networking to device supply and digital support, Joetech Solutions helps customers solve real technology problems with clear communication, practical choices, and dependable follow-up.',
  JSON_OBJECT(
    'actions', JSON_ARRAY(
      JSON_OBJECT('label', 'Request service', 'url', 'http://localhost/joetech/public/service-request.php', 'style', 'primary'),
      JSON_OBJECT('label', 'Book repair', 'url', 'http://localhost/joetech/public/repair-request.php', 'style', 'secondary'),
      JSON_OBJECT('label', 'Browse products', 'url', 'http://localhost/joetech/public/shop.php', 'style', 'secondary'),
      JSON_OBJECT('label', 'Track request', 'url', 'http://localhost/joetech/public/request-status.php', 'style', 'secondary')
    ),
    'stats', JSON_ARRAY(
      JSON_OBJECT('title', 'Repairs', 'text', 'Laptops, desktops, accessories, and upgrades'),
      JSON_OBJECT('title', 'Support', 'text', 'For homes, teams, shops, schools, and offices'),
      JSON_OBJECT('title', 'Supply', 'text', 'Devices, components, and practical tech guidance')
    )
  ),
  'published',
  1
),
(
  'home',
  'highlights',
  'Clear help, practical solutions, and a process people can follow.',
  'Why clients choose us',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Clear service paths for repairs, requests, product enquiries, and tracking',
      'Business-focused support built for homes, shops, schools, and growing teams',
      'A structured admin workflow behind each order, message, request, and repair'
    ),
    'link_label', 'Learn more about our approach',
    'link_url', 'http://localhost/joetech/public/about.php'
  ),
  'published',
  2
),
(
  'home',
  'journey',
  'A straightforward customer journey',
  'How it works',
  'Good service starts by removing friction and guiding customers to the right action from the first page.',
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Choose the service, repair, or product path that matches your need.',
      'Send the request, place the order, or ask for guidance through a clear form.',
      'Receive follow-up, updates, and practical support from the Joetech team.'
    )
  ),
  'published',
  3
),
(
  'home',
  'callout',
  'Tell us what you need and we will guide you to the right next step.',
  'Ready to start?',
  'Use a service request for support work, book a repair for device issues, or browse the shop if you already know what you need.',
  JSON_OBJECT(
    'actions', JSON_ARRAY(
      JSON_OBJECT('label', 'Request service', 'url', 'http://localhost/joetech/public/service-request.php', 'style', 'primary'),
      JSON_OBJECT('label', 'Book repair', 'url', 'http://localhost/joetech/public/repair-request.php', 'style', 'secondary'),
      JSON_OBJECT('label', 'Contact Joetech', 'url', 'http://localhost/joetech/public/contact.php', 'style', 'secondary')
    )
  ),
  'published',
  4
),
(
  'about',
  'hero',
  'A practical technology partner for people, teams, and growing businesses.',
  'About Joetech',
  'Joetech Solutions exists to make technology more useful, more dependable, and easier to work with. We focus on repairs, networking, device supply, and digital support that solve real problems without unnecessary complexity.',
  NULL,
  'published',
  1
),
(
  'about',
  'principles',
  NULL,
  'Our principles',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Keep technology practical, understandable, and worth the investment.',
      'Recommend the simplest reliable path instead of unnecessary complexity.',
      'Support both individual clients and growing organizations with the same care.'
    )
  ),
  'published',
  2
),
(
  'about',
  'story',
  'Technology should solve problems without creating new confusion.',
  'How we work',
  'Our approach is built around clear advice, practical implementation, and support that makes sense for the way clients actually work. We care about outcomes that remain dependable after the initial job is done.',
  JSON_OBJECT(
    'body_2', 'Whether the work is device repair, office connectivity, product supply, or digital support, the goal is the same: help the client move forward with confidence.'
  ),
  'published',
  3
),
(
  'about',
  'steps',
  NULL,
  NULL,
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Understand the problem clearly before recommending the solution.',
      'Choose the most reliable and maintainable path that fits the real need.',
      'Deliver support that remains useful after installation, repair, or setup.'
    )
  ),
  'published',
  4
),
(
  'about',
  'strengths',
  'Core strengths behind our work',
  'What we bring',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      JSON_OBJECT('title', 'Repair and Recovery', 'text', 'Helping devices return to dependable, productive use through diagnostics, replacement, upgrades, and cleanup.'),
      JSON_OBJECT('title', 'Business Support', 'text', 'Supporting offices, schools, shops, and teams with stable technology choices and day-to-day operational support.'),
      JSON_OBJECT('title', 'Digital Enablement', 'text', 'Giving businesses practical websites, setup help, and digital systems they can keep managing after launch.')
    )
  ),
  'published',
  5
),
(
  'services',
  'hero',
  'Support that helps people fix problems, choose the right technology, and keep work moving.',
  'Services',
  'Joetech Solutions provides practical support across repairs, networking, digital services, and product supply. Each service path is designed to be clear from the first click to the final follow-up.',
  NULL,
  'published',
  1
),
(
  'services',
  'sidebar',
  NULL,
  'What to expect',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Clear explanations before technical work begins',
      'Support designed for real day-to-day business needs',
      'A request flow that routes work into the right admin follow-up process'
    )
  ),
  'published',
  2
),
(
  'services',
  'process',
  'Small steps, practical results',
  'Our process',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'You explain the problem, need, or goal.',
      'We guide you to the right service path and next action.',
      'Your request is reviewed, assigned, and followed through with clear communication.'
    )
  ),
  'published',
  3
),
(
  'services',
  'callout',
  'Need help now? Send a structured request and we will follow up with the right next step.',
  'Start a request',
  'Use the service request form for general support work or the repair intake form for device issues that need diagnosis and handling.',
  NULL,
  'published',
  4
),
(
  'contact',
  'hero',
  'Tell us what you need and we will guide you to the right next step.',
  'Contact',
  'Use this page for general enquiries, product questions, business support requests, or any service that does not clearly fit the dedicated request and repair forms.',
  NULL,
  'published',
  1
),
(
  'contact',
  'sidebar',
  NULL,
  'Best for',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'General business enquiries',
      'Product availability and pricing questions',
      'Support needs that require advice before the next action'
    )
  ),
  'published',
  2
),
(
  'contact',
  'flow',
  'Start with a message, not a complicated process',
  'Reach out',
  'We keep communication clear so customers can get help quickly and know where their enquiry is going.',
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Share the problem, service need, or product you are interested in.',
      'Your message is recorded so the team can review and follow up properly.',
      'We reply with the clearest next step, whether that is a quote, service path, or direct support.'
    )
  ),
  'published',
  3
),
(
  'service_request',
  'hero',
  'Tell us what you need built, fixed, or supported.',
  'Service Request',
  'This form sends your request directly into the admin workflow so Joetech can review, quote, and follow up.',
  NULL,
  'published',
  1
),
(
  'service_request',
  'sidebar',
  NULL,
  'Good for',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Website and software enquiries',
      'Networking and office setup',
      'Business support requests that need follow-up'
    )
  ),
  'published',
  2
),
(
  'service_request',
  'flow',
  'Simple intake, clearer follow-up',
  'Request Flow',
  'Your request goes into the admin side where it can be assigned, reviewed, quoted, and tracked.',
  JSON_OBJECT(
    'success_title', 'Request received',
    'success_body', 'Keep this reference if you need to follow up with Joetech about this service request.'
  ),
  'published',
  3
),
(
  'repair_request',
  'hero',
  'Book your device into the repair workflow.',
  'Repair Intake',
  'This sends the issue into the admin repair board so the device can be received, diagnosed, repaired, and tracked.',
  NULL,
  'published',
  1
),
(
  'repair_request',
  'sidebar',
  NULL,
  'Good for',
  NULL,
  JSON_OBJECT(
    'items', JSON_ARRAY(
      'Laptop and desktop faults',
      'Upgrade-related issues',
      'Devices that need diagnostics before repair'
    )
  ),
  'published',
  2
),
(
  'repair_request',
  'flow',
  'Clear intake before technical work begins',
  'Repair Flow',
  'Your repair request goes into the admin repair queue where it can be assigned, diagnosed, and updated through completion.',
  JSON_OBJECT(
    'success_title', 'Repair booked',
    'success_body', 'Keep this reference so Joetech can quickly trace your repair job during follow-up.'
  ),
  'published',
  3
)
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `subtitle` = VALUES(`subtitle`),
  `body` = VALUES(`body`),
  `json_data` = VALUES(`json_data`),
  `status` = VALUES(`status`),
  `sort_order` = VALUES(`sort_order`);
