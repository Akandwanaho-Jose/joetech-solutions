# Joetech Solutions

Plain PHP website. No frameworks. MySQL 8.0+.

## Setup

1. Copy project to your web server (e.g. `/var/www/html/joetech` or `htdocs/joetech`)
2. Edit `config/config.php` — set `SITE_URL`
3. Edit `config/db.php` — set DB credentials
4. Import the database schema and seed files. Include `password_resets_schema.sql` so forgot-password links can be created.
5. Make `uploads/` writable: `chmod -R 775 uploads/`
6. Open `http://localhost/joetech/public/` in your browser

## Email setup

Forgot-password emails use SMTP settings from `.env`. For Brevo, use:

```
SMTP_HOST=smtp-relay.brevo.com
SMTP_PORT=587
SMTP_USER=your_brevo_smtp_login
SMTP_PASS=your_fresh_brevo_smtp_key
SMTP_ENCRYPTION=tls
SMTP_FROM=info@joetechsolutions.com
SMTP_FROM_NAME=Joetech Solutions
NOTIFICATION_EMAIL=info@joetechsolutions.com
```

## How pages work

Every page starts with:
```php
<?php
require_once __DIR__ . '/../includes/init.php';
```

This one line boots everything: DB connection, session, helpers, auth functions.

Then include the header, write your page content, include the footer.

## Adding a new page

1. Create `public/mypage.php`
2. Add `require_once __DIR__ . '/../includes/init.php';`
3. Write your SQL queries using `db_all()`, `db_one()`, `db_insert()`, `db_run()`
4. Include `header.php` and `footer.php`

## Adding a new admin page

1. Create `public/admin/mypage.php`
2. Add `require_once __DIR__ . '/../../includes/init.php';`
3. Call `require_staff();` and `require_permission('manage_xxx');`
4. Include `admin_header.php` and `admin_footer.php`

## Folder structure

```
joetech/
├── config/
│   ├── config.php     ← site settings + path constants
│   └── db.php         ← database credentials
├── includes/
│   ├── init.php       ← boot file (include this on every page)
│   ├── db.php         ← PDO connection + db_all/db_one/db_run/db_insert
│   ├── auth.php       ← login guards, session helpers, password functions
│   ├── helpers.php    ← e(), flash(), money(), redirect(), etc.
│   ├── header.php     ← public site header + nav
│   ├── footer.php     ← public site footer
│   ├── admin_header.php ← admin sidebar + topbar
│   └── admin_footer.php ← admin closing tags
├── public/
│   ├── index.php      ← homepage
│   ├── shop.php       ← product listings
│   ├── blog.php       ← blog listing
│   ├── ...            ← all public pages
│   ├── admin/
│   │   ├── index.php  ← admin dashboard
│   │   ├── products.php
│   │   ├── orders.php
│   │   └── ...        ← all admin pages
│   └── assets/
│       ├── css/
│       └── js/
└── uploads/           ← product images, avatars, etc.
```
